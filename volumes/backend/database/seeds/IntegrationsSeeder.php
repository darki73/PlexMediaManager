<?php

use Illuminate\Database\Seeder;

/**
 * Class IntegrationsSeeder
 */
class IntegrationsSeeder extends Seeder {

    /**
     * Run seeder
     * @return void
     */
    public function run() : void {
        $integrations = [
            'telegram'              =>  [
                'bot_key'           =>  null,
                'chat_id'           =>  null
            ],
            'discord'               =>  [
                'client_id'         =>  null,
                'client_secret'     =>  null,
                'server_id'         =>  null,
                'channel_id'        =>  null,
                'bot_token'         =>  null,
                'access_token'      =>  null,
                'refresh_token'     =>  null,
                'webhook_url'       =>  null,
                'refresh_before'    =>  null
            ]
        ];

        foreach ($integrations as $integration => $configuration) {
            if (!\App\Models\Integration::where('integration', '=', $integration)->exists()) {
                \App\Models\Integration::create([
                    'integration'   =>  $integration,
                    'enabled'       =>  false,
                    'configuration' =>  $configuration
                ]);
            }
        }

    }

}
