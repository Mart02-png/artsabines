<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\EventController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\LimitClientController;

// Route::resource('/events', EventController::class);
Route::get('/events', [EventController::class, 'index']);
Route::post('/events', [EventController::class, 'store']);
Route::put('/events/{event}', [EventController::class, 'update']); // Handle PUT requests for updating an event
Route::patch('/events/{event}', [EventController::class, 'update']); // Handle PATCH requests for updating an event
Route::delete('/events/{event}', [EventController::class, 'destroy']);

Route::put('/events/accept/{event}', [EventController::class, 'updateClient']);
Route::patch('/events/accept/{event}', [EventController::class, 'updateClient']);
Route::delete('/events/accept/{event}', [EventController::class, 'destroyClient']);

Route::get('/limit', [LimitClientController::class, 'index']);
Route::post('/limit', [LimitClientController::class, 'store']);
Route::put('/limit/{id}', [LimitClientController::class, 'update']); // Handle PUT requests for updating an event
Route::patch('/limit/{id}', [LimitClientController::class, 'update']); // Handle PATCH requests for updating an event
Route::delete('/limit/{id}', [LimitClientController::class, 'destroy']);



// Route::get('/admins', [AdminController::class, 'index']); // List all admins
// Route::post('/admins', [AdminController::class, 'store']); // Create a new admin
// Route::get('/admins/{admin}', [AdminController::class, 'show']); // Show a specific admin
// Route::put('/admins/{admin}', [AdminController::class, 'update']); // Update a specific admin
// Route::delete('/admins/{admin}', [AdminController::class, 'destroy']); // Delete a specific admin

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::middleware(['api'])->prefix('auth')->group(function () {
//     Route::post('/register', [AdminController::class, 'register']);
//     Route::post('/login', [AdminController::class, 'login']);
//     Route::post('/logout', [AdminController::class, 'logout']);
// });

Route::middleware(['api'])->prefix('auth')->group(function () {
    Route::post('/users', [AdminController::class, 'index']);
    Route::post('/register', [AdminController::class, 'register']);
    Route::post('/login', [AdminController::class, 'login']);
    Route::post('/logout', [AdminController::class, 'logout']);
});