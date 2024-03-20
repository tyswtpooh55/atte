<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StampingController;
use App\Http\Controllers\AttendanceController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::middleware('auth')->group(function () {
    Route::get('/', [StampingController::class, 'index']);
    Route::post('/workin', [StampingController::class, 'workIn']);
    Route::post('/workout', [StampingController::class, 'workOut']);
    Route::post('/breakingin', [StampingController::class, 'breakingIn']);
    Route::post('/breakingout', [StampingController::class, 'breakingOut']);
    Route::get('/attendance', [AttendanceController::class, 'attendance'])->name('attendance');
});
