<?php namespace App\Classes\Jackett;

use RuntimeException;
use App\Classes\Jackett\Components\Client;
use App\Classes\Jackett\Indexers\AbstractIndexer;

/**
 * Class Jackett
 * @package App\Classes\Jackett
 */
class Jackett {

    /**
     * HTTP Client instance
     * @var Client|null
     */
    protected $client = null;

    /**
     * List of available indexers implementations
     * @var array
     */
    protected $implementations = [];

    /**
     * Jackett constructor.
     */
    public function __construct() {
        $this->client = new Client;
        $this->implementations = config('jackett.indexers');
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return AbstractIndexer
     */
    public function __call(string $name, array $arguments) : AbstractIndexer {
        if (! array_key_exists($name, $this->implementations)) {
            throw new RuntimeException('Indexer `' . $name . '` is not implemented, please use one of the following: [' . implode(', ', array_keys($this->implementations)) . '] or you can implement one yourself.');
        }
        return new $this->implementations[$name]($this->client);
    }

}
