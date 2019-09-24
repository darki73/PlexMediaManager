<?php namespace App\Classes\Media\Source;

use App\Classes\Media\Source\Type\Movies;
use App\Classes\Media\Source\Type\Series;

/**
 * Class Source
 * @package App\Classes\Media\Source
 */
class Source {

    /**
     * Get instance of Series type
     * @return Series
     */
    public static function series() : Series {
        return new Series;
    }

    /**
     * Get instance of Movies type
     * @return Movies
     */
    public static function movies() : Movies {
        return new Movies;
    }

}
