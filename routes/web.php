<?php namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('auth.login');
});

/**
 * Route for admin
 */
Route::prefix("admin")->group(function() {
    Route::group(["middleware" => "auth"], function() {
        // Dashboard Admin
        Route::get("/dashboard", [DashboardController::class, "index"])->name("admin.dashboard.index");
        
        // Category
        Route::resource("/category", CategoryController::class, ["as" => "admin"]);
        // Campaign
        Route::resource("/campaign", CampaignController::class, ["as" => "admin"]);
        // Donatur
        Route::get("/donaturs", [DonaturController::class, "index"])->name("admin.donatur.index");
        // Donation
        Route::get('/donation', [DonationController::class, 'index'])->name('admin.donation.index');
        //route profile
        Route::get('/profile', [ProfileController::class, 'index'])->name('admin.profile.index');
        //route resource slider
        Route::resource('/slider', SliderController::class, ['except' => ['show', 'create', 'edit', 'update'], 'as' => 'admin']);
    });
});