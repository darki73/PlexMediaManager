<?php namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\Search\Search;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class SearchController
 * @package App\Http\Controllers\Api
 */
class SearchController extends APIController {


    /**
     * Perform search on the remote search providers
     * @param Request $request
     * @return JsonResponse
     */
    public function remoteSearch(Request $request) : JsonResponse {
        $validator = Validator::make($request->all(), [
            'query'     =>  'required|string',
            'type'      =>  'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing or invalid search query provided', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $query = $request->get('query');
        $type = $request->get('type', null);

        return $this->sendResponse('Successfully fetched list of results from search endpoints', (new Search)->{$type}($query));
    }

}
