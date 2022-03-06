<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthenticationController;
use App\Http\Controllers\BookingController;

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

Route::post('/register', [AuthenticationController::class, 'createAccount']);
Route::post('/login', [AuthenticationController::class, 'signin']);
Route::post('/reset-password-request', 'AuthenticationController@forgotPassword');
Route::post('/password-reset', 'AuthenticationController@passwordReset');

Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::post('/logout', [AuthenticationController::class, 'signout']);
    Route::post('/update-profile', [AuthenticationController::class, 'updateProfile']);
    Route::post('/update-profile-picture', [AuthenticationController::class, 'updateProfilePicture']);
    Route::get('/get-hotel-listing', [BookingController::class, 'getHotelListing']);
    Route::get('/get-single-hotel-detail', [BookingController::class, 'getSingleHotelById']);
    Route::get('/book-hotel', [BookingController::class, 'bookHotel']);
    Route::get('/cancel-booking', [BookingController::class, 'cancelBooking']);
    Route::get('/update-booking', [BookingController::class, 'updateBooking']);
    Route::get('/get-booking-detail', [BookingController::class, 'getBookingInfo']);
});

