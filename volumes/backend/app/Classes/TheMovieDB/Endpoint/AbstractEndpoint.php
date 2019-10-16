<?php namespace App\Classes\TheMovieDB\Endpoint;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use LanguageDetection\Language;

/**
 * Class AbstractEndpoint
 * @package App\Classes\TheMovieDB\Endpoint
 */
abstract class AbstractEndpoint {

    /**
     * Russian Language Constant Variable
     * @var string
     */
    public const LANGUAGE_RUSSIAN = 'ru';

    /**
     * English Language Constant Variable
     * @var string
     */
    public const LANGUAGE_ENGLISH = 'en';

    /**
     * Deutsch Language Constant Variable
     * @var string
     */
    public const LANGUAGE_DEUTSCH = 'de';

    /**
     * Spanish Language Constant Variable
     * @var string
     */
    public const LANGUAGE_SPANISH = 'es';

    /**
     * API Version 3
     * @var integer
     */
    public const VERSION_3 = 3;

    /**
     * API Version 4
     * @var integer
     */
    public const VERSION_4 = 4;

    /**
     * API Base URL
     * @var string
     */
    protected string $baseURL = 'https://api.themoviedb.org';

    /**
     * GuzzleHTTP Client Instance
     * @var Client|null
     */
    protected ?Client $client = null;

    /**
     * API Version
     * @var int
     */
    protected int $version = 3;

    /**
     * API Language
     * @var string
     */
    protected string $language = 'en';

    /**
     * TheMovieDB Configuration Settings
     * @var array|null
     */
    protected ?array $configuration = null;

    /**
     * Request Options
     * @var array
     */
    protected array $options = [];

    /**
     * AbstractEndpoint constructor.
     */
    public function __construct() {
        $this->client = new Client;
        $this->buildBaseOptions();
        if (Cache::has('tmdb::api:configuration')) {
            $this->configuration = Cache::get('tmdb::api:configuration');
        } else {
            if (!$this instanceof Configuration) {
                $this->configuration = (new Configuration)->fetch();
            }
        }
    }

    /**
     * Get Current API Version
     * @return int
     */
    public function getVersion() : int {
        return $this->version;
    }

    /**
     * Set Desired API Version
     * @param int $version
     * @return AbstractEndpoint|static|self|$this
     */
    public function setVersion(int $version = AbstractEndpoint::VERSION_3) : self {
        $this->version = $version;
        return $this;
    }

    /**
     * Get Current API Language
     * @return string
     */
    public function getLanguage() : string {
        return $this->language;
    }

    /**
     * Set Desired API Language
     * @param string $language
     * @return AbstractEndpoint|static|self|$this
     */
    public function setLanguage(string $language = AbstractEndpoint::LANGUAGE_ENGLISH) : self {
        $this->language = $language;
        return $this;
    }

    /**
     * Set Page
     * @param int $page
     * @return AbstractEndpoint|static|self|$this
     */
    public function page(int $page = 1) : self {
        $this->options['page'] = $page;
        return $this;
    }

    /**
     * Detect the best language for query
     * @return void
     */
    protected function detectLanguage() : void {
        $languageDetect = (new Language)->detect($this->query)->whitelist(... config('search.languages'));
        $this->options['language'] = $this->language = array_key_first($languageDetect->bestResults()->close());
        return;
    }

    /**
     * Build Base Options
     * @return AbstractEndpoint|static|self|$this
     */
    protected function buildBaseOptions() : self {
        $this->options = [
            'api_key'       =>  env('TMDB_API_KEY'),
            'language'      =>  $this->language,
            'adult'         =>  true,
            'include_adult' =>  true
        ];
        return $this;
    }

}
