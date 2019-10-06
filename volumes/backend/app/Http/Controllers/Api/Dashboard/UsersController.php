<?php namespace App\Http\Controllers\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\User as UserModel;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\APIController;

/**
 * Class UsersController
 * @package App\Http\Controllers\Api\Dashboard
 */
class UsersController extends APIController {

    /**
     * Get list of Users and associated permissions
     * @param Request $request
     * @return JsonResponse
     */
    public function listUsers(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched list of users', UserModel::all()->toArray());
    }

    /**
     * Delete user and all associated data
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteUser(Request $request) : JsonResponse {
        // TODO: Implement user deletion logic
        return $this->sendResponse('User has been successfully deleted');
    }

}
