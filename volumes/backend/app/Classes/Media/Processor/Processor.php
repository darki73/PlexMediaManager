<?php namespace App\Classes\Media\Processor;

use Carbon\Carbon;
use RuntimeException;
use App\Models\Movie as MovieModel;
use App\Models\Series as SeriesModel;
use App\Models\Episode as EpisodeModel;
use Illuminate\Database\Eloquent\Model;
use App\Classes\Media\Processor\Type\Movie;
use App\Classes\Media\Processor\Type\Series;
use App\Classes\Media\Processor\Type\Episode;
use App\Classes\TheMovieDB\Processor\Movie as MovieProcessor;
use App\Classes\TheMovieDB\Processor\Series as SeriesProcessor;
use App\Classes\TheMovieDB\Processor\Episode as EpisodeProcessor;

/**
 * Class Processor
 * @package App\Classes\Media\Processor
 */
class Processor {

    /**
     * Movie model class reference
     * @var string
     */
    public const MOVIE = MovieModel::class;

    /**
     * Series model class reference
     * @var string
     */
    public const SERIES = SeriesModel::class;

    /**
     * Episode model class reference
     * @var string
     */
    public const EPISODE = EpisodeModel::class;

    /**
     * Check if element exists in the database
     * @param string $type
     * @param string $localName
     * @return bool
     */
    public static function exists(string $type, string $localName) : bool {
        if (! in_array($type, Processor::allowedTypes())) {
            throw new RuntimeException('Type `' . $type . '` is not on the list of allowed types. Please use \App\Classes\Media\Processor class constants.');
        }
        /**
         * @var Model $class
         */
        $class = new $type;
        $element = $class->where('local_title', '=', $localName)->first();
        if ($element === null) {
            return false;
        }

        $exists = true;
        $now = Carbon::now()->setTime(0, 0, 0);

        switch ($type) {
            case Processor::MOVIE:
                $canUpdateAgain = $element->updated_at->addDays(7)->setTime(0, 0, 0);
                if ($now->greaterThanOrEqualTo($canUpdateAgain)) {
                    $exists = false;
                }
                break;
            case Processor::SERIES:
                $canUpdateAgain = $element->updated_at->addDays(1)->setTime(0, 0, 0);
                if ($now->greaterThanOrEqualTo($canUpdateAgain)) {
                    $exists = false;
                }
                break;
        }

        return $exists;
    }

    /**
     * Get movie processor instance
     * @param MovieProcessor $movieProcessor
     * @return Movie
     */
    public static function movie(MovieProcessor $movieProcessor) : Movie {
        return new Movie($movieProcessor);
    }

    /**
     * Get series processor instance
     * @param SeriesProcessor $seriesProcessor
     * @return Series
     */
    public static function series(SeriesProcessor $seriesProcessor) : Series {
        return new Series($seriesProcessor);
    }

    /**
     * Get episode processor instance
     * @param EpisodeProcessor $episodeProcessor
     * @return Episode
     */
    public static function episode(EpisodeProcessor $episodeProcessor) : Episode {
        return new Episode($episodeProcessor);
    }

    /**
     * List of allowed types
     * @return array
     */
    protected static function allowedTypes() : array {
        return [
            Processor::MOVIE,
            Processor::SERIES,
            Processor::EPISODE
        ];
    }

}
