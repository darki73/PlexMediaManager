<?php namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

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

        if ($user->email_verified_at === null) {
            return $this->sendError('errors.api.account.not_validated', [], Response::HTTP_CONFLICT);
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

    /**
     * Create new user account
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'username'      =>  'required|string|min:6|max:16',
            'email'         =>  'required|email',
            'password'      =>  'required|confirmed'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Request parameters are not what we have expected', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $userInformation = $request->only(['username', 'email', 'password']);
        $userInformation['password'] = Hash::make($userInformation['password']);
        // TODO: Check against Plex accounts to automatically validate account if it is already in the Plex database

        try {
            $avatar = \Avatar::create($userInformation['username'])->getImageObject()->encode('png', 100);
            $avatarDirectory = storage_path(implode(DIRECTORY_SEPARATOR, ['app', 'public', 'avatars', $userInformation['username']]));
            if (!File::exists($avatarDirectory)) {
                File::makeDirectory($avatarDirectory, 0755, true);
            }
            \Storage::put(implode(DIRECTORY_SEPARATOR, ['public', 'avatars', $userInformation['username'], 'avatar.png']), (string) $avatar);
        } catch (\Exception $exception) {
            return $this->sendError('Encountered error when tried to crate directory for user avatar. Message: ', [
                'code'      =>  $exception->getCode(),
                'message'   =>  $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        try {
            $user = User::create($userInformation);
            /**
             * @var Role $role
             */
            foreach(Role::whereName('user')->get() as $role) {
                $user->assignRole($role);
            }
            return $this->sendResponse('Successfully created new user');
        } catch (\Exception $exception) {
            return $this->sendError('We were unable to create new user', [
                'code'      =>  $exception->getCode(),
                'message'   =>  $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Check if the given username is not already taken
     * @param Request $request
     * @return JsonResponse
     */
    public function usernameAvailabilityCheck(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'username'  =>  'required|string'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Invalid or missing parameters', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $result = User::where('username', '=', $request->get('username'))->exists();
        return $this->sendResponse('Successfully performed email availability check', [
            'available'     =>  !$result
        ]);
    }

    /**
     * Check if the given email is not already taken
     * @param Request $request
     * @return JsonResponse
     */
    public function emailAvailabilityCheck(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'email' =>  'required|email'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Invalid or missing parameters', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $result = User::where('email', '=', $request->get('email'))->exists();
        return $this->sendResponse('Successfully performed email availability check', [
            'available'     =>  !$result
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
