<?php namespace App\Classes\Core;

use RuntimeException;
use Illuminate\Hashing\HashManager;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * Class Authenticator
 * @package App\Classes\Core
 */
class Authenticator {

    /**
     * Hash Manager Instance
     * @var HashManager|null
     */
    protected $hashManager = null;

    /**
     * Authenticator constructor.
     * @param HashManager $hashManager
     */
    public function __construct(HashManager $hashManager) {
        $this->hashManager = $hashManager;
    }

    /**
     * Attempt to authenticate user with given credentials
     * @param string $email
     * @param string $password
     * @param string $provider
     *
     * @return Authenticatable|null
     */
    public function attempt(string $email, string $password, string $provider) : ? Authenticatable {
        if (! $model = config('auth.providers.' . $provider . '.model')) {
            throw new RuntimeException('Unable to determine authentication model from configuration.');
        }

        if (! $user = (new $model)->where('email', '=', $email)->first()) {
            return null;
        }

        if (! $this->hasher->check($password, $user->getAuthPassword())) {
            return null;
        }
        return $user;
    }

}
