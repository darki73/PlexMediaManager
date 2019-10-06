<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

/**
 * Class RolesAndPermissionsSeeder
 */
class RolesAndPermissionsSeeder extends Seeder {

    /**
     * Run seeder
     * @return void
     */
    public function run() : void {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            0       =>  'dashboard access',

            // Dashboard >> Accounts >> Groups permissions
            1       =>  'dashboard accounts view',
            2       =>  'dashboard accounts groups view',
            3       =>  'dashboard accounts groups edit',
            4       =>  'dashboard accounts groups delete',

            // Dashboard >> Accounts >> Users permissions
            5       =>  'dashboard accounts users view',
            6       =>  'dashboard accounts users edit',
            7       =>  'dashboard accounts users delete',

            // Dashboard >> Requests permissions
            8       =>  'dashboard requests view',
            9       =>  'dashboard requests update',
            10      =>  'dashboard requests delete',

            // Dashboard >> Storage >> Disks permissions
            11      =>  'dashboard storage view',
            12      =>  'dashboard storage disks view',
//            13      =>  'dashboard storage disks SOMETHING, NOT DECIDED YET',
//            14      =>  'dashboard storage disks SOMETHING, NOT DECIDED YET',
//            15      =>  'dashboard storage disks SOMETHING, NOT DECIDED YET',

            // Dashboard >> Storage >> Mounts permissions
            16      =>  'dashboard storage mounts view',
//            17      =>  'dashboard storage mounts SOMETHING, NOT DECIDED YET',
//            18      =>  'dashboard storage mounts SOMETHING, NOT DECIDED YET',
//            19      =>  'dashboard storage mounts SOMETHING, NOT DECIDED YET',

            // Dashboard >> Logs permissions
            20      =>  'dashboard logs view',

            // Dashboard >> Torrents permissions
            21      =>  'dashboard torrents view',
            22      =>  'dashboard torrents list view',
            23      =>  'dashboard torrents categories view',
            24      =>  'dashboard torrents create view',

            // Dashboard >> Settings permissions
            25      =>  'dashboard settings view',
            26      =>  'dashboard settings update',
            27      =>  'dashboard settings view sensitive'
        ];

        $roles = [
            'administrator'     =>  array_keys($permissions),
//            'user'              =>  array_keys(array_keys_between($permissions, 1000, 2000, true))
            'user'              =>  []
        ];


        foreach ($permissions as $index => $permission) {
            if (! Permission::where('name', '=', $permissions)->exists()) {
                $permissions[$index] = Permission::create(['name' => $permission, 'guard_name' => 'web']);
            } else {
                $permissions[$index] = Permission::where('name', '=', $permission)->first();
            }
        }

        foreach ($roles as $role => $rolePermissions) {
            /**
             * @var Role|null $roleModel
             */
            $roleModel = null;

            if (! Role::where('name', '=', $role)->where('guard_name', '=', 'web')->exists()) {
                $roleModel = Role::create(['name' => $role]);
            }

            if ($roleModel === null) {
                $roleModel = Role::where('name', '=', $role)->first();
            }

            foreach ($rolePermissions as $permissionID) {
                $permission = $permissions[$permissionID];
                $relationExists = DB::table('role_has_permissions')
                    ->where('role_id', '=', $roleModel->id)
                    ->where('permission_id', '=', $permission->id)
                    ->exists();
                if (! $relationExists) {
                    $roleModel->givePermissionTo($permission);
                }
            }

        }

    }

}
