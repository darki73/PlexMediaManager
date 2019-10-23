<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PlexMediaRelation
 * @package App\Models
 */
class PlexMediaRelation extends Model {

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'plex_media_relations';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'model',
        'media_id',
        'plex_url',
        'server_id',
        'server_name'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'                =>  'integer',
        'model'             =>  'string',
        'media_id'          =>  'integer',
        'plex_url'          =>  'string',
        'server_id'         =>  'string',
        'server_name'       =>  'string'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

}
