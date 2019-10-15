<?php namespace App\Classes\Jackett\Indexers;

use App\Models\Series;
use App\Models\Request;
use App\Models\Episode;
use ReflectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use App\Models\SeriesIndexerExclude;
use Illuminate\Support\Facades\Cache;
use App\Classes\Jackett\Enums\Quality;
use GuzzleHttp\Exception\ConnectException;
use App\Classes\Jackett\Components\Client;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class AbstractIndexer
 * @package App\Classes\Jackett\Indexers
 */
abstract class AbstractIndexer {

    /**
     * Constant SEARCH_ANY: search for anything that matches the query
     * @var integer
     */
    public const SEARCH_ANY = 0;

    /**
     * Constant SEARCH_SERIES: search for series
     * @var integer
     */
    public const SEARCH_SERIES = 1;

    /**
     * Constant SEARCH_MOVIES: search for movies
     * @var integer
     */
    public const SEARCH_MOVIES = 2;

    /**
     * Client instance
     * @var Client|null
     */
    protected $client = null;

    /**
     * Specific options for indexer
     * @var array
     */
    protected $options = [];

    /**
     * Tracker (Indexer) name
     * @var string|null
     */
    protected $tracker = null;

    /**
     * Type of search
     * @var int
     */
    protected $type = 0;

    /**
     * Search Query
     * @var string|null
     */
    protected $query = null;

    /**
     * Which quality we are looking for
     * @var integer|null
     */
    protected $quality = null;

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
     * AbstractIndexer constructor.
     * @param Client $client
     */
    public function __construct(Client $client) {
        $this->client = $client;
    }

    /**
     * What type of media are we looking for
     * @param int $type
     * @return AbstractIndexer|static|self|$this
     */
    public function in(int $type = self::SEARCH_ANY) : self {
        if ($type >= 0 && $type <= 2) {
            $this->type = $type;
        }
        return $this;
    }

    /**
     * Try to get media for specified quality
     * Note: Ony results with matching quality will be shown, rest will be hidden
     * @param int $quality
     * @return AbstractIndexer|static|self|$this
     * @throws ReflectionException
     */
    public function quality(int $quality = Quality::FHD) : self {
        if (in_array($quality, Quality::toArray(), true)) {
            $this->quality = $quality;
        }
        return $this;
    }

    /**
     * Perform search request to the specified indexer
     * @return null|array
     * @throws \Exception
     */
    public function fetch() : ?array {
        $this->runPreChecks();
        $response = Cache::remember($this->buildCacheKey(), now()->addMinutes(8), function() {
            $requestTime = number_format(round(microtime(true) * 1000), 0, '', '');
            try {
                $response = $this->client->get('indexers/all/results?Query=' . urlencode($this->query) . '&Tracker[]=' . $this->tracker . '&_=' . $requestTime);
                return $response;
            } catch (ConnectException $connectException) {
                return null;
            }
        });
        if ($response === null) {
            return $response;
        }
        return $this->processResponse($response);
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
     * Build cache key
     * @return string
     */
    protected function buildCacheKey() : string {
        return sprintf('indexers::%s:%s', $this->tracker, md5($this->query));
    }

    /**
     * Check whether or not cache key exists
     * @return bool
     */
    protected function hasCacheKey() : bool {
        return Cache::has($this->buildCacheKey());
    }

    /**
     * Set the search query
     * @param string $searchQuery
     * @return AbstractIndexer|static|self|$this
     */
    protected function search(string $searchQuery) : self {
        $this->query = $searchQuery;
        return $this;
    }

    /**
     * Whether or not we should exclude the whole season and its episodes from download
     * @param Series $series
     * @param Episode $episode
     * @return bool
     */
    protected function isSeasonExcludedFromDownload(Series $series, Episode $episode) : bool {
        $shouldSkip = SeriesIndexerExclude::where('series_id', '=', $episode->series_id)->where('season_number', '=', $episode->season_number)->exists();
        if ($shouldSkip) {
            app('log')->info(sprintf(
                '%s Season %d was marked to be excluded from downloading',
                $series->title,
                $episode->season_number
            ));
        }
        return $shouldSkip;
    }

    /**
     * Check if series wes approved for download
     * @param Series $series
     * @return bool
     */
    protected function isSeriesApprovedForDownload(Series $series) : bool {
        $request = Request::where('title', '=', $series->title)->where('year', '=', Arr::first(explode('-', $series->release_date)))->first();
        if ($request !== null) {
            return $request->status === 1 || $request->status === 3;
        }
        return true;
    }

    /**
     * Create possible names for the torrent file
     * @param Series $series
     * @param Episode $episode
     * @return array
     */
    protected function createPossibleTorrentNames(Series $series, Episode $episode) {
        $seriesNames = [];
        array_push($seriesNames, $series->title);
        array_push($seriesNames, create_lostfilm_title($series->local_title));
        array_push($seriesNames, implode('.', explode(' ', $series->title)));
        array_push($seriesNames, implode('.', explode(' ', create_lostfilm_title($series->local_title))) . '.');
        $seriesNames = array_values(array_unique($seriesNames));

        $possibleTorrentNames = [];

        foreach ($seriesNames as $name) {
            $possibleTorrentNames[] = sprintf('%sS%sE%s', $name, pad($episode->season_number), pad($episode->episode_number));
        }
        $possibleTorrentNames = array_merge($seriesNames, $possibleTorrentNames);
        return $possibleTorrentNames;
    }

    /**
     * Get next best quality
     * @param array $qualityArray
     * @param $quality
     * @return int
     */
    protected function getNextBestQuality(array $qualityArray, $quality) : int {
        krsort($qualityArray);
        if (array_key_exists($quality, $qualityArray)) {
            return $quality;
        }
        do {
            $quality = Quality::getNextQuality($quality);
        } while (! array_key_exists($quality, $qualityArray));
        return $quality;
    }

    /**
     * Service specific indexer implementation
     * @param Collection $seriesCollection
     * @return void
     */
    abstract public static function index(Collection $seriesCollection) : void;

    /**
     * Download missing episode for series
     * @param Series $series
     * @param Episode $episode
     * @param int $quality
     *
     * @return bool
     */
    abstract public static function download(Series $series, Episode $episode, int $quality = Quality::FHD) : bool;

    /**
     * Run indexer specific pre checks before executing query
     * @return void
     */
    abstract protected function runPreChecks() : void;

    /**
     * Indexer specific method to process response
     * @param array $response
     * @return null|array
     */
    abstract protected function processResponse(array $response) : ?array;

}
