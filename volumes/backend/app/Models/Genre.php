<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Genre
 * @package App\Models
 */
class Genre extends Model {

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'genres';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'id',
        'name'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'            =>  'integer',
        'name'          =>  'string'
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
