<?php

use App\Events\Requests\RequestCompleted;
use App\Models\Episode;
use App\Models\Request;
use App\Models\Series;
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

//Route::get('/redis', static function() {
//    $keys = \Illuminate\Support\Facades\Redis::connection()->keys('*');
//    dd($keys);
//});


Route::get('/test', static function() {

});
