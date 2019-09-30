<?php namespace App\Classes\Jackett\Indexers;

use App\Classes\Jackett\Components\Client;
use App\Classes\Jackett\Enums\Quality;
use App\Classes\Torrent\Torrent;
use App\Models\Episode;
use App\Models\Series;
use App\Models\SeriesIndexer;
use RuntimeException;
use Illuminate\Support\Arr;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class LostFilm
 * @package App\Classes\Jackett\Indexers
 */
class LostFilm extends AbstractIndexer {

    /**
     * @inheritDoc
     * @var string
     */
    protected $tracker = 'lostfilm';

    /**
     * @inheritDoc
     * @var int
     */
    protected $type = self::SEARCH_SERIES;

    /**
     * Which season we are looking for
     * @var integer|null
     */
    protected $seasonNumber = null;

    /**
     * Which episode we are looking for
     * @var integer|null
     */
    protected $episodeNumber = null;

    /**
     * @inheritDoc
     * @param Collection $seriesCollection
     * @return void
     * @throws \ReflectionException
     * @throws \Exception
     */
    public static function index(Collection $seriesCollection): void {
        $self = (new self(new Client));
        foreach ($seriesCollection as $series) {
            $indexer = $series->indexer;
            if ($indexer === null) {
                $seriesName = $self->stripYear($series->local_title);
                $result = $self
                    ->series($seriesName)
                    ->season(1)
                    ->quality(Quality::FHD)
                    ->fetch();
                if ($result !== null) {
                    SeriesIndexer::create([
                        'series_id'     =>  $series->id,
                        'indexer'       =>  $self->tracker
                    ]);
                }
            }
        }
    }

    /**
     * Download missing episode for series
     * @param Series $series
     * @param Episode $episode
     * @param int $quality
     *
     * @return bool
     * @throws \ReflectionException
     */
    public static function download(Series $series, Episode $episode, int $quality = Quality::FHD) : bool {
        $self = (new self(new Client));
        $torrent = new Torrent();

        $possibleTorrentNames = $self->createPossibleTorrentNames($series, $episode);
        $alreadyDownloading = false;

        foreach ($torrent->listTorrents() as $item) {
            foreach ($possibleTorrentNames as $name) {
                if (false !== stripos($item['name'], $name)) {
                    $alreadyDownloading = true;
                    break;
                }
            }
            if ($alreadyDownloading) {
                break;
            }
        }

        if ($alreadyDownloading) {
            return false;
        }

        $search = $self->series(create_lostfilm_title($series->local_title))
            ->season($episode->season_number)
            ->episode($episode->episode_number)
            ->quality($quality);
        $torrent = $search->fetch();
        if ($torrent !== null && isset($torrent['torrent'])) {
            $downloader = new Torrent;
            $downloader->download($torrent['torrent'], 'Series');
            return true;
        }
        return false;
    }

    /**
     * What series we are searching for
     * @param string $seriesName
     * @return AbstractIndexer|static|self|$this
     */
    public function series(string $seriesName) : AbstractIndexer {
        return $this->search($seriesName);
    }

    /**
     * Filter search results down to a specific season
     * @param int $seasonNumber
     * @return LostFilm|static|self|$this
     */
    public function season(int $seasonNumber) : self {
        $this->seasonNumber = $seasonNumber;
        return $this;
    }

    /**
     * Filter search results down to a specific episode
     * Note: `forSeason(int $seasonNumber)` must be called first
     * @param int $episodeNumber
     * @return LostFilm|static|self|$this
     */
    public function episode(int $episodeNumber) : self {
        if ($this->seasonNumber === null) {
            throw new RuntimeException('You must call `season(int $seasonNumber)` method before calling this method.');
        }
        $this->episodeNumber = $episodeNumber;
        return $this;
    }

    /**
     * @inheritDoc
     * @return void
     */
    protected function runPreChecks(): void {
        if ($this->seasonNumber === null) {
            throw new RuntimeException('You have to specify season first using `season(int $seasonNumber)` method.');
        }
    }

