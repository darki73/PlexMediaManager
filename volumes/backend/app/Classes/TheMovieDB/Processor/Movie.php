<?php namespace App\Classes\TheMovieDB\Processor;

/**
 * Class Movie
 * @package App\Classes\TheMovieDB\Processor
 */
class Movie extends AbstractProcessor {

    /**
     * Movie budget
     * @var integer|null
     */
    protected $budget = null;

    /**
     * Movie revenue
     * @var integer|null
     */
    protected $revenue = null;

    /**
     * Whether or not this is adult movie
     * @var boolean
     */
    protected $adult = false;

    /**
     * Movie tagline
     * @var string|null
     */
    protected $tagline = null;

    /**
     * Movie IMDB id
     * @var string|null
     */
    protected $imdbId = null;

    /**
     * Movie production countries
     * @var array|null
     */
    protected $productionCountries = null;

    /**
     * Series translations
     * @var array|null
     */
    protected ?array $translations = null;

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractTitle(): AbstractProcessor {
        $this->title = $this->rawElement['title'];
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractOriginalTitle(): AbstractProcessor {
        $this->originalTitle = $this->rawElement['original_title'];
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractRuntime(): AbstractProcessor {
        $this->runtime = (integer) $this->rawElement['runtime'];
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractReleaseDate(): AbstractProcessor {
        $this->releaseDate = $this->rawElement['release_date'];
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractLanguages(): AbstractProcessor {
        foreach ($this->rawElement['spoken_languages'] as $language) {
            $this->languages[] = $language['iso_639_1'];
        }
        return $this;
    }

    /**
     * @inheritDoc
     * @return AbstractProcessor|static|self|$this
     */
    protected function extractProcessorSpecificData(): AbstractProcessor {
        $this
            ->extractBudget()
            ->extractRevenue()
            ->extractAdult()
            ->extractTagline()
            ->extractIMDBId()
            ->extractProductionCountries()
            ->extractTranslations();
        return $this;
    }

    /**
     * Extract movie budget value
     * @return Movie|static|self|$this
     */
    private function extractBudget() : self {
        $this->budget = $this->rawElement['budget'];
        return $this;
    }

    /**
     * Extract movie revenue value
     * @return Movie|static|self|$this
     */
    private function extractRevenue() : self {
        $this->revenue = $this->rawElement['revenue'];
        return $this;
    }

    /**
     * Extract movie adult value
     * @return Movie|static|self|$this
     */
    private function extractAdult() : self {
        $this->adult = (boolean) $this->rawElement['adult'];
        return $this;
    }

    /**
     * Extract movie tagline value
     * @return Movie|static|self|$this
     */
    private function extractTagline() : self {
        $this->tagline = $this->rawElement['tagline'];
        return $this;
    }

    /**
     * Extract movie imdb id
     * @return Movie|static|self|$this
     */
    private function extractIMDBId() : self {
        $this->imdbId = $this->rawElement['imdb_id'];
        return $this;
    }

    /**
     * Extract movie production countries
     * @return Movie|static|self|$this
     */
    private function extractProductionCountries() : self {
        foreach ($this->rawElement['production_countries'] as $country) {
            $this->productionCountries[] = [
                'id'        =>  strtolower($country['iso_3166_1']),
                'code'      =>  $country['iso_3166_1'],
                'name'      =>  $country['name']
            ];
        }
        return $this;
    }

    /**
     * Extract series translations
     * @return Series|static|self|$this
     */
    private function extractTranslations() : self {
        $allowedLocales = [
            'ar',
            'de',
            'en',
            'es',
            'fr',
            'ja',
            'ko',
            'no',
            'ru',
            'uk',
            'zh'
        ];

        $id = $this->rawElement['id'];
        $translations = [
            0               =>  [
                'id'        =>  $id
            ]
        ];
        foreach ($this->rawElement['translations']['translations'] as $localeData) {
            $localeCode = $localeData['iso_639_1'];
            if (in_array($localeCode, $allowedLocales)) {
                $details = $localeData['data'];
                $title = strlen($details['title']) > 0 ? $details['title'] : $this->rawElement['original_title'];
                $overview = strlen($details['overview']) > 0 ? str_replace(["\n", "\r", "\n\r", "\r\n"], '', $details['overview']) : $this->rawElement['overview'];
                $translations[0]['locale_' . $localeCode . '_title'] = strlen($title) > 100 ? $this->rawElement['original_title'] : $title;
                $translations[0]['locale_' . $localeCode . '_overview'] = $overview;
            }
        }
        $this->translations = $translations;
        return $this;
    }

}
