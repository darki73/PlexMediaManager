<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

/**
 * Class APIController
 * @package App\Http\Controllers\Api
 */
class APIController extends Controller {

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

    protected function createStorageLink(string $file) : string {

    }
}
