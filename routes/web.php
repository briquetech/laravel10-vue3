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

Route::middleware(['auth'])->group(function () {

	Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

	// Role
	Route::get('/role', [App\Http\Controllers\RoleController::class, 'index'])->name('role-list');
	Route::post('/role/save', [App\Http\Controllers\RoleController::class, 'save'])->name('save-role');
	Route::post('/selectrole', [App\Http\Controllers\RoleController::class, 'loadForSelection'])->name('selectrole');
	Route::post('/role/get-permitted-objects', [App\Http\Controllers\RoleController::class, 'getPermittedObjects'])->name('role-objects');

	// User
	Route::get('/user', [App\Http\Controllers\UserController::class, 'index'])->name('user-list');
	Route::post('/user/save', [App\Http\Controllers\UserController::class, 'save'])->name('save-user');
	Route::post('/selectuser', [App\Http\Controllers\UserController::class, 'loadForSelection'])->name('selectuser');

});
