<?php namespace App\Http\Controllers\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Classes\Storage\PlexStorage;
use App\Http\Controllers\Api\APIController;

/**
 * Class StorageController
 * @package App\Http\Controllers\Api\Dashboard
 */
class StorageController extends APIController {

    /**
     * Get information about mounted disks
     * @param Request $request
     * @return JsonResponse
     */
    public function listDisks(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched information about mounted disks', (new PlexStorage)->countSeriesMovies()->drives());
    }

}
