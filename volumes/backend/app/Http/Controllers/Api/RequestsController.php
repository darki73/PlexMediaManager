<?php namespace App\Http\Controllers\Api;

use App\Events\Requests\RequestCreated;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * Class RequestsController
 * @package App\Http\Controllers\Api
 */
class RequestsController extends APIController {

    /**
     * Request new item to be added to library
     * @param Request $request
     * @return JsonResponse
     */
    public function createRequest(Request $request) : JsonResponse {
        $user = $request->user();

        $validator = Validator::make($request->toArray(), [
            'title'     =>  'required|string',
            'released'  =>  'required|string',
            'type'      =>  'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing required parameters', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        try {
            $createdRequest = \App\Models\Request::create([
                'user_id'       =>  $user->id,
                'request_type'  =>  $this->getMediaType($request->get('type')),
                'title'         =>  $request->get('title'),
                'year'          =>  $this->extractYear($request->get('released'))
            ]);
            event(new RequestCreated($createdRequest));
        } catch (\Exception $exception) {
            return $this->sendError('Unable to request item', [
                'code'      =>  $exception->getCode(),
                'message'   =>  $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->sendResponse('Successfully created new request');

    }

    /**
     * Extract year from the release date
     * @param string $released
     * @return int
     */
    protected function extractYear(string $released) : int {
        $parts = explode('-', $released);
        $year = null;
        foreach ($parts as $part) {
            if (strlen($part) === 4) {
                $year = (integer) $part;
                break;
            }
        }
        return $year;
    }

    /**
     * Get media type
     * @param string $type
     * @return int
     */
    protected function getMediaType(string $type) : int {
        $intType = null;
        switch ($type) {
            case 'tv':
                $intType = 0;
                break;
            case 'movie':
                $intType = 1;
                break;
            case 'music':
                $intType = 2;
                break;
        }
        return $intType;
    }

}
