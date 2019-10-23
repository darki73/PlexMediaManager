<?php namespace App\Classes\TheMovieDB\Endpoint;

use Carbon\Carbon;
use ReflectionClass;
use RuntimeException;
use ReflectionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Class Search
 * @package App\Classes\TheMovieDB\Endpoint
 */
class Search extends AbstractEndpoint {

    /**
     * Search For Companies
     * @var string
     */
    public const SEARCH_COMPANY = 'company';

    /**
     * Search For Collections
     * @var string
     */
    public const SEARCH_COLLECTION = 'collection';

    /**
     * Search For Keywords
     * @var string
     */
    public const SEARCH_KEYWORD = 'keyword';

    /**
     * Search For Movies
     * @var string
     */
    public const SEARCH_MOVIE = 'movie';

    /**
     * Search For Multiple
     * @var string
     */
    public const SEARCH_MULTI = 'multi';

    /**
     * Search For People
     * @var string
     */
    public const SEARCH_PEOPLE = 'person';

    /**
     * Search For TV Series
     * @var string
     */
    public const SEARCH_SERIES = 'tv';

    /**
     * Search Type
     * @var null|string
     */
    protected ?string $type = null;

    /**
     * Search Query
     * @var null|string
     */
    protected ?string $query = null;

    /**
     * Release Year
     * @var null|integer
     */
    protected ?int $year = null;

    /**
     * Whether Or Not Include Adult Content
     * @var bool
     */
    protected bool $includeAdult = true;

    /**
     * Set the search type and query
     * @param string $type
     * @param string $query
     * @return Search|static|self|$this
     */
    public function for(string $type, string $query) : self {
        $allowedTypes = $this->getAllowedSearchTypes();
        if (! in_array($type, $allowedTypes)) {
            throw new RuntimeException('Invalid search type `' . $type . '` provided, allowed search types are: ' . implode(', ', $allowedTypes));
        }
        $this->type = $type;
        $this->query = $this->options['query'] = $query;
        return $this;
    }

    /**
     * Set the year
     * @param int|null $year
     * @return Search|static|self|$this
     */
    public function year(?int $year = null) : self {
        if ($year !== null) {
            $now = Carbon::now();
            $currentYear = $now->year;
            if ($year > $currentYear) {
                $year = $currentYear;
                $this->removeYearFromQuery($currentYear);
            }
            $this->year = $year;
        }
        return $this;
    }

    /**
     * Fetch anything that matched the original query
     * @param bool $forceRefresh
     * @return array
     */
    public function fetchAll(bool $forceRefresh = false) : array {
        $this->detectLanguage();
        if ($this->year !== null) {
            if ($this->type === 'tv') {
                $this->options['first_air_date_year'] = $this->year;
            } else if ($this->type === 'tv') {
                $this->options['year'] = $this->year;
            }
        }
        $key = 'tmdb::api:search:' . md5(sprintf('%s:%s:%s', $this->type, strtolower($this->query), $this->year));

        if ($forceRefresh) {
            Cache::forget($key);
        }

        return Cache::remember($key, now()->addMinutes(2), function() {
            $request = $this->client->get(sprintf('%s/%d/search/%s', $this->baseURL, $this->version, $this->type), [
                'query'         =>  $this->options
            ]);
            $response = json_decode($request->getBody()->getContents(), true);
            return $response['results'];
        });
    }

    /**
     * Get the best matching item from the results
     * @param bool $forceRefresh
     * @return array
     */
    public function fetch(bool $forceRefresh = false) : array {
        $data = $this->fetchAll($forceRefresh);
        $returnArray = [];

        if (\count($data) === 1) {
            return Arr::first($data);
        }

        foreach ($data as $result) {
            $itemName = $result['name'] ?? $result['title'];
            $originalItemName = $result['original_name'] ?? $result['original_title'];
            if (false !== strpos($itemName, ':')) {
                $itemName = str_replace(':', '', $itemName);
            }
            if (false !== strpos($originalItemName, ':')) {
                $originalItemName = str_replace(':', '', $originalItemName);
            }

            if (false !== stripos($itemName, $this->query) || false !== stripos($originalItemName, $this->query)) {
                if (strtolower($itemName) === strtolower($this->query) || strtolower($originalItemName) === strtolower($this->query)) {
                    $returnArray = $result;
                    break;
                }
            }
        }

        if (\count($returnArray) === 0) {
            $returnArray = [];
        }
        return $returnArray;
    }

    /**
     * Remove year from query string
     * This method is only called if invalid year is provided
     * @param int $year
     * @return void
     */
    protected function removeYearFromQuery(int $year) : void {
        $query = str_replace('(' . $year . ')', '', $this->options['query']);
        $this->options['query'] = trim($query);
        return;
    }

    /**
     * Get list of allowed search types
     * @return array
     */
    protected function getAllowedSearchTypes() : array {
        $types = [];
        try {
            $constants = (new ReflectionClass(__CLASS__))->getConstants();
            foreach ($constants as $constant => $value) {
                if (false !== stripos($constant, 'search_')) {
                    $types[] = $value;
                }
            }
        } catch (ReflectionException $exception) {
            $types = [];
        }
        return $types;
    }

}
