<?php namespace App\Http\Controllers\Api\Dashboard;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
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
     * @throws Exception
     */
    public function listGroups(Request $request) : JsonResponse {
        $groups = [];
        $roles = Role::all();

        foreach ($roles as $role) {
            $groups[$role->id] = [
                'id'            =>  $role->id,
                'name'          =>  $role->name,
                'guard_name'    =>  $role->guard_name,
                'permissions'   =>  []
            ];
            /**
             * @var Permission $permission
             */
            foreach ($role->getAllPermissions() as $permission) {
                $groups[$role->id]['permissions'][$permission->id] = [
                    'id'            =>  $permission->id,
                    'name'          =>  $permission->name,
                    'guard_name'    =>  $permission->guard_name
                ];
            }

            $groups[$role->id]['permissions'] = array_values($groups[$role->id]['permissions']);
        }

        return $this->sendResponse('Successfully fetched list of groups', $groups);
    }

}
