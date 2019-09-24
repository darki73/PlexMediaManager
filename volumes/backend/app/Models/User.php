<?php namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 * @package App\Models
 */
class User extends Authenticatable implements MustVerifyEmail {

    use Notifiable;

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'email_verified_at',
        'password'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $casts = [
        'id'                =>  'integer',
        'username'          =>  'string',
        'email'             =>  'string',
        'email_verified_at' =>  'datetime',
        'password'          =>  'string',
        'remember_token'    =>  'string'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
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
     * Get all requests submitted by the user
     * @return HasMany
     */
    public function requests() : HasMany {
        return $this->hasMany(Request::class, 'user_id', 'id');
    }

}
