<?php namespace App\Classes\Jackett\Indexers;

use App\Models\Series;
use App\Models\Episode;
use ReflectionException;
use Illuminate\Support\Facades\Log;
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
        $requestTime = number_format(round(microtime(true) * 1000), 0, '', '');
        try {
            $response = $this->client->get('indexers/all/results?Query=' . urlencode($this->query) . '&Tracker[]=' . $this->tracker . '&_=' . $requestTime);
        } catch (ConnectException $connectException) {
            Log::info('[Jackett::AbstractIndexer] Tried to fetch information for ' . $this->query . ' but the timeout of ' . env('JACKETT_TIMEOUT', 10.0) . ' seconds has been exceeded.');
            return null;
        }
        return $this->processResponse($response);
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
