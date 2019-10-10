<?php namespace App\Http\Controllers\Api;

use App\Classes\Plex\Plex;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

/**
 * Class PlexController
 * @package App\Http\Controllers\Api
 */
class PlexController extends APIController {

    /**
     * Plex Instance
     * @var Plex|null
     */
    protected $plex = null;

    /**
     * PlexController constructor.
     */
    public function __construct() {
        $this->plex = new Plex;
    }

    /**
     * Get list of all available servers
     * @param Request $request
     * @return JsonResponse
     */
    public function listServers(Request $request) : JsonResponse {
        $plexToken = $this->getPlexToken($request);
        if (! $plexToken) {
            return $this->sendError('X-Plex-Token header is either not set or the provided value is incorrect', [], Response::HTTP_BAD_REQUEST);
        }
        $servers = $this->plex->servers()->list($plexToken);
        return $this->sendRaw(Arr::except($servers, ['status']), $servers['status']);
    }

    /**
     * Force servers refresh (as we do cache them)
     * @param Request $request
     * @return JsonResponse
     */
    public function listServersRefresh(Request $request) : JsonResponse {
        $plexToken = $this->getPlexToken($request);
        if (! $plexToken) {
            return $this->sendError('X-Plex-Token header is either not set or the provided value is incorrect', [], Response::HTTP_BAD_REQUEST);
        }
        $servers = $this->plex->servers()->list($plexToken, true);
        return $this->sendRaw(Arr::except($servers, ['status']), $servers['status']);
    }

    /**
     * Get list of available Sections (Libraries)
     * @param Request $request
     * @return JsonResponse
     */
    public function listSections(Request $request) : JsonResponse {
        $plexToken = $this->getPlexToken($request);
        if (! $plexToken) {
            return $this->sendError('X-Plex-Token header is either not set or the provided value is incorrect', [], Response::HTTP_BAD_REQUEST);
        }
        $validator = Validator::make($request->toArray(), [
            'server'    =>  'required|string'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing required parameters', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }


        $libraries = $this->plex->library()->listCategories($request->get('server'), $plexToken);
        return $this->sendRaw(Arr::except($libraries, ['status']), $libraries['status']);
    }

}
