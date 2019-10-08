<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class SeriesIndexerTorrentLink
 * @package App\Models
 */
class SeriesIndexerTorrentLink extends Model {

    /**
     * @inheritDoc
     * @var bool
     */
    protected $increments = false;

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'indexers_torrent_links';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'series_id',
        'season',
        'torrent_file',
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'series_id'         =>  'integer',
        'season'            =>  'integer',
        'torrent_file'      =>  'string',
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $hidden = [

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
     * Get indexer
     * @return BelongsTo
     */
    public function indexer() : BelongsTo {
        return $this->belongsTo(SeriesIndexer::class, 'series_id', 'series_id');
    }

}
