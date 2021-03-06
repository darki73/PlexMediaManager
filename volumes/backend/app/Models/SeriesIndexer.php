<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SeriesIndexer
 * @package App\Models
 */
class SeriesIndexer extends Model {

    /**
     * @inheritDoc
     * @var string
     */
    public $primaryKey = 'series_id';

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'series_indexers';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'series_id',
        'indexer'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'series_id'     =>  'integer',
        'indexer'       =>  'string'
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
     * Get series instance from series indexer
     * @return BelongsTo
     */
    public function series() : BelongsTo {
        return $this->belongsTo(Series::class, 'series_id', 'id');
    }

    /**
     * Get all episodes linked to series
     * @return HasMany
     */
    public function episodes() : HasMany {
        return $this->hasMany(Episode::class, 'series_id', 'series_id');
    }

    /**
     * Get torrent file
     * @return HasMany
     */
    public function torrentFiles() : HasMany {
        return $this->hasMany(SeriesIndexerTorrentLink::class, 'series_id', 'series_id');
    }

    /**
     * Get list of excluded items from the indexing
     * @return HasMany
     */
    public function excludes() : HasMany {
        return $this->hasMany(SeriesIndexerExclude::class, 'series_id', 'series_id');
    }

}
