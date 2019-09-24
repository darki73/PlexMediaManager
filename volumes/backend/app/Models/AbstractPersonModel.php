<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class AbstractPersonModel
 * @package App\Models
 */
abstract class AbstractPersonModel extends Model {

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'gender',
        'photo',
        'birthday',
        'deathday',
        'biography',
        'birth_place',
        'popularity',
        'imdb_id',
        'homepage',
        'adult',
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'            =>  'integer',
        'name'          =>  'string',
        'gender'        =>  'integer',
        'photo'         =>  'string',
        'birthday'      =>  'string',
        'deathday'      =>  'string',
        'biography'     =>  'string',
        'birth_place'   =>  'string',
        'popularity'    =>  'double',
        'imdb_id'       =>  'string',
        'homepage'      =>  'string',
        'adult'         =>  'boolean',
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