    /**
     * @inheritDoc
     * @param array $response
     * @return array|null
     */
    protected function processResponse(array $response) : ?array {
        if (! array_key_exists('Results', $response)) {
            return []; // Just return empty array, no results found anyways
        }
        $results = $response['Results'];
        $data = $this->processSingleEpisode($results) + $this->processWholeSeason($results);
        ksort($data);
        if ($this->seasonNumber !== null) {
            if (array_key_exists($this->seasonNumber, $data)) {
                if ($this->episodeNumber !== null) {
                    if (isset($data[$this->seasonNumber]['episodes']) && array_key_exists($this->episodeNumber, $data[$this->seasonNumber]['episodes'])) {
                        if ($this->quality === null) {
                            return $data[$this->seasonNumber]['episodes'][$this->episodeNumber];
                        }

                        if (!array_key_exists($this->quality, $data[$this->seasonNumber]['episodes'][$this->episodeNumber]['quality'])) {
                            return Arr::last($data[$this->seasonNumber]['episodes'][$this->episodeNumber]['quality']);
                        }
                        return $data[$this->seasonNumber]['episodes'][$this->episodeNumber]['quality'][$this->quality];
                    }
                    return null;
                }
                if ($this->quality === null) {
                    return $data[$this->seasonNumber];
                }

                if (!isset($data[$this->seasonNumber]['episodes'])) {
                    if (!array_key_exists($this->quality, $data[$this->seasonNumber]['quality'])) {
                        return Arr::last($data[$this->seasonNumber]['quality']);
                    }
                    return $data[$this->seasonNumber]['quality'][$this->quality];
                }
                return $data[$this->seasonNumber];
            }
            return null;
        }
        return count($data) > 0 ? $data : null;
    }

    /**
     * Process single episode entries from results
     * @param array $results
     * @return array
     */
    protected function processSingleEpisode(array $results) : array {
        $details = [];
        $regex = '/ - S\d{1,2}E\d{1,2}/';

        foreach ($results as $item) {
            $item = array_change_key_case($item, CASE_LOWER);
            preg_match($regex, $item['title'], $matches);
            if (\count($matches) > 0) {
                $filtered = trim(str_replace(['-', 's', 'e'], ['', '', ' '], strtolower($matches[0])));
                $exploded = explode(' ', $filtered);
                $season = (int) $exploded[0];
                $episode = (int) $exploded[1];
                if (!array_key_exists($season, $details)) {
                    $details[$season] = [
                        'season'    =>  $season,
                        'episodes'  =>  []
                    ];
                }

                $episodeTitle = $item['title'];
                $episodeTitleRegex = '/ - S\d{1,2}E\d{1,2} - .* -/';
                preg_match($episodeTitleRegex, $item['title'], $titleMatches);
                if (\count($titleMatches) > 0) {
                    $episodeTitle = trim(
                        ltrim(
                            rtrim(
                                str_replace($matches[0], '', $titleMatches[0]),
                                ' -'
                            ),
                            ' - '
                        )
                    );
                }

                if (!array_key_exists($episode, $details[$season]['episodes'])) {
                    $details[$season]['episodes'][$episode] = [
                        'title'     =>  $episodeTitle,
                        'quality'   =>  []
                    ];
                }
                $qualityLevel = 360;
                if (false !== stripos($item['title'], '1080p')) {
                    $qualityLevel = 1080;
                } else if (false !== stripos($item['title'], '720p')) {
                    $qualityLevel = 720;
                }

                $details[$season]['episodes'][$episode]['quality'][$qualityLevel] = [
                    'torrent'   =>  $item['link'],
                    'size'      =>  $item['size']
                ];
                ksort($details[$season]['episodes'][$episode]['quality']);
                ksort($details[$season]['episodes']);
            }
        }
        return $details;
    }

    /**
     * Process whole season entries from results
     * @param array $results
     * @return array
     */
    protected function processWholeSeason(array $results) : array {
        $details = [];
        $regex = '/ - S\d{1,2} - /';

        foreach ($results as $item) {
            $item = array_change_key_case($item, CASE_LOWER);
            preg_match($regex, $item['title'], $matches);
            if (\count($matches) > 0) {
                $season = (int) trim(str_replace(['-', 'S'], '', $matches[0]));
                if (!array_key_exists($season, $details)) {
                    $details[$season] = [
                        'season'    =>  $season,
                        'quality'   =>  []
                    ];
                }
                $qualityLevel = 360;
                if (false !== stripos($item['title'], '1080p')) {
                    $qualityLevel = 1080;
                } else if (false !== stripos($item['title'], '720p')) {
                    $qualityLevel = 720;
                }
                $details[$season]['quality'][$qualityLevel] = [
                    'title'     =>  'Complete Season ' . $season,
                    'torrent'   =>  $item['link'],
                    'size'      =>  $item['size']
                ];
                ksort($details[$season]['quality']);
            }
        }
        return $details;
    }

    /**
     * Remove year from series name
     * @param string $seriesName
     * @return string
     */
    protected function stripYear(string $seriesName) : string {
        preg_match( '!\(([^\)]+)\)!', $seriesName, $match );
        if (\count($match) > 1) {
            return trim(str_replace($match[0], '', $seriesName));
        }
        return $seriesName;
    }

}
