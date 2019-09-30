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

Route::get('/', function () {
    return response()->json(Server::information());
});

Route::get('/test', static function() {
    $series = \App\Models\Series::where('title', '=', '13 Reasons Why')->first();
    $episode = \App\Models\Episode::where('series_id', '=', $series->id)->where('season_number', '=', 3)->where('episode_number', '=', '10')->first();
    \App\Classes\Jackett\Indexers\LostFilm::download($series, $episode);
});
