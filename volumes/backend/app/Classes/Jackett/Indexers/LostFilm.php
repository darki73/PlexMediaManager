<?php namespace App\Classes\Jackett\Indexers;

use Exception;
use Illuminate\Support\Facades\Cache;
use RuntimeException;
use App\Models\Series;
use App\Models\Episode;
use ReflectionException;
use Illuminate\Support\Arr;
use App\Models\SeriesIndexer;
use App\Classes\Torrent\Torrent;
use App\Classes\Jackett\Enums\Quality;
use App\Classes\Jackett\Components\Client;
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
     * @inheritDoc
     * @param Collection $seriesCollection
     * @return void
     * @throws ReflectionException
     * @throws Exception
     */
    public static function index(Collection $seriesCollection): void {
        $self = (new self(new Client));
        foreach ($seriesCollection as $index => $series) {
            if (! $self->seriesWanted($series)) {
                continue;
            }
            $indexer = $series->indexer;
            if ($indexer === null) {
                $seriesName = create_lostfilm_title($self->stripYear($series->local_title));
                $result = $self
                    ->series($seriesName)
                    ->season(1)
                    ->quality(Quality::FHD)
                    ->fetchForIndex();

                if ($result !== false) {
                    SeriesIndexer::create([
                        'series_id'     =>  $series->id,
                        'indexer'       =>  $self->tracker
                    ]);
                }
            }
        }
    }

    /**
     * Fetch data for index
     * @return bool
     */
    protected function fetchForIndex() {
        $requestTime = number_format(round(microtime(true) * 1000), 0, '', '');
        try {
            $response = $this->client->get('indexers/all/results?Query=' . urlencode($this->query) . '&Tracker[]=' . $this->tracker . '&_=' . $requestTime);
            if ($response === null || !array_key_exists('Results', $response) || \count($response['Results']) === 0) {
                return false;
            }
        } catch (\GuzzleHttp\Exception\ConnectException $connectException) {
            return false;
        }
        return true;
    }

    /**
     * Download missing episode for series
     * @param Series $series
     * @param Episode $episode
     * @param int $quality
     *
     * @return bool
     * @throws ReflectionException
     */
    public static function download(Series $series, Episode $episode, int $quality = Quality::FHD) : bool {
        $self = (new self(new Client));
        $torrent = new Torrent();
        $alreadyDownloading = false;

        if ($series->local_title === null) {
            return false;
        }

        if (!$self->isSeriesApprovedForDownload($series)) {
            return false;
        }

        if ($self->isSeasonExcludedFromDownload($series, $episode)) {
            return false;
        }

        if (
            $series->id === 1408
            && $episode->season_number === 6
            && $episode->episode_number === 22
        ) {
            return false;
        }

        foreach ($torrent->listTorrents() as $item) {
            $torrentName = $item['name'];
            $singleFileName = sprintf('%s %d', create_lostfilm_title($self->stripYear($series->local_title)), $episode->season_number);
            $seasonFileName = sprintf('%s.S%sE%s',
                implode('.', explode(' ', create_lostfilm_title($self->stripYear($series->local_title)))),
                pad($episode->season_number),
                pad($episode->episode_number)
            );
            if (
                false !== stripos($torrentName, $singleFileName)
                || false !== stripos($torrentName, $seasonFileName)
            ) {
                $alreadyDownloading = true;
                break;
            }

            if ($alreadyDownloading) {
                break;
            }
        }

        if ($alreadyDownloading) {
            return false;
        }

        $search = $self->series(create_lostfilm_title($self->stripYear($series->local_title)))
            ->season($episode->season_number)
            ->episode($episode->episode_number)
            ->quality($quality);

        $torrent = $search->fetch();
        if ($torrent !== null && isset($torrent['torrent'])) {
            $downloader = new Torrent;
            $downloader->download($torrent['torrent'], 'series');
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     * @param int $quality
     * @return void
     */
    public static function downloadRequests(int $quality = Quality::FHD) : void {
        return;
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
                if ($this->quality === null) {
                    $this->quality = Quality::FHD;
                }

                if ($this->episodeNumber !== null) {
                    if (isset($data[$this->seasonNumber]['episodes'])) {
                        if (array_key_exists($this->episodeNumber, $data[$this->seasonNumber]['episodes'])) {
                            $bestAvailableQuality = $this->getNextBestQuality($data[$this->seasonNumber]['episodes'][$this->episodeNumber]['quality'], $this->quality);
                            return $data[$this->seasonNumber]['episodes'][$this->episodeNumber]['quality'][$bestAvailableQuality];
                        }
                        return null;
                    }
                }

                if (isset($data[$this->seasonNumber])) {
                    if (isset($data[$this->seasonNumber]['quality'])) {
                        $bestAvailableQuality = $this->getNextBestQuality($data[$this->seasonNumber]['quality'], $this->quality);
                        return $data[$this->seasonNumber]['quality'][$bestAvailableQuality];
                    }
                }
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
