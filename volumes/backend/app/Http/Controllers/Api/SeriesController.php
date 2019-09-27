<?php namespace App\Http\Controllers\Api;

use App\Models\Season;
use App\Models\Series;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Class SeriesController
 * @package App\Http\Controllers\Api
 */
class SeriesController extends APIMediaController {

    /**
     * Series Collection Array
     * @var Series[]|Collection|null
     */
    protected $seriesCollection = null;

    /**
     * SeriesController constructor.
     */
    public function __construct() {
        parent::__construct();
        $this->seriesCollection = Series::all();
    }

    /**
     * Get list of all series in the database
     * @param Request $request
     * @return JsonResponse
     */
    public function list(Request $request) : JsonResponse {
        // TODO: Check the caching mechanism later (smells fishy)
        return $this->sendResponse('Successfully fetched list of series', Cache::rememberForever('series:list', function () {
            return $this->seriesCollection->map(function (Series $series, int $key) {
                return $this->getBaseSeriesInformation($series, [
                    'id',
                    'title',
                    'original_title',
                    'local_title',
                    'original_language',
                    'overview',
                    'backdrop',
                    'poster',
                    'genres',
                    'seasons_count',
                    'runtime'
                ]);
            })->toArray();
        }));
    }

    /**
     * Get information for single series
     * @param Request $request
     * @param string|integer $id
     * @return JsonResponse
     */
    public function getSingleSeries(Request $request, $id) : JsonResponse {
        if (! ctype_digit($id)) {
            return $this->errorSeriesIDisNotAnInteger($id);
        }
        $id = (integer) $id;

        $series = $this->getSingleSeriesByID($id);

        if ($series === null) {
            return $this->errorSeriesNotFoundByID($id);
        }
        return $this->sendResponse('Successfully fetched information for series with ID: ' . $id, $this->getBaseSeriesInformation($series));
    }

    /**
     * Get seasons for series by its ID
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function getSeriesSeasons(Request $request, $id) : JsonResponse {
        if (! ctype_digit($id)) {
            return $this->errorSeriesIDisNotAnInteger($id);
        }
        $id = (integer) $id;

        $series = $this->getSingleSeriesByID($id);

        if ($series === null) {
            return $this->errorSeriesNotFoundByID($id);
        }

        $seasons = $series->seasons->map(function (Season $season, int $index) {
            $returnArray = $season->toArray();
            $returnArray['poster'] = $this->buildImagePath($season->poster, 'poster', [
                'type'      =>  'series',
                'id'        =>  $season->series_id,
                'category'  =>  'seasons'
            ]);
            return $returnArray;
        })->toArray();

        return $this->sendResponse(sprintf('Successfully fetched seasons for series: `%s` (%d)', $series->title, $series->id), $seasons);
    }

    /**
     * Get information for specific season of series
     * @param Request $request
     * @param mixed $seriesID
     * @param mixed $seasonNumber
     * @return JsonResponse
     */
    public function getSeasonInformationForSeries(Request $request, $seriesID, $seasonNumber) : JsonResponse {
        if (! ctype_digit($seriesID)) {
            return $this->errorSeriesIDisNotAnInteger($seriesID);
        }
        $seriesID = (integer) $seriesID;

        if (! ctype_digit($seasonNumber)) {
            return $this->errorSeasonNumberIsNotAnInteger($seasonNumber);
        }
        $seasonNumber = (integer) $seasonNumber;

        $series = $this->getSingleSeriesByID($seriesID);

        if ($series === null) {
            return $this->errorSeriesNotFoundByID($seriesID);
        }

        $season = $series->seasons->reject(function (Season $season) use ($seasonNumber) {
            return $season->season_number !== $seasonNumber;
        })->first();

        if ($season === null) {
            return $this->errorSeasonNotFound($series, $seasonNumber);
        }

        $seasonArray = $season->toArray();
        $seasonArray['poster'] = $this->buildImagePath($season->poster, 'poster', [
            'type'      =>  'series',
            'id'        =>  $season->series_id,
            'category'  =>  'seasons'
        ]);

        // TODO: Do something with episodes, right now they are here just "for fun"
        $seasonArray['episodes'] = $season->episodes->toArray();

        return $this->sendResponse(sprintf('Successfully fetched information for series `%s (%d)` and season `%d`', $series->title, $seriesID, $seasonNumber), $seasonArray);
    }

