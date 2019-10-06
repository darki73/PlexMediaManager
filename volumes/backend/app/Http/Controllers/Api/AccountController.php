<?php namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Class AccountController
 * @package App\Http\Controllers\Api
 */
class AccountController extends APIController {

    /**
     * Authenticate user
     * @param Request $request
     * @return JsonResponse
     */
    public function authenticate(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'email'         =>  'required|string|email',
            'password'      =>  'required|string|min:8',
            'remember_me'   =>  'boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendError('errors.api.invalid_request', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $credentials = request(['email', 'password']);

        if (! Auth::attempt($credentials)) {
            return $this->sendError('errors.api.account.invalid_credentials', [], Response::HTTP_CONFLICT);
        }

        $user = $request->user();

        $createdToken = $user->createToken(sprintf('Access token for %s', $user->username));
        $token = $createdToken->token;

        if ($request->get('remember_me')) {
            $token->expires_at = now()->addYear();
            $token->save();
        }

        return $this->sendResponse('account.status.authenticated', [
            'access_token'      =>  $createdToken->accessToken,
            'token_type'        =>  'Bearer',
            'expires_at'        =>  Carbon::parse($createdToken->token->expires_at)->format('D, d M Y H:i:s') . ' GMT'
        ]);
    }

    /**
     * Get authenticated user information
     * @param Request $request
     * @return JsonResponse
     */
    public function user(Request $request) : JsonResponse {
        $user = $request->user();
        return $this->sendResponse('Successfully fetched authenticated user information', $user->toArray());
    }

}
