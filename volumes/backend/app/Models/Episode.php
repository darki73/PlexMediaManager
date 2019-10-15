<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Episode
 * @package App\Models
 */
class Episode extends Model {

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'episodes';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'id',
        'series_id',
        'season_id',
        'season_number',
        'episode_number',
        'title',
        'overview',
        'production_code',
        'release_date',
        'still',
        'vote_count',
        'vote_average',
        'downloaded',
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'                =>  'integer',
        'series_id'         =>  'integer',
        'season_id'         =>  'integer',
        'season_number'     =>  'integer',
        'episode_number'    =>  'integer',
        'title'             =>  'string',
        'overview'          =>  'string',
        'production_code'   =>  'string',
        'release_date'      =>  'string',
        'still'             =>  'string',
        'vote_count'        =>  'integer',
        'vote_average'      =>  'float',
        'downloaded'        =>  'boolean',
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
     * Get series to which this episode belongs to
     * @return BelongsTo
     */
    public function series() : BelongsTo {
        return $this->belongsTo(Series::class, 'series_id', 'id');
    }

    /**
     * Get season to which this episode belongs to
     * @return BelongsTo
     */
    public function season() : BelongsTo {
        return $this->belongsTo(Season::class, 'season_id', 'id');
    }

    /**
     * Scope to query only downloaded episodes
     * @param Builder $builder
     * @return Builder
     */
    public function scopeDownloaded(Builder $builder) : Builder {
        return $builder->where('downloaded', '=', 1);
    }

    /**
     * Scope to query only missing episodes
     * @param Builder $builder
     * @return Builder
     */
    public function scopeMissing(Builder $builder) : Builder {
        return $builder->where('downloaded', '=', 0);
    }

}
