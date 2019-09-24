<?php namespace App\Classes;

use App\Classes\TheMovieDB\Endpoint\Search;
use App\Classes\TheMovieDB\TheMovieDB;
use App\Classes\Torrent\Torrent;
use App\Models\Movie as MovieModel;
use App\Classes\Storage\PlexStorage;
use App\Models\Series as SeriesModel;
use App\Classes\Storage\TorrentStorage;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Class DownloadManager
 * @package App\Classes
 */
class DownloadManager {

    /**
     * Public constant Type: Series
     * @var string
     */
    public const TYPE_SERIES = 'series';

    /**
     * Public constant Type: Movies
     * @var string
     */
    public const TYPE_MOVIES = 'movies';

    /**
     * Torrent Storage instance
     * @var TorrentStorage|null
     */
    protected $torrentStorage = null;

    /**
     * Plex Storage instance
     * @var PlexStorage|null
     */
    protected $plexStorage = null;

    /**
     * Torrent client instance
     * @var Torrent|null
     */
    protected $torrentInstance = null;

    /**
     * Type of content we are working with
     * @var string|null
     */
    protected $type = null;

    /**
     * Collection for specified type of media content
     * @var array|SeriesModel[]|MovieModel[]
     */
    protected $collection = [];

    /**
     * Full torrent and storage paths
     * @var array
     */
    protected $paths = [];

    /**
     * Cached responses for same series
     * @var array
     */
    protected $responseCache = [];


    /**
     * DownloadManager constructor.
     */
    public function __construct() {
        $this->torrentStorage = new TorrentStorage;
        $this->plexStorage = $this->torrentStorage->getPlexStorage();
        $this->torrentInstance = new Torrent;
    }

    /**
     * We are working with Series
     * @return DownloadManager|static|self|$this
     */
    public function series() : self {
        $this->type = DownloadManager::TYPE_SERIES;
        $this->collection = SeriesModel::all();
        $this->paths = $this->torrentStorage->getStorageFor($this->type);
        return $this;
    }

    /**
     * We are working with Movies
     * @return DownloadManager|static|self|$this
     */
    public function movies() : self {
        $this->type = DownloadManager::TYPE_MOVIES;
        $this->collection = MovieModel::all();
        $this->paths = $this->torrentStorage->getStorageFor($this->type);
        return $this;
    }

    public function listDownloadedFiles() : array {
        $activeTorrents = $this->torrentInstance->listTorrents();
        switch ($this->type) {
            case 'series':
                $singleEpisodeFiles = $this->torrentStorage->listFiles($this->paths['torrent']['local']['relative']);
                $episodes = [];
                foreach ($singleEpisodeFiles as $singleEpisodeFile) {
                    $fileName = Arr::last(explode(DIRECTORY_SEPARATOR, $singleEpisodeFile));
                    if (! $this->shouldSkip($fileName, $activeTorrents)) {
                        $episodes[] = $this->processSingleEpisodeFile($singleEpisodeFile);
                    }
                }
                $this->processSeasonFolders($episodes, $activeTorrents);
                return $episodes;
                break;
            case 'movies':
                $torrentFiles = $this->torrentStorage->listFiles($this->paths['torrent']['local']['relative']);
                $movies = [];
                foreach ($torrentFiles as $file) {
                    $fileName = Arr::last(explode(DIRECTORY_SEPARATOR, $file));
                    if (! $this->shouldSkip($fileName, $activeTorrents)) {
                        $movies[] = $this->extractMovieInformation($file, $fileName);
                    }
                }
                return $movies;
                break;
            default:
                return [];
        }
    }

