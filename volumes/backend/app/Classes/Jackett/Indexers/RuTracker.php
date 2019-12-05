<?php namespace App\Classes\Jackett\Indexers;

use App\Models\Request;
use App\Models\Series;
use App\Models\Episode;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Models\SeriesIndexer;
use App\Classes\Torrent\Torrent;
use App\Classes\Jackett\Enums\Quality;
use App\Models\SeriesIndexerTorrentLink;
use App\Classes\Jackett\Components\Client;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class RuTracker
 * @package App\Classes\Jackett\Indexers
 */
class RuTracker extends AbstractIndexer {

    /**
     * @inheritDoc
     * @var string
     */
    protected $tracker = 'rutracker';

    /**
     * @inheritDoc
     * @var int
     */
    protected $type = self::SEARCH_SERIES;

    /**
     * Per request title
     * @var string|null
     */
    protected ?string $requestTitle = null;

    /**
     * Per request year
     * @var int|null
     */
    protected ?int $requestYear = null;

    /**
     * Search for series
     * @param string $query
     * @return AbstractIndexer
     */
    public function series(string $query) : AbstractIndexer {
        $this->type = self::SEARCH_SERIES;
        return $this->search($query);
    }

    /**
     * Search for movie
     * @param string $query
     * @return AbstractIndexer
     */
    public function movie(string $query) : AbstractIndexer {
        $this->type = self::SEARCH_MOVIES;
        return $this->search($query);
    }

    /**
     * @inheritDoc
     * @param Collection $seriesCollection
     * @return void
     * @throws \ReflectionException
     */
    public static function index(Collection $seriesCollection): void {
        $self = (new self(new Client));
        foreach ($seriesCollection as $series) {
            if (! $self->seriesWanted($series)) {
                continue;
            }

            $indexer = $series->indexer;
            if ($indexer === null) {

                $title = $series->title;
                if ($series->id === 34307) {
                    $title = sprintf('%s (%s)', $series->title, $series->origin_country);
                }

                $result = $self
                    ->series($title)
                    ->season(1)
                    ->quality(Quality::FHD)
                    ->fetch();

                $nextAcceptableQuality = Quality::getNextQuality($self->quality);
                $seasons = [];

                foreach ($result as $item) {
                    $information = $self->extractItemInformation($item);
                    if ($information === null) {
                        continue;
                    }
                    if (false !== strpos($item['Title'], (string) $self->quality)) {
                        $information = array_merge($information, [
                            'quality'   =>  1
                        ]);
                        $seasons[$information['season']][$self->getItemWeight($series, $information)] = $information;
                    } else if (false !== stripos($item['Title'], (string) $nextAcceptableQuality)) {
                        $information = array_merge($information, [
                            'quality'   =>  0
                        ]);
                        $seasons[$information['season']][$self->getItemWeight($series, $information)] = $information;
                    } else {
                        $information = array_merge($information, [
                            'quality'   =>  -1
                        ]);
                        $seasons[$information['season']][$self->getItemWeight($series, $information)] = $information;
                    }
                }

                ksort($seasons);

                foreach ($seasons as $season => $value) {
                    krsort($value);
                    $seasons[$season] = Arr::first($value);
                }

                if (\count($seasons) > 0) {
                    SeriesIndexer::create([
                        'series_id'     =>  $series->id,
                        'indexer'       =>  $self->tracker
                    ]);
                }

                foreach ($seasons as $data) {
                    SeriesIndexerTorrentLink::create([
                        'series_id'     =>  $series->id,
                        'season'        =>  $data['season'],
                        'torrent_file'  =>  $data['torrent_file']
                    ]);
                }
            }
        }
    }

    /**
     * Extract information from torrent item
     * @param array $item
     * @return array|null
     */
    protected function extractItemInformation(array $item) : ?array {
        $regex = '/s(\d{1,2})e(\d{1,3})-(\d{1,3})/i';
        preg_match($regex, $item['Title'], $matches);
        try {
            $season = $matches[1];
            $firstEpisode = $matches[2];
            $lastEpisode = $matches[3];
        } catch (\Exception $exception) {
            return null;
        }

        return [
            'season'    =>  (integer) $season,
            'first'     =>  (integer) $firstEpisode,
            'last'      =>  (integer) $lastEpisode,
            'seeders'   =>  $item['Seeders'],
            'torrent_file'  =>  str_replace('viewtopic', 'dl', $item['Comments'])
        ];
    }

