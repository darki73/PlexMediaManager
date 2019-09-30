<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/**
 * Series Group
 */
Route::group([
    'prefix'    =>  'series',
    'namespace' =>  'Api'
], static function() {
    Route::get('', 'SeriesController@list');
    Route::get('/missing', 'SeriesController@getMissingEpisodes');
    Route::get('/{id}', 'SeriesController@getSingleSeries');
    Route::get('/{id}/seasons', 'SeriesController@getSeriesSeasons');
    Route::get('/{series}/season/{season}', 'SeriesController@getSeasonInformationForSeries');
//    Route::get('/{series}/season/{season}/episode/{episode}', 'SeriesController@getEpisodeInformationForSeries');
});

/**
 * Movies Group
 */
Route::group([
    'prefix'    =>  'movies',
    'namespace' =>  'Api'
], static function() {
   // Create the endpoints for movies
    Route::get('', 'MovieController@list');
});

/**
 * Torrent Group
 */
Route::group([
    'prefix'    =>  'torrent',
    'namespace' =>  'Api'
], static function() {

    /**
     * Get information about the torrent client
     */
    Route::get('', 'TorrentController@clientInformation');

    /**
     * Dispatch new job to synchronize downloaded items with the database
     */
    Route::get('sync', 'TorrentController@synchronizeItems');

    /**
     * Get list of completed torrents with respective commands to move the files
     */
    Route::get('completed', 'TorrentController@listCompletedTorrents');

});

/**
 * Dashboard Group
 */
Route::group([
    'prefix'    =>  'dashboard',
    'namespace' =>  'Api\Dashboard'
], static function() {
    // Add authentication requirement
    Route::get('server-information', 'MainController@serverInformation');

    /**
     * Dashboard >> Storage Group
     */
    Route::group([
        'prefix'    =>  'storage'
    ], static function() {
        /**
         * Dashboard >> Storage >> Disks Group
         */
        Route::group([
            'prefix'    =>  'disks'
        ], static function() {
            Route::get('list', 'StorageController@listDisks');
        });
    });

    /**
     * Dashboard >> Logs Group
     */
    Route::group([
        'prefix'    =>  'logs'
    ], static function() {
        Route::get('', 'LogsController@retrieveLogs');
    });

    /**
     * Dashboard >> Requests Group
     */
    Route::group([
        'prefix'    =>  'requests'
    ], static function() {
        Route::get('', 'RequestsController@listAllRequests');
        Route::post('update-status', 'RequestsController@updateRequestStatus');
        Route::post('delete-request', 'RequestsController@deleteRequest');
        Route::get('/movies', 'RequestsController@listMoviesRequests');
        Route::get('/series', 'RequestsController@listSeriesRequests');
    });

});
