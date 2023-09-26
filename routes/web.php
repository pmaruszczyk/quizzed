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


Route::get('/',        [\App\Http\Controllers\SetNick::class, 'index']);
Route::post('/setNick', [\App\Http\Controllers\SetNick::class, 'set']);
Route::get('/user/{nick}', [\App\Http\Controllers\User::class, 'index']);
Route::get('/admin',        [\App\Http\Controllers\Admin::class, 'adminIndex']);
Route::get('/goToNextStep', [\App\Http\Controllers\Admin::class, 'goToNextStep']);
Route::get('/showAnswer',   [\App\Http\Controllers\Admin::class, 'showAnswer']);
Route::post('/resetGame', [\App\Http\Controllers\Admin::class, 'resetGame']);
Route::get('/makeMeAdmin123', [\App\Http\Controllers\Admin::class, 'makeMeAdmin']);
Route::get('/users', [\App\Http\Controllers\Admin::class, 'users']);
Route::get('/questions567', [\App\Http\Controllers\Question::class, 'list']);
Route::get('/question', [\App\Http\Controllers\Question::class, 'getCurrent']);
Route::post('/saveAnswer', [\App\Http\Controllers\Question::class, 'saveAnswer']);
