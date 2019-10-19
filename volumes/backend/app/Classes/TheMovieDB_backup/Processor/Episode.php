<?php namespace App\Classes\TheMovieDB\Processor;

/**
 * Class Episode
 * @package App\Classes\TheMovieDB\Processor
 */
class Episode extends AbstractProcessor {

    /**
     * Episode number
     * @var null|integer
     */
    protected $episodeNumber = null;

    /**
     * Episode production code
     * @var null|string
     */
    protected $productionCode = null;

    /**
     * Series ID
     * @var null|integer
     */
    protected $seriesId = null;

    /**
     * Season ID
     * @var integer|null
     */
    protected $seasonId = null;

    /**
     * Season number
     * @var null|integer
     */
    protected $seasonNumber = null;

    /**
     * List of guest stars
     * @var array
     */
    protected $guestStars = [];

    /**
     * List of crew members
     * @var array
     */
    protected $crew = [];

    /**
     * @inheritDoc
     * @var array
     */
    protected $excludeFromArray = [
        'backdrop',
        'genres',
        'languages',
        'localTitle',
        'originalLanguage',
        'originalTitle',
        'popularity',
        'poster',
        'productionCompanies',
        'runtime',
        'status',
        'homepage'
    ];

    /**
     * Episode constructor.
     * @param array $information
     * @param int $seasonID
     */
    public function __construct(array $information, int $seasonID) {
        parent::__construct($information);
        $this->seasonId = $seasonID;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor
     */
    protected function extractTitle(): AbstractProcessor {
        $this->title = $this->rawElement['name'];
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor
     */
    protected function extractOriginalTitle(): AbstractProcessor {
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor
     */
    protected function extractReleaseDate(): AbstractProcessor {
        $this->releaseDate = $this->rawElement['air_date'];
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor
     */
    protected function extractRuntime(): AbstractProcessor {
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor
     */
    protected function extractLanguages(): AbstractProcessor {
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor
     */
    protected function extractProcessorSpecificData(): AbstractProcessor {
        $this
            ->extractEpisodeNumber()
            ->extractProductionCode()
            ->extractSeriesID()
            ->extractSeasonNumber()
            ->extractGuestStars()
            ->extractCrewMembers();
        return $this;
    }

    /**
     * Extract episode number
     * @return Episode|static|self|$this
     */
    protected function extractEpisodeNumber() : self {
        $this->episodeNumber = $this->rawElement['episode_number'];
        return $this;
    }

    /**
     * Extract episode production code
     * @return Episode|static|self|$this
     */
    protected function extractProductionCode() : self {
        $this->productionCode = $this->rawElement['production_code'];
        return $this;
    }

    /**
     * Extract series ID
     * @return Episode|static|self|$this
     */
    protected function extractSeriesID() : self {
        $this->seriesId = $this->rawElement['show_id'];
        return $this;
    }

    /**
     * Extract episode number
     * @return Episode|static|self|$this
     */
    protected function extractSeasonNumber() : self {
        $this->seasonNumber = $this->rawElement['season_number'];
        return $this;
    }

    /**
     * Extract guest stars for episode
     * @return Episode|static|self|$this
     */
    protected function extractGuestStars() : self {
        foreach ($this->rawElement['guest_stars'] as $star) {
            $this->guestStars[] = [
                'id'        =>  $star['id'],
                'name'      =>  $star['name'],
                'character' =>  $star['character'],
                'order'     =>  $star['order'],
                'gender'    =>  $star['gender'] ?? null,
                'photo'     =>  $this->clearPath($star['profile_path'])
            ];
        }
        return $this;
    }

    /**
     * Extract crew members for episode
     * @return Episode|static|self|$this
     */
    protected function extractCrewMembers() : self {
        foreach ($this->rawElement['crew'] as $crew) {
            $this->crew[] = [
                'id'            =>  $crew['id'],
                'name'          =>  $crew['name'],
                'department'    =>  $crew['department'],
                'job'           =>  $crew['job'],
                'photo'         =>  $this->clearPath($crew['profile_path'])
            ];
        }
        return $this;
    }

}
