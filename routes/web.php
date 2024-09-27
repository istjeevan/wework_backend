<?php

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

use App\Events\Propertyevent;
use App\Models\Amenities;
use Illuminate\Support\Facades\Route;
use Google\Service\Drive;
use Google_Client;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});