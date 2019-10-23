<?php namespace App\Classes\TheMovieDB\Processor;

use Illuminate\Support\Arr;

/**
 * Class AbstractProcessor
 * @package App\Classes\TheMovieDB\Processor
 */
abstract class AbstractProcessor {

    /**
     * Constant for Status: Released
     * @var integer
     */
    protected const STATUS_RELEASED = 1;

    /**
     * Constant for Status: Running / Returning Series
     * @var integer
     */
    protected const STATUS_RUNNING = 2;

    /**
     * Constant for Status: Ended
     * @var integer
     */
    protected const STATUS_ENDED = 3;

    /**
     * Constant for Status: Canceled
     * @var integer
     */
    protected const STATUS_CANCELED = 4;

    /**
     * Constant for Status: Unknown
     * @var integer
     */
    protected const STATUS_UNKNOWN = 5;

    /**
     * Raw api response converted to array
     * @var array|null
     */
    protected $rawElement = null;

    /**
     * TheMovieDB element number
     * @var integer|null
     */
    protected $id = null;

    /**
     * Title of the element
     * @var string|null
     */
    protected $title = null;

    /**
     * Original title of the element
     * @var string|null
     */
    protected $originalTitle = null;

    /**
     * Local title of the element
     * @var string|null
     */
    protected $localTitle = null;

    /**
     * Original language of the element
     * @var string|null
     */
    protected $originalLanguage = null;

    /**
     * Home page for given element
     * @var string|null
     */
    protected $homepage = null;

    /**
     * Overview for provided element
     * @var string|null
     */
    protected $overview = null;

    /**
     * Popularity index for provided element
     * @var double|null
     */
    protected $popularity = null;

    /**
     * Average vote score for provided element
     * @var double|null
     */
    protected $voteAverage = null;

    /**
     * Vote count for provided element
     * @var integer|null
     */
    protected $voteCount = null;

    /**
     * Backdrop image name for provided element
     * @var string|null
     */
    protected $backdrop = null;

    /**
     * Poster image name for provided element
     * @var string|null
     */
    protected $poster = null;

    /**
     * Still image name for provided element
     * @var string|null
     */
    protected $still = null;

    /**
     * Runtime for the provided element
     * @var integer|null
     */
    protected $runtime = null;

    /**
     * Release date for the provided element
     * @var string|null
     */
    protected $releaseDate = null;

    /**
     * Status for the provided element
     * @var integer|null
     */
    protected $status = null;

    /**
     * Production companies for the provided element
     * @var array|null
     */
    protected $productionCompanies = null;

    /**
     * Languages for the provided element
     * @var array|null
     */
    protected $languages = null;

    /**
     * Genres for the provided element
     * @var array|null
     */
    protected $genres = null;

    /**
     * Class members to be excluded from array
     * @var array
     */
    protected $excludeFromArray = [];

    /**
     * AbstractProcessor constructor.
     * @param array $information
     */
    public function __construct(array $information) {
        $this->rawElement = $information;
        $this->processElement();
    }

    /**
     * Convert processor class to array
     * @return array
     */
    public function toArray() : array {
        $values = Arr::except(get_object_vars($this), array_merge(
            [
                'rawElement',
                'excludeFromArray'
            ],
            $this->excludeFromArray
        ));
        ksort($values);
        return $values;
    }

    /**
     * Processor specific element title/name extraction function
     * @return AbstractProcessor|static|self|$this
     */
    abstract protected function extractTitle() : self;

    /**
     * Processor specific element original title/name extraction function
     * @return AbstractProcessor|static|self|$this
     */
    abstract protected function extractOriginalTitle() : self;

    /**
     * Processor specific element runtime extraction function
     * @return AbstractProcessor|static|self|$this
     */
    abstract protected function extractRuntime() : self;

    /**
     * Processor specific element release date extraction function
     * @return AbstractProcessor|static|self|$this
     */
    abstract protected function extractReleaseDate() : self;

    /**
     * Processor specific element languages extraction function
     * @return AbstractProcessor|static|self|$this
     */
    abstract protected function extractLanguages() : self;

    /**
     * Extract Processor Specific Data
     * @return AbstractProcessor|static|self|$this
     */
    abstract protected function extractProcessorSpecificData() : self;

    /**
     * Clear image path
     * @param string|null $path
     * @return string|null
     */
    protected function clearPath(?string $path) : ?string {
        if ($path === null) {
            return null;
        }
        return trim(ltrim($path, '/'));
    }

    /**
     * Check whether raw element has specified property
     * @param string $property
     * @return bool
     */
    protected function hasOwnProperty(string $property) : bool {
        return isset($this->rawElement[$property]);
    }

