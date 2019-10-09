<?php namespace App\Http\Controllers\Api\OAuth;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

/**
 * Class GoogleController
 * @package App\Http\Controllers\Api\OAuth
 */
class GoogleController extends OAuthController {

    /**
     * @inheritDoc
     * @param Request $request
     * @return JsonResponse
     */
    public function redirectToProvider(Request $request): JsonResponse {
        dd($request->toArray());
    }

    /**
     * @inheritDoc
     * @param Request $request
     * @return JsonResponse
     */
    public function callback(Request $request): JsonResponse {
        dd($request->toArray());
    }

}
