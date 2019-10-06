<?php namespace App\Http\Controllers\Api\Dashboard;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\APIController;

/**
 * Class AccountController
 * @package App\Http\Controllers\Api\Dashboard
 */
class AccountController extends APIController {

    /**
     * Authenticate user with given credentials
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

        if (! $user->hasRole('administrator')) {
            return $this->sendError('errors.api.account.not_administrator', [], Response::HTTP_FORBIDDEN);
        }

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

}
