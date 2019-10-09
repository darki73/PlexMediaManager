<?php namespace App\Http\Controllers\Api\OAuth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\APIController;

/**
 * Class OAuthController
 * @package App\Http\Controllers\Api\OAuth
 */
abstract class OAuthController extends APIController {

    /**
     * Redirect user to OAuth provider
     * @param Request $request
     * @return JsonResponse
     */
    abstract public function redirectToProvider(Request $request) : JsonResponse;

    /**
     * Handle callback received from OAuth provider
     * @param Request $request
     * @return mixed
     */
    abstract public function callback(Request $request);

}
