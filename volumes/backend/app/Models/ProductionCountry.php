<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductionCompany
 * @package App\Models
 */
class ProductionCountry extends Model {

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'production_countries';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'id',
        'code',
        'name'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'            =>  'string',
        'code'          =>  'string',
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
