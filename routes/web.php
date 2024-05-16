<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// Users
Route::get('/users', [App\Http\Controllers\UsersController::class, 'index'])->name('users-list');
Route::post('/users/save', [App\Http\Controllers\UsersController::class, 'save'])->name('save-users');
Route::post('/selectusers', [App\Http\Controllers\UsersController::class, 'loadForSelection'])->name('selectusers');
