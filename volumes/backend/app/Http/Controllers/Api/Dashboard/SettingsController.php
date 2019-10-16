<?php namespace App\Http\Controllers\Api\Dashboard;

use App\Models\Integration;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Classes\Storage\PlexStorage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Api\APIController;
use App\Classes\Integrations\AbstractClient as IntegrationClient;

/**
 * Class SettingsController
 * @package App\Http\Controllers\Api\Dashboard
 */
class SettingsController extends APIController {

    /**
     * Fetch all settings
     * @param Request $request
     * @return JsonResponse
     */
    public function fetchSettings(Request $request) : JsonResponse {
        $environment = $this->loadValuesFromEnvFile();

        return $this->sendResponse('Successfully fetched application settings', [
            'environment'   =>  $this->extractEnvironmentSettings($environment),
            'disks'         =>  $this->extractStorageSettings($environment),
            'proxy'         =>  $this->extractProxySettings($environment),
            'integrations'  =>  $this->extractIntegrationsSettings(Integration::all()->toArray()),
        ]);
    }

    /**
     * Update Integration Settings
     * @param Request $request
     * @param string $integration
     * @return JsonResponse
     */
    public function updateIntegrationSettings(Request $request, string $integration) : JsonResponse {
        $supportedIntegrations = config('integrations.list');
        if (! array_key_exists($integration, $supportedIntegrations)) {
            return $this->sendError('Trying to update settings for integration which is not supported!', [], Response::HTTP_BAD_REQUEST);
        }
        /**
         * @var IntegrationClient $integrationClass
         */
        $integrationClass = (new $supportedIntegrations[$integration]);

        $validationRules = [
            'enabled'           =>  'required|boolean'
        ];
        foreach ($integrationClass->validationRules() as $key => $value) {
            $validationRules['configuration.' . $key] = $value;
        }
        $validator = Validator::make($request->toArray(), $validationRules);

        if ($validator->fails()) {
            return $this->sendError('Invalid/Missing parameters detected, request cannot be completed', $validator->errors()->toArray(), Response::HTTP_BAD_REQUEST);
        }

        $newConfiguration = [];

        foreach ($request->get('configuration') as $key => $value) {
            if ($value !== null && strlen($value) > 0) {
                $newConfiguration[$key] = $value;
            }
        }

        $integrationModel = Integration::where('integration', '=', $integration)->first();
        if ($integrationModel === null) {
            return $this->sendError('Unable to find specified integration in the database. Probably you forgot to seed the database!', [], Response::HTTP_NOT_FOUND);
        }
        $databaseConfiguration = $integrationModel->configuration;

        foreach ($databaseConfiguration as $key => $value) {
            if (array_key_exists($key, $newConfiguration)) {
                if ($value !== $newConfiguration[$key]) {
                    $databaseConfiguration[$key] = $newConfiguration[$key];
                }
            }
        }

        try {
            $integrationModel->update([
                'enabled'           =>  $request->get('enabled'),
                'configuration'     =>  $databaseConfiguration
            ]);
            return $this->sendResponse('Successfully updated configuration for the integration');
        } catch (\Exception $exception) {
            return $this->sendError('Failed to update configuration for the integration', [
                'code'      =>  $exception->getCode(),
                'message'   =>  $exception->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Load values from the .env file
     * @return array
     */
    private function loadValuesFromEnvFile() : array {
        $file = array_values(array_filter(file(base_path('.env'), FILE_IGNORE_NEW_LINES)));
        $values = [];
        $sensitiveVariables = [
            'APP_KEY',
            'TMDB_API_KEY',
            'JACKETT_URL',
            'JACKETT_KEY',
            'QBIT_USERNAME',
            'QBIT_PASSWORD',
            'PROXY_HOST',
            'PROXY_PORT',
            'PROXY_USERNAME',
            'PROXY_PASSWORD',
        ];


        foreach ($file as $line) {
            $shouldEscape = false;
            [$key, $value] = explode('=', $line);
            if (false !== strpos($value, '"')) {
                $shouldEscape = true;
            }
            if (false === stripos($key, 'app_version')) {
                $values[$key] = [
                    'value'     =>  env($key, $value),
                    'escape'    =>  $shouldEscape,
                    'sensitive' =>  in_array($key, $sensitiveVariables)
                ];
            }
        }
        return $values;
    }

    /**
     * Extract genereal environment settings
     * @param array $environment
     * @return array
     */
    private function extractEnvironmentSettings(array $environment) : array {
        $returnArray = [
            'parameters'    =>  []
        ];
        $this->extractParameters($returnArray, $environment, 'app');
        return $returnArray;
    }

    /**
     * Extract storage settings
     * @param array $environment
     * @return array
     */
    private function extractStorageSettings(array $environment) : array {
        $returnArray = [
            'drives'        =>  (new PlexStorage)->drives(),
            'parameters'    =>  []
        ];

        $this->extractParameters($returnArray, $environment, 'storage');
        return $returnArray;
    }

    /**
     * Extract proxy settings
     * @param array $environment
     * @return array
     */
    private function extractProxySettings(array $environment) : array {
        $returnArray = [
            'allowed_types'     =>  [
                'http'          =>  'HTTP',
                'http1'         =>  'HTTP 1.0',
                'https'         =>  'HTTPS',
                'socks4'        =>  'SOCKS4',
                'socks4a'       =>  'SOCKS4a',
                'socks5'        =>  'SOCKS5',
                'socks5host'    =>  'SOCKS5 Hostname'
            ],
            'parameters'        =>  []
        ];

        $this->extractParameters($returnArray, $environment, 'proxy');

        return $returnArray;
    }

    /**
     * Extract integrations settings
     * @param array $integrations
     * @return array
     */
    private function extractIntegrationsSettings(array $integrations) : array {
        $hide = [
            'refresh_before'
        ];
        foreach ($integrations as $index => $integration) {
            foreach ($integration['configuration'] as $key => $value) {
                if (in_array($key, $hide)) {
                    unset($integrations[$index]['configuration'][$key]);
                }
            }
            $integrations[$index]['oauth'] = in_array($integration['integration'], config('integrations.oauth_required'));
            $integrations[$index]['validation'] = config('integrations.validation_rules.' . $integration['integration'], []);
            if (isset($integrations[$index]['created_at'], $integrations[$index]['updated_at'])) {
                unset($integrations[$index]['created_at'], $integrations[$index]['updated_at']);
            }
        }
        return $integrations;
    }

    /**
     * Extract parameters from the environment array and append them by reference to the array
     * @param array $returnArray
     * @param array $environment
     * @param string $extractFor
     * @return void
     */
    private function extractParameters(array & $returnArray, array $environment, string $extractFor) {
        foreach ($environment as $key => $value) {
            if (Str::startsWith(strtolower($key), $extractFor . '_')) {
                $returnArray['parameters'][trim(str_replace($extractFor . '_', '', strtolower($key)))] = [
                    'key'   =>  $key,
                    'value' =>  $value
                ];
            }
        }
    }

    /**
     * Extract boolean or string
     * @param string $value
     * @return bool|string
     */
    private function extractBooleanOrString(?string $value) {
        if ($value === null) {
            return '';
        }
        if ($value === 'false') {
            return false;
        } else if ($value === 'true') {
            return true;
        } else {
            return $value;
        }
    }

}
