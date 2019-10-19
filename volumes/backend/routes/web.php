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

Route::get('/test', static function() {


//    $rawFile = file(storage_path(implode(DIRECTORY_SEPARATOR, ['temp', 'tv_series_ids_10_18_2019.json'])), FILE_IGNORE_NEW_LINES);
//    $seriesList = [];
//    foreach ($rawFile as $row) {
//        $array = json_decode($row, true);
//        if ($array['popularity'] > env('DUMPER_POPULARITY_THRESHOLD')) {
//            $seriesList[$array['id']] = $array;
//        }
//    }
//
//    dd(\count($seriesList));
});
