<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ProjectController;

Route::get('/', [ProjectController::class, 'index'])->name('home');
Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');
Route::get('/projects/{id}/logs', [ProjectController::class, 'logs'])->name('projects.logs');
Route::post('/projects/{id}/clear', [ProjectController::class, 'clear'])->name('projects.clear');
