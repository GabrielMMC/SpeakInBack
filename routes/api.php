<?php

use App\Http\Controllers\CallController;
use App\Http\Controllers\UserController;
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

Route::group([
    "prefix" => "auth"
], function () {
    Route::post("login", [UserController::class, "login"]);
    Route::post("register", [UserController::class, "register"]);
});

Route::group([
    "prefix" => "room"
], function () {
    Route::group([
        "middleware" => ["auth:api"]
    ], function () {
        Route::post("/connect", [CallController::class, "roomConnection"]);
    });
});
