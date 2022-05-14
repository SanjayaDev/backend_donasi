<?php

use App\Http\Controllers\Api\{
    RegisterController,
    LoginController,
    CategoryController,
    CampaignController,
    SliderController,
    ProfileController,
    DonationController
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

/**
 * API Register
 * 
 */
Route::post("/register", [RegisterController::class, "register"]);
Route::post("/login", [LoginController::class, "login"]);

// Category API
Route::get('/category', [CategoryController::class, 'index']);
Route::get('/category/{slug}', [CategoryController::class, 'show']);
Route::get('/categoryHome', [CategoryController::class, 'categoryHome']);

/**
 * Api Campaign
 */
Route::get('/campaign', [CampaignController::class, 'index']);
Route::get('/campaign/{slug}', [CampaignController::class, 'show']);

/**
 * Api Slider
 */
Route::get('/slider', [SliderController::class, 'index']);

/**
 * Api Profile
 */
Route::get('/profile', [ProfileController::class, 'index']);
Route::post('/profile', [ProfileController::class, 'update'])->middleware('auth:api');
Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->middleware('auth:api');

/**
 * Api Donation
 */
Route::get('/donation', [DonationController::class, 'index'])->middleware('auth:api');
Route::post('/donation', [DonationController::class, 'store'])->middleware('auth:api');
Route::post('/donation/notification', [DonationController::class, 'notificationHandler']);

/**
 * API Reset Password without login
 * 
 */
Route::post("/forgot-password", [ProfileController::class, "updatePasswordWithoutLogin"]);
