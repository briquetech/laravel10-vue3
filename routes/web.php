<?php

use Illuminate\Support\Facades\Route;

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

// Company
Route::get('/company', [App\Http\Controllers\CompanyController::class, 'index'])->name('company-list');
Route::post('/company/save', [App\Http\Controllers\CompanyController::class, 'save'])->name('save-company');
Route::post('/selectcompany', [App\Http\Controllers\CompanyController::class, 'loadForSelection'])->name('selectcompany');
Route::get('/company/edit/{id}', [App\Http\Controllers\CompanyController::class, 'edit'])->name('edit-company-page');
Route::post('/company/duplicate', [App\Http\Controllers\CompanyController::class, 'duplicateRecord'])->name('duplicate-company-page');
Route::get('/company/view-file/{id}/{fieldName}/{size}/{randomId}', [App\Http\Controllers\CompanyController::class, 'viewFile'])->name('view-company-file');
Route::get('/company/export-to-pdf/{id}', [App\Http\Controllers\CompanyController::class, 'exportToPDF'])->name('print-company');
Route::post('/company/upload-file', [App\Http\Controllers\CompanyController::class, 'uploadFile'])->name('upload-company-file');
Route::post('/company/clear-file/{id}/{randomId}', [App\Http\Controllers\CompanyController::class, 'clearFile'])->name('clear-company-file');
Route::post('/company/delete', [App\Http\Controllers\CompanyController::class, 'deleteRecord'])->name('deletecompany');
