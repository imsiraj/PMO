<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleSheetsController;
use App\Http\Controllers\AuthController;
use Illuminate\Routing\Middleware\ValidateSignature;
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
    return view('welcome');
});
Route::get('/user-email-verification/{user_id}/{token}',[AuthController::class,'userEmailVerification'])->middleware(ValidateSignature::class)->name('user-email-verify');