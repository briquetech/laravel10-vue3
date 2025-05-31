<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Role
Route::post('/role/get', [App\Http\Controllers\RoleController::class, 'get'])->name('get-role-list');
// User
Route::post('/user/get', [App\Http\Controllers\UserController::class, 'get'])->name('get-user-list');

// Company	
Route::post('/company/get', [App\Http\Controllers\CompanyController::class, 'get'])->name('get-company-list');
Route::post('/company/get-record/{id}', [App\Http\Controllers\CompanyController::class, 'getRecord'])->name('get-company-record');