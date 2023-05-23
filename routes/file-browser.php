<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FileBrowserController;

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

// Copy folder or file
Route::post('/copy', [FileBrowserController::class, 'copy'])->name('copy');
// Create folder
Route::post('/create', [FileBrowserController::class, 'create'])->name('create');
// View folder or file info
Route::post('/info', [FileBrowserController::class, 'info'])->name('info');
// List folder content
Route::post('/list', [FileBrowserController::class, 'list'])->name('list');
// Move / Rename folder or file
Route::post('/move', [FileBrowserController::class, 'move'])->name('move');
// Remove folder or file
Route::post('/remove', [FileBrowserController::class, 'remove'])->name('remove');
// Search folder or file in sub folder
Route::post('/search/{name}', [FileBrowserController::class, 'search'])->name('search');
// View folder or file size
Route::post('/size', [FileBrowserController::class, 'size'])->name('size');
// Upload file
Route::post('/upload', [FileBrowserController::class, 'upload'])->name('upload');
