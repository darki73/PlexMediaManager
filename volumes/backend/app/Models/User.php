<?php namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class User
 * @package App\Models
 */
class User extends Authenticatable implements MustVerifyEmail {

    use Notifiable, HasApiTokens, HasRoles;

    /**
     * @inheritDoc
     * @var array
     */
    protected $fillable = [
        'username',
        'email',
        'email_verified_at',
        'password',
        'avatar'
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
        'remember_token'    =>  'string',
        'avatar'            =>  'string'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'avatar'
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
     * Get roles associated with user account
     * @var array
     */
    protected $with = [
        'roles'
    ];

    /**
     * @inheritDoc
     * @var array
     */
    protected $appends = [
        'avatar_url'
    ];

    /**
     * Get all requests submitted by the user
     * @return HasMany
     */
    public function requests() : HasMany {
        return $this->hasMany(Request::class, 'user_id', 'id');
    }

    /**
     * Create avatar url
     * @return string
     */
    public function getAvatarUrlAttribute() : string {
        return sprintf('https://%s%s', str_replace(['http://', 'https://'], '', env('APP_URL')), \Storage::url(
            implode(DIRECTORY_SEPARATOR, [
                'public',
                'avatars',
                $this->username,
                $this->avatar
            ])
        ));
    }

}
