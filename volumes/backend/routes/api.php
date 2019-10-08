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
 * Get general API information
 */
Route::get('/', 'Api\APIController@getInformation');

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

Route::group([
    'prefix'    =>  'search',
    'namespace' =>  'Api'
], static function() {
    Route::post('remote', 'SearchController@remoteSearch');
});

Route::group([
    'prefix'    =>  'account',
    'namespace' =>  'Api'
], static function() {
    Route::post('authenticate', 'AccountController@authenticate');
    Route::get('user', 'AccountController@user')->middleware('auth:api');
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

Route::group([
    'prefix'    =>  'requests',
    'namespace' =>  'Api'
], static function() {
    Route::post('create', 'RequestsController@createRequest');
});


Route::group([
    'namespace'     =>  'Api\Dashboard',
    'prefix'        =>  'dashboard/account'
], static function() {
    Route::post('authenticate', 'AccountController@authenticate');
});

/**
 * Dashboard Group
 */
Route::group([
    'prefix'        =>  'dashboard',
    'namespace'     =>  'Api\Dashboard',
    'middleware'    =>  [
        'auth:api',
        'role:administrator'
    ],
], static function() {
    // Add authentication requirement
    Route::get('server-information', 'MainController@serverInformation');

    /**
     * Dashboard >> Accounts Group
     */
    Route::group([
        'prefix'    =>  'accounts'
    ], static function() {

        /**
         * Dashboard >> Account >> Users Group
         */
        Route::group([
            'prefix'    =>  'users'
        ], static function() {
            Route::get('list', 'UsersController@listUsers');
            Route::post('delete', 'UsersController@deleteUser');
        });

        /**
         * Dashboard >> Account >> Groups Group
         */
        Route::group([
            'prefix'    =>  'groups'
        ], static function() {
            Route::get('list', 'GroupsController@listGroups');
        });
    });

    Route::group([
        'prefix'        =>  'indexers'
    ], static function() {
        Route::get('list', 'IndexersController@list');
    });

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

    /**
     * Dashboard >> Torrents Group
     */
    Route::group([
        'prefix'        =>  'torrents'
    ], static function() {
        Route::get('list', 'TorrentsController@listActiveTorrents');
        Route::post('resume', 'TorrentsController@resumeTorrent');
        Route::post('pause', 'TorrentsController@pauseTorrent');
        Route::post('delete', 'TorrentsController@deleteTorrent');
        Route::post('create-category', 'TorrentsController@createCategory');
        Route::post('create-torrent', 'TorrentsController@createTorrent');
    });

    /**
     * Dashboard >> Settings Group
     */
    Route::group([
        'prefix'        =>  'settings'
    ], static function() {
        Route::get('', 'SettingsController@fetchSettings');
    });

});
