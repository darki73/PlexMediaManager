<?php namespace App\Http\Controllers\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\APIController;

/**
 * Class MainController
 * @package App\Http\Controllers\Api\Dashboard
 */
class MainController extends APIController {

    /**
     * Get server information
     * @param Request $request
     * @return JsonResponse
     */
    public function serverInformation(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched server information', \Server::information());
    }

}
