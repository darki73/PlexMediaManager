<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Integration
 * @package App\Models
 */
class Integration extends Model {

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'integrations';

    /**
     * @inheritDoc
     * @var string
     */
    protected $primaryKey = 'integration';

    /**
     * @inheritDoc
     * @var bool
     */
    protected $increments = false;

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'integration',
        'enabled',
        'configuration'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'integration'       =>  'string',
        'enabled'           =>  'boolean',
        'configuration'     =>  'array'
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
