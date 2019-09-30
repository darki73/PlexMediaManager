<?php namespace App\Http\Controllers\Api\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Classes\Torrent\Torrent;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\APIController;
use Illuminate\Support\Facades\Validator;

/**
 * Class TorrentsController
 * @package App\Http\Controllers\Api\Dashboard
 */
class TorrentsController extends APIController {

    /**
     * Torrent Client instance
     * @var Torrent|null
     */
    protected $torrentClient = null;

    /**
     * TorrentsController constructor.
     */
    public function __construct() {
        $this->torrentClient = new Torrent;
    }

    /**
     * Get list of all active torrents
     * @param Request $request
     * @return JsonResponse
     */
    public function listActiveTorrents(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched list of active torrents', $this->torrentClient->listTorrentsForDashboard());
    }

    /**
     * Resume torrent
     * @param Request $request
     * @return JsonResponse
     */
    public function resumeTorrent(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'hash'  =>  'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing or invalid parameters received', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $this->torrentClient->resumeTorrent($request->get('hash'));

        return $this->sendResponse('Successfully resumed torrent');
    }

    /**
     * Pause torrent
     * @param Request $request
     * @return JsonResponse
     */
    public function pauseTorrent(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'hash'  =>  'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing or invalid parameters received', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $this->torrentClient->pauseTorrent($request->get('hash'));

        return $this->sendResponse('Successfully paused torrent');
    }

    /**
     * Delete torrent
     * @param Request $request
     * @return JsonResponse
     */
    public function deleteTorrent(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'hash'  =>  'required|string',
            'force' =>  'required|bool'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing or invalid parameters received', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $this->torrentClient->deleteTorrent($request->get('hash'), $request->get('force'));

        return $this->sendResponse('Successfully deleted torrent');
    }

    /**
     * Create category
     * @param Request $request
     * @return JsonResponse
     */
    public function createCategory(Request $request) : JsonResponse {
        $validator = Validator::make($request->toArray(), [
            'category'      =>  'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing or invalid parameters received', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $this->torrentClient->createCategory($request->get('category'));

        return $this->sendResponse('Successfully created a category');
    }

}
