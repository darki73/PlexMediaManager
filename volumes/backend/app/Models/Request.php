<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Request
 * @package App\Models
 */
class Request extends Model {

    /**
     * @inheritDoc
     * @var string
     */
    protected $table = 'requests';

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'user_id',
        'request_type',
        'title',
        'year',
        'status'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'            =>  'integer',
        'user_id'       =>  'integer',
        'request_type'  =>  'integer',
        'title'         =>  'string',
        'year'          =>  'integer',
        'status'        =>  'integer'
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
     * @inheritDoc
     * @var array
     */
    protected $with = [
        'user'
    ];

    /**
     * Get user from the request
     * @return BelongsTo
     */
    public function user() : BelongsTo {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