    /**
     * Get list of missing episodes (those which were not yet downloaded)
     * @param Request $request
     * @return JsonResponse
     */
    public function getMissingEpisodes(Request $request) : JsonResponse {
        $missingEpisodes = \App\Models\Episode::where('downloaded', '=', false)->orderBy('series_id', 'ASC')->get();
        $currentDate = \Carbon\Carbon::now();
        $result = [];

        foreach ($missingEpisodes as $episode) {
            $series = $episode->series;
            $aired = \Carbon\Carbon::createFromDate($episode->release_date)->addDays(3);
            if ($episode->season_number !== 0 && $aired->lessThan($currentDate)) {
                $result[] = [
                    'series_id'         =>  $series->id,
                    'series_name'       =>  $series->title,
                    'season_number'     =>  $episode->season_number,
                    'episode_id'        =>  $episode->id,
                    'episode_number'    =>  $episode->episode_number,
                    'aired_on'          =>  $aired
                ];
            }
        }

        return $this->sendResponse('Successfully fetched list of missing episodes', $result);
    }

    /**
     * Get base series information
     * @param Series $series
     * @param array $only
     * @return array
     */
    private function getBaseSeriesInformation(Series $series, array $only = []) : array {
        $data = Arr::except($series->toArray(), [
            'backdrop',
            'poster',
            'genres',
            'production_companies',
            'creators',
            'networks'
        ]);
        $data['backdrop'] = $this->buildImagePath($series->backdrop, 'backdrop', [
            'type'      =>  'series',
            'id'        =>  $series->id,
            'category'  =>  'global'
        ]);
        $data['poster'] = $this->buildImagePath($series->poster, 'poster', [
            'type'      =>  'series',
            'id'        =>  $series->id,
            'category'  =>  'global'
        ]);
        $data['genres'] = $this->loadGenres($series->genres);
        $data['production_companies'] = $this->loadProductionCompanies($series->production_companies);
        $data['creators'] = $this->loadCreators($series->creators);
        $data['networks'] = $this->loadNetworks($series->networks);

        if (\count($only) > 0) {
            $data = Arr::only($data, $only);
        }


        return $data;
    }

    /**
     * Get single series by its ID
     * @param int $seriesID
     * @return Series|null
     */
    private function getSingleSeriesByID(int $seriesID) : ?Series {
        return $this->seriesCollection->filter(function (Series $series, int $index) use ($seriesID) {
            return $series->id === $seriesID;
        })->first();
    }

    /**
     * Return error that ID is not an integer
     * @param mixed $id
     * @return JsonResponse
     */
    private function errorSeriesIDisNotAnInteger($id) : JsonResponse {
        return $this->sendError('Invalid series ID provided', [
            'error'     =>  'Invalid series ID provided',
            'expected'  =>  'integer',
            'received'  =>  gettype($id)
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Return error that series could not be found by the ID
     * @param mixed $id
     * @return JsonResponse
     */
    private function errorSeriesNotFoundByID($id) : JsonResponse {
        return $this->sendError('Unable to find series with specified ID', [
            'error'     =>  'Series with the given ID could not be found',
            'id'        =>  $id
        ]);
    }

    /**
     * Return error that Season Number is not an integer
     * @param mixed $id
     * @return JsonResponse
     */
    private function errorSeasonNumberIsNotAnInteger($id) : JsonResponse {
        return $this->sendError('Invalid Season number provided', [
            'error'     =>  'Invalid Season number provided',
            'expected'  =>  'integer',
            'received'  =>  gettype($id)
        ], Response::HTTP_BAD_REQUEST);
    }

    /**
     * Return error that given series has no season matching provided number
     * @param Series $series
     * @param int $seasonNumber
     * @return JsonResponse
     */
    private function errorSeasonNotFound(Series $series, int $seasonNumber) : JsonResponse {
        return $this->sendError('Unable to find season for the given season number', [
            'series'            =>  [
                'id'            =>  $series->id,
                'title'         =>  $series->title,
            ],
            'error'             =>  sprintf('There is no season `%d` for the series `%s (%d)`', $seasonNumber, $series->title, $series->id)
        ]);
    }

}
