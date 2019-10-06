<?php namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;

/**
 * Class AccountController
 * @package App\Http\Controllers\Api
 */
class AccountController extends APIController {

    public function user(Request $request) : JsonResponse {
        $user = $request->user();
        return $this->sendResponse('Successfully fetched authenticated user information', $user->toArray());
    }

}
