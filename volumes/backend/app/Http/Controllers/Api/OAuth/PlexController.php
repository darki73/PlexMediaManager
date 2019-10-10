<?php namespace App\Http\Controllers\Api\OAuth;

use App\Events\Account\Plex\AuthenticationContinue;
use App\Models\User;
use App\Classes\Plex\Plex;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Class PlexController
 * @package App\Http\Controllers\Api\OAuth
 */
class PlexController extends OAuthController {

    /**
     * @inheritDoc
     * @param Request $request
     * @return Response
     */
    public function redirectToProvider(Request $request): JsonResponse {
        return $this->sendResponse('Successfully fetched information for Plex Authentication Provider', (new Plex)->authenticate()->buildGoogleOAuthLink(Auth::user()->email));
    }

    /**
     * Finalize authorization, obtain token and return it to user
     * @param Request $request
     * @return JsonResponse
     */
    public function finalizeAuthorization(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'id'        =>  'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing required parameters or invalid parameters were specified', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $authenticationToken = (new Plex)->authenticate()->authorizeUser($request->get('id'));

        if ($authenticationToken) {
            return $this->sendResponse('Successfully retrieved authentication token', [
                'authentication_token'      =>  $authenticationToken
            ]);
        }

        return $this->sendError('Something went wrong', [
            'message'   =>  'We were unable to obtain authentication token. Please try again.'
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @inheritDoc
     * @param Request $request
     * @return mixed
     */
    public function callback(Request $request) {
        $validator = Validator::make($request->toArray(), [
            'email'     =>  'required|email'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing or Invalid parameters received', $validator->errors()->toArray());
        }

        $email = $request->get('email');

        $user = User::where('email', '=', $email)->first();
        if ($user === null) {
            return $this->sendError('Unable to retrieve a user');
        }
        event(new AuthenticationContinue($user));
        return view('close_window');
    }

}
