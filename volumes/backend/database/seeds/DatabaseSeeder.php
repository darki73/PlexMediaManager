<?php

use Illuminate\Database\Seeder;

/**
 * Class DatabaseSeeder
 */
class DatabaseSeeder extends Seeder {

    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run() : void {
        $this->call(RolesAndPermissionsSeeder::class);
        $this->call(IntegrationsSeeder::class);
    }

}
