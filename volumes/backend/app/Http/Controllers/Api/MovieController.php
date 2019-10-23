<?php namespace App\Http\Controllers\Api;

use App\Models\Movie;
use App\Models\PlexMediaRelation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

/**
 * Class MovieController
 * @package App\Http\Controllers\Api
 */
class MovieController extends APIMediaController {

    /**
     * Collection of movies
     * @var Movie[]|Collection|null
     */
    protected $moviesCollection = null;

    /**
     * MovieController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->moviesCollection = Movie::all();
    }

    /**
     * Get list of all movies in the database
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched list of movies', $this->cacheAllMovies());
    }

    /**
     * Put information about all movies to cache
     * @return array
     */
    public function cacheAllMovies() : array {
        return Cache::rememberForever('movies:list', function () {
            return $this->moviesCollection->map(function (Movie $movie, int $index) {
                return $this->getBaseMovieInformation($movie, [
                    'poster',
                    'backdrop',
                    'created_at',
                    'updated_at'
                ], true);
            })->toArray();
        });
    }

    /**
     * Get base information for the movie
     * @param Movie $movie
     * @param array $only
     * @param bool $except
     * @return array
     */
    public function getBaseMovieInformation(Movie $movie, array $only = [], bool $except = false) : array {
        $data = Arr::except($movie->toArray(), [
            'backdrop',
            'poster',
            'genres',
            'production_companies',
            'production_countries',
        ]);

        $data['genres'] = $this->loadGenres($movie->genres);
        $data['production_companies'] = $this->loadProductionCompanies($movie->production_companies);
        $data['production_countries'] = $this->loadProductionCountries($movie->production_countries);
        $data['plex'] = PlexMediaRelation::where('model', '=', Movie::class)->where('media_id', '=', $movie->id)->get();
        $requestDetails = $this->checkIfRequested($data['title'], $data['release_date'], 1);
        $data['requested'] = $requestDetails['requested'];
        $data['request_status'] = $requestDetails['status'];
        $translations = $movie->getModelTranslations();
        $data['title'] = $translations['title'];
        $data['overview'] = $translations['overview'];
        $data['type'] = 1;
        if ($movie->backdrop !== null) {
            $data['backdrop'] = $this->buildImagePath($movie->backdrop, 'backdrop', [
                'type'      =>  'movie',
                'id'        =>  $movie->id,
                'category'  =>  'global'
            ]);
        } else {
            $data['backdrop'] = $this->generateNotFoundImageLinks('backdrop');
        }
        if ($movie->poster !== null) {
            $data['poster'] = $this->buildImagePath($movie->poster, 'poster', [
                'type'      =>  'movie',
                'id'        =>  $movie->id,
                'category'  =>  'global'
            ]);
        } else {
            $data['poster'] = $this->generateNotFoundImageLinks('poster');
        }

        if (\count($only) > 0) {
            if ($except) {
                $data = Arr::except($data, $only);
            } else {
                $data = Arr::only($data, array_merge($only, ['plex', 'type', 'vote_average', 'requested', 'request_status']));
            }
        }

        return $data;
    }

}