    /**
     * Get item weight
     * @param Series $series
     * @param array $item
     * @return int
     */
    protected function getItemWeight(Series $series, array $item) : int {
        $selectedSeason = $series->seasons->filter(function($season) use ($item) {
            return $season->season_number === $item['season'];
        })->first();

        $episodes = $item['last'];
        if ($selectedSeason->episodes_count < $episodes) {
            $episodes = -100;
        }
        $qualityWeight = [
            -1      =>  0,
            0       =>  50,
            1       =>  100
        ];
        return ($qualityWeight[$item['quality']] + $item['seeders'] + $episodes);
    }

    /**
     * Download multiple files at once
     * @param Series $series
     * @param Collection $episodesCollection
     * @param int $quality
     *
     * @return bool
     * @throws \ReflectionException
     */
    public static function downloadMultiple(Series $series, Collection $episodesCollection, int $quality = Quality::FHD) : bool {
        $self = (new self(new Client));
        $torrent = new Torrent();
        $alreadyDownloading = [];
        $seasons = [];

        if (!$self->isSeriesApprovedForDownload($series)) {
            return false;
        }

        if (!$self->seriesWanted($series)) {
            return false;
        }

        $title = $series->title;
        if ($series->id === 34307) {
            $title = sprintf('%s (%s)', $series->title, $series->origin_country);
        }

        foreach ($episodesCollection as $episode) {
            if (!$self->isSeasonExcludedFromDownload($series, $episode)) {
                if (!array_key_exists($episode->season_number, $seasons)) {
                    $jackettDownloadLink = null;
                    $torrentLinkModel = SeriesIndexerTorrentLink::where('series_id', '=', $series->id)->where('season', '=', $episode->season_number)->first();
                    if ($torrentLinkModel === null)  {
                        continue;
                    }
                    $result = $self
                        ->series($title)
                        ->season($episode->season_number)
                        ->quality($quality)
                        ->fetch();
                    foreach ($result as $item) {
                        $commentString = str_replace('dl.php', 'viewtopic.php', $torrentLinkModel->torrent_file);
                        if (trim($item['Comments']) === trim($commentString)) {
                            $jackettDownloadLink = $item['Link'];
                            break;
                        }
                    }
                    $seasons[$episode->season_number] = [
                        'torrent_file'      =>  $torrentLinkModel->torrent_file,
                        'jackett_link'      =>  $jackettDownloadLink,
                        'episodes'          =>  []
                    ];
                }
                $seasons[$episode->season_number]['episodes'][] = $episode->episode_number;
            }
        }

        foreach ($seasons as $season => $data) {
            $alreadyDownloading[$season] = false;
        }

        foreach ($torrent->listTorrents() as $item) {
            $torrentName = $item['name'];
            foreach ($seasons as $season => $data) {
                if (
                    false !== stripos($torrentName, $series->title)
                    && (
                        false !== stripos($torrentName, 'S' . pad($season))
                        || false !== stripos($torrentName, 'S' . $season)
                    )
                ) {
                    $alreadyDownloading[$season] = true;
                }
            }
        }

        $downloadsCount = 0;
        foreach ($seasons as $season => $data) {
            if (!$alreadyDownloading[$season]) {
                $torrent->download($data['jackett_link'], 'series');
                $downloadsCount++;
            }
        }

        sleep(10); // TODO: Yeaaaaah... we do need this due to the fact that job simply kills all torrents
        $removeFromDownloading = [];
        foreach ($torrent->listTorrents() as $item) {
            $torrentHash = $item['hash'];
            $torrentFiles = $torrent->torrentFiles($torrentHash);
            foreach ($torrentFiles as $index => $file) {
                $fileName = $file['name'];
                if (
                    false !== stripos($fileName, $series->title)
                    || false !== stripos(str_replace('.', ' ', $fileName), $series->title)
                ) {
                    preg_match('/s(\d{1,2})e(\d{1,3})/i', $fileName, $matches);
                    if (\count($matches) < 3) {
                        $removeFromDownloading[$torrentHash][] = $index;
                    } else {
                        $season = (integer) $matches[1];
                        $episode = (integer) $matches[2];
                        $data = isset($seasons[$season]) ? $seasons[$season] : null;
                        if ($data === null) {
                            $removeFromDownloading[$torrentHash][] = $index;
                        } else {
                            if (!in_array($episode, $data['episodes'])) {
                                $removeFromDownloading[$torrentHash][] = $index;
                            }
                        }
                    }
                }
            }
        }

        foreach ($removeFromDownloading as $hash => $files) {
            foreach ($files as $file) {
                $torrent->doNotDownload($hash, $file);
            }
        }

//        dispatch(new MarkFilesUnwanted($series, $seasons))->delay(10);

        return $downloadsCount > 0;
    }

