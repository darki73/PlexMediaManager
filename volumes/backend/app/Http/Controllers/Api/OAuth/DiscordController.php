<?php namespace App\Http\Controllers\Api\OAuth;

use App\Models\Integration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Events\Account\Discord\AuthenticationContinue;
use App\Classes\Integrations\Discord\Client as DiscordClient;

/**
 * Class DiscordController
 * @package App\Http\Controllers\Api\OAuth
 */
class DiscordController extends OAuthController {

    /**
     * Redirect user to OAuth provider
     * @param Request $request
     * @return JsonResponse
     */
    public function redirectToProvider(Request $request): JsonResponse {
        return $this->sendResponse('Successfully fetched Discord Authorization Url', [
            'url'       =>  (new DiscordClient)->buildAuthorizationLink(Auth::user()->email)
        ]);
    }

    /**
     * Finalize authorization process
     * @param Request $request
     * @return JsonResponse
     */
    public function finalizeAuthorization(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'code'      =>  'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing or Invalid parameters received', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $code = $request->get('code');
        $response = (new DiscordClient)->authorizeUser($code);
        if ($response) {
            Integration::where('integration', '=', 'discord')->first()->update([
                'enabled'   =>  true
            ]);
            return $this->sendResponse('Successfully authenticated user!');
        } else {
            return $this->sendError('Failed to authenticate user', [], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Handle callback received from OAuth provider
     * @param Request $request
     * @return mixed
     */
    public function callback(Request $request) {
        if ($request->has('error')) {
            return view('oauth_error', [
                'message'       =>  $request->get('error_description')
            ]);
        }

        $validator = Validator::make($request->toArray(), [
            'code'      =>  'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing or Invalid parameters received', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $code = $request->get('code');
        $admins = User::role('administrator')->get();

        foreach ($admins as $user) {
            event(new AuthenticationContinue($user, $code));
        }
        return view('close_window');
    }

}
