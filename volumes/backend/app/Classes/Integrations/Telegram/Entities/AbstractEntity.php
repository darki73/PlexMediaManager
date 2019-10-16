<?php namespace App\Classes\Integrations\Telegram\Entities;

use Illuminate\Support\Str;

/**
 * Class AbstractEntity
 * @package App\Classes\Integrations\Telegram\Entities
 */
abstract class AbstractEntity {

    /**
     * Map value to certain class
     * @var array
     */
    protected $map = [];

    /**
     * Entity ID
     * @var null|integer
     */
    protected $id = null;

    /**
     * AbstractEntity constructor.
     * @param array $response
     */
    public function __construct(?array $response = null) {
        if ($response !== null) {
            $this->unpackResponse($response);
        }
    }

    /**
     * Get identifier value
     * @return int
     */
    public function identifier() : ?int {
        return $this->id;
    }

    /**
     * Unpack response to class
     * @param array $response
     * @return $this
     */
    private function unpackResponse(array $response) : self {
        foreach ($response as $key => $value) {
            $property = $this->convertResponseKeyToClassProperty($key);
            if (property_exists($this, $property)) {
                if (array_key_exists($property, $this->map)) {
                    $class = $this->map[$property];
                    $this->{$property} = new $class($value);
                } else {
                    $this->{$property} = $value;
                }
            }
        }
        return $this;
    }

    /**
     * Convert response key to class property
     * @param string $key
     * @return string
     */
    private function convertResponseKeyToClassProperty(string $key) : string {
        $parts = explode('_', $key);
        if (\count($parts) === 2 && Str::endsWith($key, '_id')) {
            return 'id';
        }
        $property = '';
        foreach ($parts as $index => $value) {
            if ($index === 0) {
                $property .= $value;
            } else {
                $property .= ucfirst($value);
            }
        }
        return $property;
    }

}
