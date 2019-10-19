<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Class Genre
 * @package App\Models
 */
class Series extends Model {

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

}
