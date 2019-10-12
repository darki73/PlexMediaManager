<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SeriesIndexerExclude
 * @package App\Models
 */
class SeriesIndexerExclude extends Model {

    /**
     * @inheritDoc
     * @var bool
     */
    protected $increments = false;

    /**
     * @inheritDoc
     * @var string
     */
    protected $primaryKey = 'series_id';

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'series_indexers_excludes';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'series_id',
        'season_number'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'series_id'         =>  'integer',
        'season_number'     =>  'integer'
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
     * Get series indexer from the exclusion list
     * @return BelongsTo
     */
    public function indexer() : BelongsTo {
        return $this->belongsTo(SeriesIndexer::class, 'series_id', 'series_id');
    }

}
