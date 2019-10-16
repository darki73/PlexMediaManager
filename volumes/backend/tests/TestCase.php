<?php namespace Tests;

use App\Models\Series;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Class TestCase
 * @package Tests
 */
abstract class TestCase extends BaseTestCase {
    use CreatesApplication;

    /**
     * Create dummy series instance to work with
     * @return Series
     */
    protected function dummySeries() : Series {
        return new Series([
            'id'                    =>  66788,
            'title'                 =>  '13 Reasons Why',
            'original_title'        =>  '13 Reasons Why',
            'local_title'           =>  '13 Reasons Why',
            'original_language'     =>  'en',
            'overview'              =>  'After a teenage girl\'s perplexing suicide, a classmate receives a series of tapes that unravel the mystery of her tragic choice.',
            'homepage'              =>  'https://www.netflix.com/title/80117470',
            'runtime'               =>  57,
            'status'                =>  2,
            'episodes_count'        =>  39,
            'seasons_count'         =>  3,
            'release_date'          =>  '2017-03-31',
            'last_air_date'         =>  '2019-08-23',
            'origin_country'        =>  'US',
            'in_production'         =>  1,
            'vote_average'          =>  7.1,
            'vote_count'            =>  901,
            'popularity'            =>  34756,
            'backdrop'              =>  'sZb21d6EWKAEKZ9GrLQeMwX4cWN.jpg',
            'poster'                =>  'nel144y4dIOdFFid6twN5mAX9Yd.jpg',
            'created_at'            =>  '2019-09-18 00:25:47',
            'updated_at'            =>  '2019-10-16 00:00:32'
        ]);
    }

}
