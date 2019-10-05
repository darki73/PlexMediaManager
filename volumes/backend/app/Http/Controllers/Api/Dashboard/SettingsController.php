<?php namespace App\Http\Controllers\Api\Dashboard;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Classes\Storage\PlexStorage;
use App\Http\Controllers\Api\APIController;

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
            'proxy'         =>  $this->extractProxySettings($environment)
        ]);
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
                    'value'     =>  $this->extractBooleanOrString($value),
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
    private function extractBooleanOrString(string $value) {
        if ($value === 'false') {
            return false;
        } else if ($value === 'true') {
            return true;
        } else {
            return $value;
        }
    }

}
