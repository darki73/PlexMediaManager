<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

//function get_string_between($string, $start, $end){
//    $string = ' ' . $string;
//    $ini = strpos($string, $start);
//    if ($ini == 0) return '';
//    $ini += strlen($start);
//    $len = strpos($string, $end, $ini) - $ini;
//    return substr($string, $ini, $len);
//}

Route::get('/test', static function() {
//    $baseAPI = 'https://api.github.com';
//    $filesPath = 'repos/darki73/tmdb-client/contents/src/endpoints';
//    $rawUri = 'https://raw.githubusercontent.com/darki73/tmdb-client/master/src/endpoints';
//    $excludeFiles = [
//        'baseEndpoint.ts',
//        'index.ts'
//    ];
//
//    $rawLinks = [];
//
//    $client = new \GuzzleHttp\Client();
//    $response = $client->get(sprintf('%s/%s', $baseAPI, $filesPath))->getBody()->getContents();
//    $rawFilesList = array_filter(json_decode($response, true), function(array $item) use ($excludeFiles) {
//        return !in_array($item['name'], $excludeFiles);
//    });
//
//    $documentationBlocks = [];
//    $docBlockStarted = false;
//
//    foreach($rawFilesList as $item) {
//        $fileContents = explode(PHP_EOL, file_get_contents(sprintf('%s/%s', $rawUri, $item['name'])));
//        $methods = [];
//
//        $docBlockIndex = 0;
//        foreach ($fileContents as $index => $row) {
//            if (false !== strpos($row, '/**')) {
//                $docBlockStarted = true;
//                $documentationBlocks[str_replace('.ts', '', $item['name'])][$docBlockIndex]['starts'] = $index;
//            }
//
//            if ($docBlockStarted) {
//                if (false !== strpos($row, '*/')) {
//                    $docBlockStarted = false;
//                    $documentationBlocks[str_replace('.ts', '', $item['name'])][$docBlockIndex]['ends'] = $index;
//                    $declarationRow = $fileContents[$index + 1];
//                    if (false !== stripos($declarationRow, 'public async')) {
//                        $methodName = trim(str_replace('public async', '', \Illuminate\Support\Str::before($declarationRow, '(')));
//                        $reference = $documentationBlocks[str_replace('.ts', '', $item['name'])][$docBlockIndex];
//                        $see = null;
//                        $description = trim(str_replace('*', '', $reference['data'][0]));
//
//                        foreach ($reference['data'] as $value) {
//                            if (false !== strpos($value, '@see')) {
//                                $see = trim(str_replace(['@see', '*'], '', $value));
//                            }
//                        }
//
//                        $methods[$methodName] = [
//                            'description'   =>  $description,
//                            'see'           =>  $see,
//                            'usage'         =>  'const data = tmdb.' . str_replace('.ts', '', $item['name']) . '.' . $methodName . '(' . get_string_between($declarationRow, '(', ')') . ');',
//                            'returns'       =>  trim(get_string_between($declarationRow, '):', '{')),
//                        ];
//                    }
//                    $docBlockIndex++;
//                } else {
//                    if (false === strpos($row, '/**')) {
//                        $documentationBlocks[str_replace('.ts', '', $item['name'])][$docBlockIndex]['data'][] = $row;
//                    }
//                }
//            }
//        }
//
//        $rawLinks[str_replace('.ts', '', $item['name'])] = $methods;
//    }
//
//    foreach ($rawLinks as $fileName => $methods) {
//        $defaultMarkDown = '# ' . ucfirst($fileName) . ' Endpoint' . PHP_EOL .
//            '## Initialize Client' . PHP_EOL .
//            '```ts' . PHP_EOL .
//            'import { TMDBClient } from \'moviedatabase-client\';' . PHP_EOL .
//            'const tmdb = new TMDBClient(\'API_KEY\');' . PHP_EOL .
//            '```' . PHP_EOL;
//
//        echo $defaultMarkDown . PHP_EOL . PHP_EOL;
//
//        foreach ($methods as $method) {
//            echo '#### ' . $method['description'] . PHP_EOL;
//            if ($method['see'] !== null) {
//                echo '[View original method documentation](' . $method['see'] . ')' . PHP_EOL;
//            }
//            echo '```ts' . PHP_EOL;
//            echo $method['usage'] . PHP_EOL;
//            echo '// This method call will return instance of ' . $method['returns'] . PHP_EOL;
//            echo '```' . PHP_EOL;
//        }
//
//        echo PHP_EOL . PHP_EOL . PHP_EOL;
//    }

//    dd($rawLinks);
});
