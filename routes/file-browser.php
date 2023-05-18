<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FolderController;

/*
|--------------------------------------------------------------------------
| File-Browser Routes
|--------------------------------------------------------------------------
|
| Here is where you can register file-browser application routes.
| These routes are loaded by the FileBrowserServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group(['as' => 'folder.', 'prefix' => '/folder'], function () {
    // Get folder list
    Route::get('/list', [FolderController::class, 'list'])->name('list');
    // Get folder properties
    Route::get('/properties', [FolderController::class, 'properties'])->name('properties');
    // Search file or folder in filesystem
    Route::get('/search/{str}', [FolderController::class, 'search'])->name('search');
    // Get folder size in bytes
    Route::get('/size', [FolderController::class, 'size'])->name('size');
    // Create folder
    Route::post('/create', [FolderController::class, 'create'])->name('create');
    // Copy folder
    Route::post('/copy', [FolderController::class, 'copy'])->name('copy');
    // Rename folder
    Route::post('/rename', [FolderController::class, 'rename'])->name('rename');
    // Move folder
    Route::post('/move', [FolderController::class, 'move'])->name('move');
    // Remove folder
    Route::post('/remove', [FolderController::class, 'remove'])->name('remove');
});

Route::group(['as' => 'file.', 'prefix' => '/file'], function () {

});