    public static function downloadRequests(int $quality = Quality::FHD) : void {
        // TODO: Move method definition to the Interface
        $self = (new self(new Client));
        $torrent = new Torrent();
        $requestsCollection = Request::where('request_type', '=', 1)->where('status', '=', 1)->get();
        foreach ($requestsCollection as $request) {
            $alreadyDownloading = false;
            $self->requestTitle = $request->title;
            $self->requestYear = $request->year;
            $results = $self
                ->movie(sprintf('%s %d', $request->title, $request->year))
                ->quality($quality)
                ->fetch();

            $bestMatch = null;
            $mostSeeders = 0;

            foreach ($results as $result) {
                if ($result['Seeders'] > $mostSeeders) {
                    $mostSeeders = $result['Seeders'];
                    $bestMatch = $result;
                }
            }

            foreach ($torrent->listTorrents() as $item) {
                $torrentName = $item['name'];
                if (false !== stripos($torrentName, $self->requestTitle) && false !== strpos($torrentName, (string)$self->requestYear)) {
                    $alreadyDownloading = true;
                }
            }

            if (!$alreadyDownloading && $bestMatch !== null) {
                $torrent->download($bestMatch['Link'], 'movies');
            }
        }
    }

    /**
     * @inheritDoc
     * @param Series $series
     * @param Episode $episode
     * @param int $quality
     * @return bool
     */
    public static function download(Series $series, Episode $episode, int $quality = Quality::FHD): bool {
        return false;
    }

    /**
     * @inheritDoc
     * @return void
     */
    protected function runPreChecks(): void {
        // Nothing to do here, this method must be implemented tho
    }

    /**
     * @inheritDoc
     * @param array $response
     * @return array|null
     */
    protected function processResponse(array $response): ?array {
        if (! array_key_exists('Results', $response)) {
            return []; // Just return empty array, no results found anyways
        }
        $results = $response['Results'];
        $data = [];

        switch ($this->type) {
            case self::SEARCH_SERIES:
                $filteredSeries = array_filter($results, function(array $item) : bool {
                    return false !== stripos($item['CategoryDesc'], 'tv');
                });
                $data = array_filter($filteredSeries, function(array $item) : bool {
                    return Str::startsWith($item['Title'], $this->query);
                });
                break;
            case self::SEARCH_MOVIES:
                $filteredMovies = array_filter($results, function(array $item) : bool {
                    return
                        $item['CategoryDesc'] === 'Movies'
                        || false !== stripos($item['CategoryDesc'], 'Movies/Foreign')
                        || false !== stripos($item['CategoryDesc'], 'Movies/HD')
                        || false !== stripos($item['CategoryDesc'], 'PC/Mac');
                });
                $data = array_filter($filteredMovies, function(array $item) : bool {
                    return
                        Str::startsWith($item['Title'], $this->requestTitle)
                        && false !== strpos($item['Title'], (string) $this->requestYear)
                        && false !== strpos($item['Title'], (string) $this->quality);
                });
                break;
        }

        return $data;
    }


}
