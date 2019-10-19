<?php namespace App\Classes\TheMovieDB\Processor;

/**
 * Class Series
 * @package App\Classes\TheMovieDB\Processor
 */
class Series extends AbstractProcessor {

    /**
     * Whether or not series in production
     * @var bool
     */
    protected $inProduction = false;

    /**
     * Series seasons count
     * @var int
     */
    protected $seasonsCount = 0;

    /**
     * Series episodes count
     * @var int
     */
    protected $episodesCount = 0;

    /**
     * When last episode aired
     * @var string|null
     */
    protected $lastAirDate = null;

    /**
     * Series networks list
     * @var array|null
     */
    protected $networks = null;

    /**
     * Series origin country
     * @var string
     */
    protected $originCountry = null;

    /**
     * Series seasons list
     * @var array|null
     */
    protected $seasons = null;

    /**
     * Series creators list
     * @var array|null
     */
    protected $creators = null;

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractTitle(): AbstractProcessor {
        $this->title = $this->rawElement['name'];
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractOriginalTitle(): AbstractProcessor {
        $this->originalTitle = $this->rawElement['original_name'];
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractRuntime(): AbstractProcessor {
        $this->runtime = $this->rawElement['episode_run_time'][0] ?? 45;
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractReleaseDate(): AbstractProcessor {
        $this->releaseDate = $this->rawElement['first_air_date'];
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractLanguages(): AbstractProcessor {
        $this->languages = $this->rawElement['languages'];
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractProcessorSpecificData(): AbstractProcessor {
        $this
            ->extractInProduction()
            ->extractSeasonsCount()
            ->extractEpisodesCount()
            ->extractLastAirDate()
            ->extractNetworks()
            ->extractOriginCountry()
            ->extractSeasons()
            ->extractCreators();
        return $this;
    }

    /**
     * Extract series in_production value
     * @return Series|static|self|$this
     */
    private function extractInProduction() : self {
        $this->inProduction = $this->rawElement['in_production'];
        return $this;
    }

    /**
     * Extract series seasons count
     * @return Series|static|self|$this
     */
    private function extractSeasonsCount() : self {
        $this->seasonsCount = $this->rawElement['number_of_seasons'];
        return $this;
    }

    /**
     * Extract series episodes count
     * @return Series|static|self|$this
     */
    private function extractEpisodesCount() : self {
        $this->episodesCount = $this->rawElement['number_of_episodes'];
        return $this;
    }

    /**
     * Extract series last air date
     * @return Series|static|self|$this
     */
    private function extractLastAirDate() : self {
        $this->lastAirDate = $this->rawElement['last_air_date'];
        return $this;
    }

    /**
     * Extract series networks
     * @return Series|static|self|$this
     */
    private function extractNetworks() : self {
        foreach ($this->rawElement['networks'] as $network) {
            $this->networks[] = [
                'id'        =>  $network['id'],
                'name'      =>  $network['name'],
                'logo'      =>  $this->clearPath($network['logo_path']),
                'country'   =>  $network['origin_country']
            ];
        }
        return $this;
    }

    /**
     * Extract series origin country
     * @return Series|static|self|$this
     */
    private function extractOriginCountry() : self {
        $this->originCountry = $this->rawElement['origin_country'][0] ?? null;
        return $this;
    }

    /**
     * Extract series seasons
     * @return Series|static|self|$this
     */
    private function extractSeasons() : self {
        foreach ($this->rawElement['seasons'] as $season) {
            $seasonNumber = $season['season_number'];
            if ($seasonNumber !== 0) {
                $this->seasons[$seasonNumber] = [
                    'id'                =>  $season['id'],
                    'name'              =>  $season['name'],
                    'series_id'         =>  $this->id,
                    'season_number'     =>  $seasonNumber,
                    'overview'          =>  $season['overview'],
                    'episodes_count'    =>  $season['episode_count'],
                    'poster'            =>  $this->clearPath($season['poster_path']),
                    'air_date'          =>  $season['air_date']
                ];
            }
        }
        return $this;
    }

    /**
     * Extract Series Creators
     * @return Series|static|self|$this
     */
    private function extractCreators() : self {
        foreach ($this->rawElement['created_by'] as $creator) {
            $this->creators[] = [
                'id'            =>  $creator['id'],
                'name'          =>  $creator['name'],
                'gender'        =>  $creator['gender'] ?? null,
                'photo'         =>  $this->clearPath($creator['profile_path'])
            ];
        }
        return $this;
    }

}
