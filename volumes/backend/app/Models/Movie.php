<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Movie
 * @package App\Models
 */
class Movie extends Model {

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'movies';

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
        'tagline',
        'genres',
        'homepage',
        'runtime',
        'status',
        'adult',
        'imdb_id',
        'release_date',
        'production_companies',
        'production_countries',
        'vote_average',
        'vote_count',
        'popularity',
        'budget',
        'revenue',
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
        'tagline'               =>  'string',
        'genres'                =>  'array',
        'homepage'              =>  'string',
        'runtime'               =>  'integer',
        'status'                =>  'integer',
        'adult'                 =>  'boolean',
        'imdb_id'               =>  'string',
        'release_date'          =>  'string',
        'production_companies'  =>  'array',
        'production_countries'  =>  'array',
        'vote_average'          =>  'double',
        'vote_count'            =>  'integer',
        'popularity'            =>  'double',
        'budget'                =>  'integer',
        'revenue'               =>  'integer',
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

}


