<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class PlexUser
 * @package App\Models
 */
class PlexUser extends Model {

    /**
     * @inheritDoc
     * @var bool
     */
    public $incrementing = false;

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'plex_users';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'id',
        'uuid',
        'title',
        'username',
        'email',
        'admin',
        'guest',
        'avatar',
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'            =>  'integer',
        'uuid'          =>  'string',
        'title'         =>  'string',
        'username'      =>  'string',
        'email'         =>  'string',
        'admin'         =>  'boolean',
        'guest'         =>  'boolean',
        'avatar'        =>  'string',
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
