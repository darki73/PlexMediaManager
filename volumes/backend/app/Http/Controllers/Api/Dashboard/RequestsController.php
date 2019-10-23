<?php namespace App\Http\Controllers\Api\Dashboard;

use App\Classes\TheMovieDB\Endpoint\Search;
use App\Jobs\Download\SeriesImages;
use App\Jobs\Update\Episodes;
use App\Jobs\Update\SeriesIndexers;
use App\Models\Genre;
use App\Models\Movie;
use App\Models\Series;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use App\Classes\TheMovieDB\TheMovieDB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\APIController;
use Illuminate\Database\Eloquent\Collection;

/**
 * Class RequestsController
 * @package App\Http\Controllers\Api\Dashboard
 */
class RequestsController extends APIController {

    /**
     * @var \App\Models\Request|Collection|null
     */
    protected $requestsCollection = null;

    /**
     * RequestsController constructor.
     */
    public function __construct() {
        $this->requestsCollection = \App\Models\Request::orderBy('created_at', 'DESC')->get();
    }

    /**
     * List all requests
     * @param Request $request
     * @return JsonResponse
     */
    public function listAllRequests(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched list of all requests', $this->mapCollection($this->requestsCollection));
    }

    /**
     * List requests for movies
     * @param Request $request
     * @return JsonResponse
     */
    public function listMoviesRequests(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched requests for movies', $this->mapCollection($this->requestsCollection->reject(function (\App\Models\Request $request, int $index) {
            return $request->request_type === 0;
        })));
    }

    /**
     * List requests for series
     * @param Request $request
     * @return JsonResponse
     */
    public function listSeriesRequests(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched requests for series', $this->mapCollection($this->requestsCollection->reject(function (\App\Models\Request $request, int $index) {
            return $request->request_type === 1;
        })));
    }

    /**
     * Update request status
     * @param Request $request
     * @return JsonResponse
     */
    public function updateRequestStatus(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'id'        => 'required|integer',
            'status'    => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Invalid parameters have been passed', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $id = $request->get('id');
        $status = $request->get('status');

        $requestModel = \App\Models\Request::find($id);

        if ($requestModel === null) {
            return $this->sendError('Request with given id was not found', [
                'id'    =>  $id
            ]);
        }
        // request_type 0 - Series, 1 - Movie

        $requestType = $requestModel->request_type;
        $model = null;

        if ($requestType === 1) { // Movie
            $model = Movie::where('title', '=', $requestModel->title)->orWhere('original_title', '=', $requestModel->title)->where('release_date', 'LIKE', $requestModel->year . '-%')->first();
        } else if ($requestType === 0) { // Series
            $model = Series::where('title', '=', $requestModel->title)->orWhere('original_title', '=', $requestModel->title)->where('release_date', 'LIKE', $requestModel->year . '-%')->first();
        } else if ($requestType === 3) { // Music
            // Just do nothing, music is not yet supported
        }

        if ($model !== null) {
            if ($status === 1) {
                $model->update([
                    'local_title'       =>  sprintf('%s (%d)', str_replace(':', '', $requestModel->title), $requestModel->year)
                ]);
                if ($requestType === 0) { // Update indexers for series
                    dispatch(new SeriesIndexers);
                }
            }
        } else {
            try {
                $database = new TheMovieDB;
                $searchFor = null;
                switch ($requestType) {
                    case 0:
                        $searchFor = Search::SEARCH_SERIES;
                        break;
                    case 1:
                        $searchFor = Search::SEARCH_MOVIE;
                        break;
                    case 3:
                    default:
                        // Do nothing, music is not yet supported
                        break;
                }

                $search = $database->search()->for($searchFor, $requestModel->title)->year($requestModel->year);
                $result = $search->fetch();

                switch ($requestType) {
                    case 0:
                        $item = $database->series()->fetch($result['id'], sprintf('%s (%d)', $requestModel->title, $requestModel->year));
                        $parser = new \App\Classes\TheMovieDB\Processor\Series($item);
                        \App\Classes\Media\Processor\Processor::series($parser);
                        Episodes::withChain([
                            new SeriesIndexers
                        ])->dispatch();
                        break;
                    case 1:
                        $item = $database->movies()->fetch($result['id'], sprintf('%s (%d)', $requestModel->title, $requestModel->year));
                        $parser = new \App\Classes\TheMovieDB\Processor\Movie($item);
                        \App\Classes\Media\Processor\Processor::movie($parser);
                        break;
                    case 3:
                    default:
                        // Do nothing, music is not yet supported
                        break;
                }
            } catch (\Exception $exception) {
                return $this->sendError('Unable to load information from the Media API', [
                    'code'      =>  $exception->getCode(),
                    'message'   =>  $exception->getMessage()
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $requestModel->update([
            'status'    =>  $status
        ]);

        return $this->sendResponse('Successfully updated request status');
    }

    /**
     * Delete request
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteRequest(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'id'        => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Invalid parameters have been passed', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $id = $request->get('id');

        $requestModel = \App\Models\Request::find($id);

        if ($requestModel === null) {
            return $this->sendError('Request with given id was not found', [
                'id'    =>  $id
            ]);
        }

        $requestModel->forceDelete();

        return $this->sendResponse('Successfully deleted request with id: ' . $id);
    }

    /**
     * Retrieve media information
     * @param string $title
     * @param int $year
     * @param int $type
     * @return array
     */
    protected function retrieveMediaInformation(string $title, int $year, int $type) : array {
        $types = [
            0   =>  'tv',
            1   =>  'movie'
        ];

        $itemType = $types[$type];
        $key = $this->createCacheKey($title, $itemType);

        return Cache::remember($key, now()->addHours(12), function() use ($itemType, $title, $year) {
            $database = new TheMovieDB;
            $configuration = $database->configuration();
            $results = $database->search()
                ->for($itemType, $title)
                ->year($year)
                ->fetch();
            $results['poster'] = $configuration->getRemoteImagePath($results['poster_path'], 'poster');
            $results['backdrop'] = $configuration->getRemoteImagePath($results['backdrop_path'], 'backdrop');
            unset($results['poster_path'], $results['backdrop_path']);
            return $results;
        });
    }

    /**
     * Create cache key
     * @param string $title
     * @param string $type
     * @return string
     */
    protected function createCacheKey(string $title, string $type) : string {
        return sprintf('requests::%s:%s', $type, md5($title));
    }

    /**
     * Use common map method on collection
     * @param Collection $collection
     * @return array
     */
    protected function mapCollection(Collection $collection) : array {
        return $collection->map(function (\App\Models\Request $request, int $index) {
            $movieDatabase = $this->retrieveMediaInformation($request->title, $request->year, $request->request_type);
            $movieDatabase['genres'] = Genre::findMany($movieDatabase['genre_ids'], ['id', 'name']);
            return array_merge($request->toArray(), [
                'moviedb'   =>  $movieDatabase
            ]);
        })->toArray();
    }

}
