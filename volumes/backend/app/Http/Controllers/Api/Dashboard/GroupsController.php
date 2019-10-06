<?php namespace App\Http\Controllers\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\APIController;

/**
 * Class GroupsController
 * @package App\Http\Controllers\Api\Dashboard
 */
class GroupsController extends APIController {

    /**
     * Get list of groups and associated permissions
     * @param Request $request
     * @return JsonResponse
     */
    public function listGroups(Request $request) : JsonResponse {

    }

}
