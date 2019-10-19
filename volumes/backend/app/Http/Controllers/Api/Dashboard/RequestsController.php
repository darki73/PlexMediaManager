<?php namespace App\Http\Controllers\Api\Dashboard;

use App\Classes\TheMovieDB\Endpoint\Search;
use App\Jobs\Download\SeriesImages;
use App\Jobs\Update\Episodes;
use App\Jobs\Update\SeriesIndexers;
use App\Models\Genre;
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

        try {
            $database = new TheMovieDB;
            $search = $database->search()->for(Search::SEARCH_SERIES, $requestModel->title)->year($requestModel->year);
            $results = $search->fetch();
            $item = $database->series()->fetch($results['id'], sprintf('%s (%d)', $requestModel->title, $requestModel->year));
            $parser = new \App\Classes\TheMovieDB\Processor\Series($item);
            \App\Classes\Media\Processor\Processor::series($parser);
        } catch (\Exception $exception) {
            return $this->sendError('Unable to load information from the Media API', [
                'code'      =>  $exception->getCode(),
                'message'   =>  $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        Episodes::withChain([
            new SeriesIndexers,
            new SeriesImages
        ])->dispatch();

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
            unset($results['poster_path']);
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
