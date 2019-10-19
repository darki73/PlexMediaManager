<?php namespace App\Classes\TheMovieDB\Endpoint;

use Illuminate\Support\Facades\Cache;

/**
 * Class Discover
 * @package App\Classes\TheMovieDB\Endpoint
 */
class Discover extends AbstractEndpoint {

    /**
     * Discover Movies
     * @var string
     */
    public const MOVIES = 'movie';

    /**
     * Discover Movies
     * @var string
     */
    public const SERIES = 'tv';

    /**
     * Sort By: Average Rating (From highest to lowest)
     * @var string
     */
    public const SORT_AVERAGE_RATING_DESC = 'vote_average.desc';

    /**
     * Sort By: Average Rating (From lowest to highest)
     * @var string
     */
    public const SORT_AVERAGE_RATING_ASC = 'vote_average.asc';

    /**
     * Sort By: Release Date (From highest to lowest)
     * @var string
     */
    public const SORT_RELEASE_DATE_DESC = 'first_air_date.desc';

    /**
     * Sort By: Release Date (From lowest to highest)
     * @var string
     */
    public const SORT_RELEASE_DATE_ASC = 'first_air_date.asc';

    /**
     * Sort By: Popularity (From highest to lowest)
     * @var string
     */
    public const SORT_POPULARITY_DESC = 'popularity.desc';

    /**
     * Sort By: Popularity (From lowest to highest)
     * @var string
     */
    public const SORT_POPULARITY_ASC = 'popularity.asc';

    /**
     * Type discovery we are going to use
     * @var string|null
     */
    protected ?string $type = null;

    /**
     * Sorting Order
     * @var string
     */
    protected string $sort = 'popularity.desc';

    /**
     * Items will be returned in the following language
     * @var string
     */
    protected string $language = 'en';

    /**
     * Set the discovery type
     * @param string $type
     * @return Discover|static|self|$this
     */
    public function for(string $type = Discover::SERIES) : self {
        $allowedTypes = [Discover::MOVIES, Discover::SERIES];
        if (!in_array($type, $allowedTypes, true)) {
            throw  new \RuntimeException('Invalid discovery type `' . $type . '` provided, allowed discovery types are: ' . implode(', ', $allowedTypes));
        }
        $this->type = $type;
        return $this;
    }

    /**
     * Apply custom sorting options
     * @param string $sort
     * @return Discover|static|self|$this
     */
    public function sort(string $sort = Discover::SORT_POPULARITY_DESC) : self {
        $allowedTypes = [
            Discover::SORT_AVERAGE_RATING_DESC,
            Discover::SORT_AVERAGE_RATING_ASC,
            Discover::SORT_RELEASE_DATE_DESC,
            Discover::SORT_RELEASE_DATE_ASC,
            Discover::SORT_POPULARITY_DESC,
            Discover::SORT_POPULARITY_ASC,
        ];
        if (!in_array($sort, $allowedTypes, true)) {
            throw  new \RuntimeException('Invalid sort option `' . $sort . '` provided, allowed sorting options are: ' . implode(', ', $allowedTypes));
        }
        if ($sort === Discover::SORT_RELEASE_DATE_ASC || $sort === Discover::SORT_RELEASE_DATE_DESC) {
            switch ($this->type) {
                case 'movie';
                    $this->sort = str_replace('first_air_date', 'release_date', $sort);
                    break;
                case 'tv':
                default:
                    $this->sort = $sort;
                    break;
            }
        } else {
            $this->sort = $sort;
        }
        $this->options['sort_by'] = $this->sort;
        return $this;
    }

    /**
     * Set language for request
     * @param string $language
     * @return Discover|static|self|$this
     */
    public function language(string $language = 'en') : self {
        $this->language = $this->options['language'] = $language;
        return $this;
    }

    /**
     * Fetch results
     * @param bool $forceRefresh
     * @return array
     */
    public function fetch(bool $forceRefresh = false) : array {
        $key = $this->buildCacheKey();
        if ($forceRefresh) {
            Cache::forget($key);
        }
        return Cache::remember($key, now()->addMinutes(30), function() {
            $response = $this->client->get(sprintf('%s/%d/discover/%s', $this->baseURL, $this->version, $this->type), [
                'query'     =>  $this->options
            ]);
            return json_decode($response->getBody()->getContents(), true);
        });
    }

    /**
     * Generate cache key
     * @return string
     */
    protected function buildCacheKey() : string {
        return sprintf('tmdb::api:discover:%s', md5(sprintf(
            '%s:%s:%d:%s',
            $this->type,
            $this->sort,
            $this->options['page'],
            $this->language
        )));
    }

}
