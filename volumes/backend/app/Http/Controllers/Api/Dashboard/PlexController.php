<?php namespace App\Http\Controllers\Api\Dashboard;

use App\Classes\Plex\Plex;
use App\Models\PlexUser;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Api\APIController;

/**
 * Class PlexController
 * @package App\Http\Controllers\Api\Dashboard
 */
class PlexController extends APIController {

    /**
     * Plex instance
     * @var Plex|null
     */
    protected ?Plex $plex = null;

    /**
     * PlexController constructor.
     */
    public function __construct() {
        $this->plex = new Plex;
    }

    /**
     * Get list of Plex users
     * @param Request $request
     * @return JsonResponse
     */
    public function listUsers(Request $request) : JsonResponse {
        return $this->sendResponse('Successfully fetched list of plex users', PlexUser::all()->toArray());
    }

    /**
     * Sync Plex users
     * @param Request $request
     * @return JsonResponse
     */
    public function syncUsers(Request $request) : JsonResponse {
        $users = $this->plex->internal()->allUsers(true);
        foreach ($users as $user) {
            $id = $user['id'];
            $model = PlexUser::find($id);
            if ($model === null) {
                PlexUser::create($user);
            } else {
                unset($user['id']);
                $model->update($user);
            }
        }
        return $this->sendResponse('Successfully synchronized list of Plex users with Database', []);
    }


}
