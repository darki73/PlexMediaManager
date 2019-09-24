<?php namespace App\Classes\Jackett\Enums;

use ReflectionClass;
use ReflectionException;

/**
 * Class AbstractEnum
 * @package App\Classes\Jackett\Enums
 */
abstract class AbstractEnum {

    /**
     * Convert Enum to array for later use
     * @return array
     * @throws ReflectionException
     */
    public static function toArray() : array {
        return static::getConstants();
    }

    /**
     * Get constants of the class (which behaves like ENUM)
     * @return array
     * @throws ReflectionException
     */
    protected static function getConstants() : array {
        $reflectionClass = new ReflectionClass(static::class);
        return $reflectionClass->getConstants();
    }

}