    /**
     * Process single episode file
     * @param string $path
     * @return array
     */
    protected function processSingleEpisodeFile(string $path) : array {
        $extractorRegex = '/(.+)\.S([0-9]+)E([0-9]+).*$/i';
        $fileName = Arr::last(explode(DIRECTORY_SEPARATOR, $path));
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        $shouldFixAudioChannelsNames = false !== stripos($fileName, '.LostFilm.TV');

        preg_match($extractorRegex, $fileName, $matches);

        if (\count($matches) === 4) {
            $rawName = $matches[1];
            $rawSeason = $matches[2];
            $rawEpisode = $matches[3];
            $multipleEpisodes = false;
            $parts = explode('.', $fileName);
            $seasonAndEpisode = null;

            foreach ($parts as $part) {
                if (false !== stripos(strtolower($part), sprintf('s%se%s', $rawSeason, $rawEpisode))) {
                    $tmpPart = strtolower($part);

                    if (false !== strpos($tmpPart, '-e')) {
                        $tmpPart = str_replace('-e', '-', $tmpPart);
                    }

                    $seasonAndEpisode = $tmpPart;
                }
            }


            if ($multipleEpisodes) {
                dd($path, $matches, $multipleEpisodes);
            } else {
                $rawNameExploded = explode('.', $rawName);
                $possibleName = '';

                foreach ($rawNameExploded as $part) {
                    if (strlen($part) > 1) {
                        $possibleName .= $part . ' ';
                    } else {
                        if ($part === 'a') {
                            $possibleName .= $part . ' ';
                        } else {
                            $possibleName .= $part . '.';
                        }
                    }
                }
                $possibleName = trim($possibleName);
                $localName = $this->getSeriesLocalName($possibleName);
                $year = null;

                if ($localName === null) {
                    if (! array_key_exists($possibleName, $this->responseCache)) {
                        $database = new TheMovieDB;
                        $response = $database->search()->for(Search::SEARCH_SERIES, $possibleName)->fetch();
                        $this->responseCache[$possibleName] = $response;
                    } else {
                        $response = $this->responseCache[$possibleName];
                    }
                    if (isset($response['original_name'])) {
                        $releaseParts = explode('-', $response['first_air_date']);
                        $year = (int) $releaseParts[0];
                    }
                }

                $seriesFolderName = $localName ? $localName : sprintf('%s (%d)', $possibleName, $year);

                $seriesFolderInContainer = implode(DIRECTORY_SEPARATOR, [
                    $this->paths['plex']['local']['absolute'],
                    $this->plexStorage->getBestDrive(),
                    $seriesFolderName,
                    'Season ' . $rawSeason
                ]);

                if (!File::exists($seriesFolderInContainer)) {
                    File::makeDirectory($seriesFolderInContainer, 0755, true);
                }

                $result = [
                    'series_folder'     =>  $seriesFolderName,
                    'series_file'       =>  sprintf('%s - %s.%s', $seriesFolderName, $seasonAndEpisode, $extension),
                    'downloads_path'    =>  implode(DIRECTORY_SEPARATOR, [
                        $this->paths['torrent']['remote'],
                        str_replace($this->paths['torrent']['local']['relative'] . DIRECTORY_SEPARATOR, '', $path)
                    ]),
                    'local_path'        =>  implode(DIRECTORY_SEPARATOR, [
                        $this->paths['plex']['remote'],
                        $seriesFolderName,
                        'Season ' . $rawSeason,
                        sprintf('%s - %s.%s', $seriesFolderName, $seasonAndEpisode, $extension)
                    ]),
                    'fix_audio'         =>  $shouldFixAudioChannelsNames,
                    'fix_path'          =>  sprintf('/app/storage/app/%s', $path)
                ];
                return $result;
            }
        } else {
            dd($matches, \count($matches) . ' matches');
        }
    }

    /**
     * Process folders for full seasons of series
     * @param array $episodes
     * @param array $activeTorrents
     */
    protected function processSeasonFolders(array & $episodes, array $activeTorrents) : void  {
        $seasonFolders = $this->torrentStorage->listDirectories($this->paths['torrent']['local']['relative']);
        foreach ($seasonFolders as $folder) {
            $directoryName = Arr::last(explode(DIRECTORY_SEPARATOR, $folder));
            if (!$this->shouldSkip($directoryName, $activeTorrents)) {
                $singleEpisodeFiles = $this->torrentStorage->listFiles($folder);
                foreach ($singleEpisodeFiles as $singleEpisodeFile) {
                    $episodes[] = $this->processSingleEpisodeFile($singleEpisodeFile);
                }
            }
        }
    }

    /**
     * Get local name for the series
     * Returns NULL if series is not in the database
     * @param string $seriesName
     * @return string|null
     */
    protected function getSeriesLocalName(string $seriesName) : ?string {
        $localName = null;
        foreach ($this->collection as $series) {
            $originalName = str_replace([
                '`',
                '\'',
                ':'
            ], '', $series->title);
            if (false !== stripos($originalName, $seriesName)) {
                $localName = $series->local_title;
                break;
            }
        }

        return $localName;
    }

    /**
     * Extract movie information
     * @param string $filePath
     * @param string $fileName
     * @return array
     */
    protected function extractMovieInformation(string $filePath, string $fileName) : array {
        $movieTitle = Str::before($fileName, Arr::first(array_filter(preg_split("/\D+/", $fileName))));
        $titleParts = explode('.', $movieTitle);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        $ignoreItems = config('torrent.ignore_parts');

        foreach ($titleParts as $index => $value) {
            foreach ($ignoreItems as $ignore) {
                if (false !== stripos($value, $ignore)) {
                    unset($titleParts[$index]);
                }
            }
        }

        $possibleTitle = implode(' ', array_filter($titleParts));
        $searchResponse = (new TheMovieDB)->search()->for(Search::SEARCH_MOVIE, $possibleTitle)->fetch();
        if (isset($searchResponse['id'])) {
            $releaseYear = Arr::first(explode('-', $searchResponse['release_date']));
            $title = str_replace([
                ':'
            ], '', $searchResponse['title']);
            $finalFileName = sprintf('%s (%s).%s', $title, $releaseYear, $extension);
            return [
                'downloads_path'    =>  implode(DIRECTORY_SEPARATOR, [
                    $this->paths['torrent']['remote'],
                    str_replace('private/torrent/movies/', '', $filePath)
                ]),
                'local_path'        =>  implode(DIRECTORY_SEPARATOR, [
                    $this->paths['plex']['remote'],
                    $finalFileName
                ])
            ];
        }
        return [];
    }

    /**
     * Check whether or not file should be hidden from results
     * This will only be TRUE if torrent is still downloading
     * @param string $string
     * @param array $activeTorrents
     * @return bool
     */
    protected function shouldSkip(string $string, array $activeTorrents) : bool {
        if (Str::endsWith($string, '.parts')) {
            return true;
        }
        $shouldSkip = false;
        foreach ($activeTorrents as $torrent) {
            if (false !== stripos($torrent['name'], $string)) {
                $shouldSkip = true;
                break;
            }
        }
        return $shouldSkip;
    }

}
