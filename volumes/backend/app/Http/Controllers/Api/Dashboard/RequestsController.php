<?php namespace App\Http\Controllers\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
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
        return $this->sendResponse('Successfully fetched list of all requests', $this->requestsCollection->toArray());
    }

    /**
     * List requests for movies
     * @param Request $request
     * @return JsonResponse
     */
    public function listMoviesRequests(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched requests for movies', $this->requestsCollection->reject(function (\App\Models\Request $request, int $index) {
            return $request->request_type === 0;
        })->toArray());
    }

    /**
     * List requests for series
     * @param Request $request
     * @return JsonResponse
     */
    public function listSeriesRequests(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched requests for series', $this->requestsCollection->reject(function (\App\Models\Request $request, int $index) {
            return $request->request_type === 1;
        })->toArray());
    }


}
