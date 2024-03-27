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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/creator', [\BriqueAdminCreator\AdminCreatorController::class, 'index'])->name('creator');
Route::post('/creator/brique/save-config', [\BriqueAdminCreator\AdminCreatorController::class, 'saveConfig'])->name('save-config');
Route::get('/creator/download/{object}/{code}', [\BriqueAdminCreator\AdminCreatorController::class, 'downloadSomething'])->name('download-something');