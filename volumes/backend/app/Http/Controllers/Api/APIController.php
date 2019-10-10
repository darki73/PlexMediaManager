<?php namespace App\Http\Controllers\Api;

use Exception;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\Github\Github;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;

/**
 * Class APIController
 * @package App\Http\Controllers\Api
 */
class APIController extends Controller {

    /**
     * Get API Information
     * @param Request $request
     * @return JsonResponse
     */
    public function getInformation(Request $request) : JsonResponse {
        try {
            $information = [
                'application'       =>  (new Github)->toArray(),
                'server_time'       =>  [
                    'exact'         =>  time(),
                    'nice'          =>  Carbon::now()->toDateTimeString(),
                    'timezone'      =>  config('app.timezone')
                ]
            ];
            return $this->sendResponse('Successfully fetched API information', $information);
        } catch (Exception $exception) {
            return $this->sendError('Unable to fetch API information', [
                'code'      =>  $exception->getCode(),
                'message'   =>  $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Return response with already processed data
     * @param array $result
     * @param int $code
     * @return JsonResponse
     */
    public function sendRaw(array $result, int $code = Response::HTTP_OK) : JsonResponse {
        return response()->json(array_merge($result, [
            'requested_on'  =>  time()
        ]), $code);
    }

    /**
     * Return successful response
     * @param string $message
     * @param array $result
     * @param int $code
     * @return JsonResponse
     */
    public function sendResponse(string $message, array $result = [], int $code = Response::HTTP_OK) : JsonResponse {
        return response()->json([
            'success'       =>  true,
            'data'          =>  $result,
            'message'       =>  $message,
            'requested_on'  =>  time()
        ], $code);
    }

    /**
     * Return response with errors
     * @param string $error
     * @param array  $errors
     * @param int    $code
     * @return JsonResponse
     */
    public function sendError(string $error, array $errors = [], int $code = Response::HTTP_NOT_FOUND): JsonResponse {
        $response = [
            'success' => false,
            'message' => $error,
        ];
        if (!empty($errors)) {
            $response['data'] = $errors;
        }
        $response['requested_on'] = time();
        return response()->json($response, $code);
    }

    /**
     * Check if required header is present on request
     * @param Request $request
     * @param string $header
     * @return bool
     */
    public function checkIfHeaderIsPresent(Request $request, string $header) : bool {
        return $request->headers->has(strtolower($header));
    }

    /**
     * Get header value from the request
     * @param Request $request
     * @param string $header
     * @return string|null
     */
    public function getHeaderValueFromRequest(Request $request, string $header) : ?string {
        if (! $this->checkIfHeaderIsPresent($request, $header)) {
            return null;
        }
        return $request->headers->get(strtolower($header));
    }

    /**
     * Get Plex token from headers
     * @param Request $request
     * @return string|null
     */
    protected function getPlexToken(Request $request) : ?string {
        return $this->getHeaderValueFromRequest($request, 'x-plex-token');
    }

}
