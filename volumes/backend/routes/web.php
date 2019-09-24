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

});

Route::group([
    'prefix'    =>  'dashboard'
], static function() {
    Route::get('server-information', static function() {
        return response()->json([
            'success'       =>  true,
            'data'          =>  Server::information(),
            'message'       =>  'Successfully fetched server information',
            'requested_on'  =>  time()
        ], 200);
    });
    Route::group([
        'prefix'    =>  'storage'
    ], static function() {
        Route::group([
            'prefix'    =>  'disks'
        ], static function() {
            Route::get('list', static function() {
                return response()->json([
                    'success'       =>  true,
                    'data'          =>  (new App\Classes\Storage\PlexStorage)->countSeriesMovies()->drives(),
                    'message'       =>  'Successfully fetched server information',
                    'requested_on'  =>  time()
                ], 200);
            });
        });
    });

    Route::group([
        'prefix'    =>  'logs',
    ], static function() {
        Route::get('', static function() {
            $entries = [];

            /**
             * @var \App\Classes\LogReader\Entities\LogEntry $entry
             */
            foreach (LogReader::get() as $entry) {
                $context = $entry->context;
                $group = $entry->date->format('d-m-Y');
                $entries[$group][] = [
                    'id'            =>  $entry->id,
                    'environment'   =>  $entry->environment,
                    'level'         =>  $entry->level,
                    'file_path'     =>  $entry->file_path,
                    'date'          =>  $entry->date->toDateTimeString(),
                    'context'       =>  $context !== null  ? $context->toArray() : null,
                    'stack_traces'  =>  $entry->getStackTracesAsArray()
                ];
            }
            return response()->json([
                'success'       =>  true,
                'data'          =>  array_reverse($entries),
                'message'       =>  'Successfully fetched list of all logs',
                'requested_on'  =>  time()
            ], 200);
        });
    });

    Route::group([
        'prefix'    =>  'requests'
    ], static function() {
        Route::get('', static function() {
            return response()->json([
                'success'       =>  true,
                'data'          =>  \App\Models\Request::orderBy('created_at', 'DESC')->get()->toArray(),
                'message'       =>  'Successfully fetched requests',
                'requested_on'  =>  time()
            ], 200);
        });
    });

});


Route::group([
    'prefix'    =>  'torrent',
], static function() {
    Route::get('completed', static function() {
        $downloadManager = new \App\Classes\DownloadManager;
        $files = [];

        $downloadManager->series();
        foreach ($downloadManager->listDownloadedFiles() as $file) {
            $files[] = $file;
        }

        $downloadManager->movies();
        foreach ($downloadManager->listDownloadedFiles() as $file) {
            $files[] = $file;
        }

        foreach ($files as $file) {
            if (isset($file['fix_audio']) && $file['fix_audio']) {
                fix_lostfilm_audio_tracks($file['fix_path']);
            }
        }

        foreach ($files as $file) {
            echo sprintf('mv "%s" "%s"', $file['downloads_path'], $file['local_path']) . PHP_EOL;
        }
        die();
    });
    Route::get('sync', static function() {
        $manager = new \App\Classes\DownloadManager;
        $manager->series()->cleanEmptyDirectories();
        $manager->movies()->cleanEmptyDirectories();
        dispatch(new \App\Jobs\Sync\Episodes);
        die();
    });
});

Route::group([
    'prefix'    =>  'series',
    'namespace' =>  'Series'
], static function() {
//    Route::get('', 'SeriesController@list');
    Route::get('/missing', static function() {
        $missingEpisodes = \App\Models\Episode::where('downloaded', '=', false)->orderBy('series_id', 'ASC')->get();
        $currentDate = \Carbon\Carbon::now();

        foreach ($missingEpisodes as $episode) {
            $series = $episode->series;
            $aired = \Carbon\Carbon::createFromDate($episode->release_date)->addDays(3);
            if ($episode->season_number !== 0 && $aired->lessThan($currentDate)) {
                $missingStrings[] = sprintf('%s Season %d Episode %d (Aired %s)', $series->title, $episode->season_number, $episode->episode_number, $aired);
            }
        }

        echo '<pre>';
        echo '<h1>Missing ' . \count($missingStrings) . '</h1>' . PHP_EOL;
        foreach ($missingStrings as $string) {
            echo $string . PHP_EOL;
        }
    });
//    Route::get('/{id}', 'SeriesController@getSingle');
//    Route::get('/{id}/seasons', 'SeriesController@listSeasons');
//    Route::get('/{series}/season/{season}', 'SeriesController@listEpisodes');
});

Route::group([
    'prefix'    =>  'movies',
    'namespace' =>  'Movies'
], static function() {
//    Route::get('', 'MoviesController@list');
//    Route::get('/{id}', 'MoviesController@getSingle');
});
