<?php namespace App\Classes\Integrations\Telegram\Entities;

/**
 * Class User
 * @package App\Classes\Integrations\Telegram\Entities
 */
class User extends AbstractEntity {

    /**
     * Will be true if user is bot
     * @var bool
     */
    protected $isBot = false;

    /**
     * 	User‘s or bot’s first name
     * @var null|string
     */
    protected $firstName = null;

    /**
     * [OPTIONAL] User‘s or bot’s last name
     * @var null|string
     */
    protected $lastName = null;

    /**
     * [OPTIONAL] User‘s or bot’s username
     * @var null|string
     */
    protected $username = null;

    /**
     * [OPTIONAL] IETF language tag of the user's language
     * @var null|string
     */
    protected $languageCode = null;

    /**
     * Is this user bot or actual user
     * @return bool
     */
    public function isBot() : bool {
        return $this->isBot();
    }

    /**
     * Get first name of the user
     * @return string|null
     */
    public function getFirstName() : ?string {
        return $this->firstName;
    }

    /**
     * Set first name for the user
     * @param string $firstName
     * @return User|static|self|$this
     */
    public function setFirstName(string $firstName) : self {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get last name of the user
     * @return string|null
     */
    public function getLastName() : ?string {
        return $this->lastName;
    }

    /**
     * Set last name for the user
     * @param string $lastName
     * @return User|static|self|$this
     */
    public function setLastName(string $lastName) : self {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Get the username of the user
     * @return string|null
     */
    public function getUsername() : ?string {
        return $this->username;
    }

    /**
     * Set the username for the user
     * @param string $username
     * @return User|static|self|$this
     */
    public function setUsername(string $username) : self {
        $this->username = $username;
        return $this;
    }

    /**
     * Get the language code of the user
     * @return string|null
     */
    public function getLanguageCode() : ?string {
        return $this->languageCode;
    }

    /**
     * Set the language code for the user
     * @param string $languageCode
     * @return User|static|self|$this
     */
    public function setLanguageCode(string $languageCode) : self {
        $this->languageCode = $languageCode;
        return $this;
    }

}
