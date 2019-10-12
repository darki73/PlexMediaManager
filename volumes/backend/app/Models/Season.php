<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Season
 * @package App\Models
 */
class Season extends Model {

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'seasons';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'series_id',
        'season_number',
        'overview',
        'episodes_count',
        'poster',
        'air_date',
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'                =>  'integer',
        'name'              =>  'string',
        'series_id'         =>  'integer',
        'season_number'     =>  'integer',
        'overview'          =>  'string',
        'episodes_count'    =>  'integer',
        'poster'            =>  'string',
        'air_date'          =>  'string',
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
     * @var array
     */
    protected $appends = [
        'episodes_downloaded'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $with = [
        'episodes'
    ];

    /**
     * Get series instance from season
     * @return BelongsTo
     */
    public function series() : BelongsTo {
        return $this->belongsTo(Series::class, 'series_id', 'id');
    }

    /**
     * Get list of episodes for current season
     * @return HasMany
     */
    public function episodes() : HasMany {
        return $this->hasMany(Episode::class, 'season_id', 'id');
    }

    /**
     * Count downloaded episodes for season
     * @return int
     */
    public function getEpisodesDownloadedAttribute() : int {
        return Episode::where('series_id', '=', $this->series_id)->where('season_number', '=', $this->season_number)->where('downloaded', '=', true)->count();
    }

}
