<?php

use Google\Client;
use Google\Service\Calendar;
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

Route::get('/', [\App\Http\Controllers\HomepageController::class, 'index'])->name('home.index');
Route::get('/ajax', [\App\Http\Controllers\HomepageController::class, 'ajax'])->name('home.ajax');