    /**
     * Extract element id from the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractId() : self {
        $this->id = $this->rawElement['id'];
        return $this;
    }

    /**
     * Extract original language from the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractOriginalLanguage() : self {
        if ($this->hasOwnProperty('original_language')) {
            $this->originalLanguage = $this->rawElement['original_language'];
        }
        return $this;
    }

    /**
     * Extract local title for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractLocalTitle() : self {
        if ($this->hasOwnProperty('local_name')) {
            $this->localTitle = $this->rawElement['local_name'];
        }
        return $this;
    }

    /**
     * Extract home page for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractHomePage() : self {
        if ($this->hasOwnProperty('homepage')) {
            if (strlen($this->rawElement['homepage']) > 200) {
                $this->homepage = null;
            } else {
                $this->homepage = $this->rawElement['homepage'];
            }
        }
        return $this;
    }

    /**
     * Extract overview for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractOverview() : self {
        $this->overview = $this->rawElement['overview'];
        return $this;
    }

    /**
     * Extract popularity for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractPopularity() : self {
        if ($this->hasOwnProperty('popularity')) {
            $this->popularity = (double) $this->rawElement['popularity'];
        }
        return $this;
    }

    /**
     * Extract average vote value for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractVoteAverage() : self {
        $this->voteAverage = (double) $this->rawElement['vote_average'];
        return $this;
    }

    /**
     * Extract total vote count for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractVoteCount() : self {
        $this->voteCount = (integer) $this->rawElement['vote_count'];
        return $this;
    }

    /**
     * Extract backdrop image name and clear it for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractBackdrop() : self {
        if ($this->hasOwnProperty('backdrop_path')) {
            $this->backdrop = $this->clearPath($this->rawElement['backdrop_path']);
        }
        return $this;
    }

    /**
     * Extract poster image name and clear it for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractPoster() : self {
        if ($this->hasOwnProperty('poster_path')) {
            $this->poster = $this->clearPath($this->rawElement['poster_path']);
        }
        return $this;
    }

    /**
     * Extract still image name and clear it for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractStill() : self {
        if ($this->hasOwnProperty('still_path')) {
            $this->still = $this->clearPath($this->rawElement['still_path']);
        }
        return $this;
    }

    /**
     * Extract status for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractStatus() : self {
        if ($this->hasOwnProperty('status')) {
            $this->status = $this->stringStatusToInteger();
        }
        return $this;
    }

    /**
     * Extract production companies for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractProductionCompanies() : self {
        if ($this->hasOwnProperty('production_companies')) {
            foreach ($this->rawElement['production_companies'] as $company) {
                $this->productionCompanies[] = [
                    'id'        =>  $company['id'],
                    'logo'      =>  $this->clearPath($company['logo_path']),
                    'name'      =>  $company['name'],
                    'country'   =>  $company['origin_country']
                ];
            }
        }
        return $this;
    }

    /**
     * Extract genres for the provided raw element
     * @return AbstractProcessor|static|self|$this
     */
    private function extractGenres() : self {
        if ($this->hasOwnProperty('genres')) {
            foreach ($this->rawElement['genres'] as $genre) {
                $this->genres[] = [
                    'id'    =>  $genre['id'],
                    'name'  =>  $genre['name']
                ];
            }
        }
        return $this;
    }

    /**
     * Process provided element
     * @return AbstractProcessor|static|self|$this
     */
    private function processElement() : self {
        $this
            ->extractId()
            ->extractTitle()
            ->extractOriginalTitle()
            ->extractOriginalLanguage()
            ->extractLocalTitle()
            ->extractHomePage()
            ->extractOverview()
            ->extractPopularity()
            ->extractVoteAverage()
            ->extractVoteCount()
            ->extractBackdrop()
            ->extractPoster()
            ->extractStill()
            ->extractRuntime()
            ->extractReleaseDate()
            ->extractStatus()
            ->extractProductionCompanies()
            ->extractLanguages()
            ->extractGenres()
            ->extractProcessorSpecificData();
        return $this;
    }

    /**
     * Convert status string to integer
     * @return int
     */
    private function stringStatusToInteger() : int {
        $statuses = [
            'released'          =>  self::STATUS_RELEASED,
            'returning series'  =>  self::STATUS_RUNNING,
            'ended'             =>  self::STATUS_ENDED,
            'canceled'          =>  self::STATUS_CANCELED
        ];
        $status = strtolower($this->rawElement['status']);

        if (array_key_exists($status, $statuses)) {
            return $statuses[$status];
        } else {
            return self::STATUS_UNKNOWN;
        }
    }

}
