<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class ProductionCompany
 * @package App\Models
 */
class ProductionCompany extends Model {

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'production_companies';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'id',
        'name',
        'country',
        'logo'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'            =>  'integer',
        'name'          =>  'string',
        'country'       =>  'string',
        'logo'          =>  'string'
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
