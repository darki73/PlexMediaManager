<?php namespace App\Classes\TheMovieDB\Endpoint;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use LanguageDetection\Language;

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
    protected $type = null;

    /**
     * Search Query
     * @var null|string
     */
    protected $query = null;

    /**
     * Release Year
     * @var null|integer
     */
    protected $year = null;

    /**
     * Whether Or Not Include Adult Content
     * @var bool
     */
    protected $includeAdult = true;

    /**
     * Number of requests left before 429 error will be triggered
     * @var int
     */
    protected $requestsRemaining = 40;

    /**
     * Names Which Must Be "Fixed"
     * @var array
     */
    protected $nameFix = [

    ];

    /**
     * Set The Search Type
     * @param string $type
     * @param string $query
     * @return Search|static|self|$this
     */
    public function for(string $type, string $query) : self {
        $allowedTypes = $this->getAllowedSearchTypes();
        if (!in_array($type, $allowedTypes, true)) {
            throw  new \RuntimeException('Invalid search type `' . $type . '` provided, allowed search types are: ' . implode(', ', $allowedTypes));
        }
        $this->type = $type;
        if (array_key_exists($query, $this->nameFix)) {
            $this->options['query'] = $this->query = $this->nameFix[$query];
        } else {
            $this->options['query'] = $this->query = $query;
        }
        return $this;
    }

    /**
     * Set Release Year
     * @param null|int $year
     * @return Search|static|self|$this
     */
    public function year(?int $year) : self {
        if ($year !== null) {
            $now = Carbon::now();
            $currentYear = $now->year;
            if ($year > $currentYear) {
                $year = $currentYear;
                $this->removeYear($currentYear);
            }
            $this->year = $year;
        }
        return $this;
    }

    /**
     * Remove year from the query
     * NOTE: this method will only be called if there is error with year parsing
     * @param int $year
     * @return void
     */
    protected function removeYear(int $year) : void {
        $query = str_replace('(' . $year . ')', '', $this->options['query']);
        $this->options['query'] = trim($query);
    }

    /**
     * Detect the best language for query
     * @return void
     */
    protected function detectLanguage() : void {
        $languageDetect = (new Language)->detect($this->query)->whitelist('ru', 'en', 'es', 'de');
        $this->options['language'] = $this->language = array_key_first($languageDetect->bestResults()->close());
        return;
    }

    public function fetch() : array {
        $this->detectLanguage();
        if ($this->year !== null) {
            switch ($this->type) {
                case 'tv':
                    $this->options['first_air_date_year'] = $this->year;
                    break;
                case 'movie':
                    $this->options['year'] = $this->year;
                    break;
            }
        }
        $request = $this->client->get(sprintf('%s/%d/search/%s', $this->baseURL, $this->version, $this->type), [
            'query' =>  $this->options
        ]);
        $this->requestsRemaining = (int) $request->getHeader('X-RateLimit-Limit')[0];
        $data = json_decode($request->getBody()->getContents(), true);
        $returnArray = [];

        if (\count($data['results']) === 1) {
            return $data['results'][0];
        }

        foreach ($data['results'] as $result) {
            $itemName = $result['name'] ?? $result['title'];
            if (false !== strpos($itemName, ':')) {
                $itemName = str_replace(':', '', $itemName);
            }
            if (false !== stripos($itemName, $this->query)) {
                if (strtolower($itemName) === strtolower($this->query)) {
                    $returnArray = $result;
                    break;
                }
            }
        }

        if (\count($returnArray) === 0) {
            $returnArray = $data['results'][0];
        }

        return $returnArray;
    }

    /**
     * Get number of requests left before rate limiting
     * @return int
     */
    public function getRemainingRequestsCount() : int {
        return $this->requestsRemaining;
    }

    /**
     * Get list of allowed search types
     * @return array
     */
    protected function getAllowedSearchTypes() : array {
        $types = [];
        try {
            $constants = (new \ReflectionClass(__CLASS__))->getConstants();
            foreach ($constants as $constant => $value) {
                if (false !== stripos($constant, 'search_')) {
                    $types[] = $value;
                }
            }
        } catch (\ReflectionException $exception) {
            $types = [];
        }
        return $types;
    }

}
