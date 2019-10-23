<?php namespace App\Models;

use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Genre
 * @package App\Models
 */
class Series extends AbstractMediaModel {
    use Searchable;

    /**
     * @inheritDoc
     * @var string|null
     */
    protected ?string $translationClass = SeriesTranslation::class;

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'series';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'id',
        'title',
        'original_title',
        'local_title',
        'original_language',
        'languages',
        'overview',
        'genres',
        'homepage',
        'runtime',
        'status',
        'episodes_count',
        'seasons_count',
        'release_date',
        'last_air_date',
        'origin_country',
        'in_production',
        'production_companies',
        'creators',
        'networks',
        'vote_average',
        'vote_count',
        'popularity',
        'backdrop',
        'poster',
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'                    =>  'integer',
        'title'                 =>  'string',
        'original_title'        =>  'string',
        'local_title'           =>  'string',
        'original_language'     =>  'string',
        'languages'             =>  'array',
        'overview'              =>  'string',
        'genres'                =>  'array',
        'homepage'              =>  'string',
        'runtime'               =>  'integer',
        'status'                =>  'integer',
        'episodes_count'        =>  'integer',
        'seasons_count'         =>  'integer',
        'release_date'          =>  'string',
        'last_air_date'         =>  'string',
        'origin_country'        =>  'string',
        'in_production'         =>  'boolean',
        'production_companies'  =>  'array',
        'creators'              =>  'array',
        'networks'              =>  'array',
        'vote_average'          =>  'double',
        'vote_count'            =>  'integer',
        'popularity'            =>  'double',
        'backdrop'              =>  'string',
        'poster'                =>  'string',
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @inheritDoc
     * @return string
     */
    public function searchableAs() : string {
        return 'series';
    }

    /**
     * @inheritDoc
     * @return array
     */
    public function toSearchableArray() : array {
        $array = Arr::only($this->toArray(), [
            'id', 'title', 'original_title', 'original_language', 'overview',
            'genres', 'origin_country', 'production_companies', 'creators',
            'networks', 'vote_average', 'popularity', 'release_date'
        ]);
        if ($array['id'] === 0) {
            return [];
        }
        $translations = $this->getModelTranslations(true);
        $array['title'] = $translations['title'];
        $array['overview'] = $translations['overview'];
        $array['production_companies'] = ProductionCompany::findMany($array['production_companies'])->toArray();
        $array['creators'] = Creator::findMany($array['creators'])->toArray();
        $array['networks'] = Network::findMany($array['networks'])->toArray();
        $array['genres'] = Genre::findMany($array['genres'])->toArray();
        return $array;
    }


    /**
     * Get indexer assigned to series
     * @return HasOne
     */
    public function indexer() : HasOne {
        return $this->hasOne(SeriesIndexer::class, 'series_id', 'id');
    }

    /**
     * Get all seasons for selected series
     * @return HasMany
     */
    public function seasons() : HasMany {
        return $this->hasMany(Season::class, 'series_id', 'id');
    }

    /**
     * Get all episodes for selected series
     * @return HasMany
     */
    public function episodes() : HasMany {
        return $this->hasMany(Episode::class, 'series_id', 'id');
    }

    /**
     * Get translation for selected series
     * @return HasOne
     */
    public function translation() : HasOne {
        return $this->hasOne(SeriesTranslation::class, 'id', 'id');
    }

    /**
     * Get number of seasons available for the series
     * @return int
     */
    public function seasonsCount() : int {
        return $this->seasons()->count();
    }

    /**
     * Count downloaded episodes for series
     * @return int
     */
    public function downloadedEpisodesCount() : int {
        return $this->episodes()->where('downloaded', '=', 1)->count();
    }

    /**
     * Count missing episodes for series
     * @return int
     */
    public function missingEpisodesCount() : int {
        return $this->episodes()->where('downloaded', '=', 0)->count();
    }

    /**
     * Count number of episodes available in the database
     * @return int
     */
    public function episodesCount() : int {
        return $this->episodes()->count();
    }

    /**
     * Count total number of episodes for series
     * @return int
     */
    public function episodesTotal() : int {
        return $this->seasons()->sum('episodes_count');
    }

}
