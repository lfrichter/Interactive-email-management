<?php

use App\Http\Controllers\WebhookController;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tasks', function () {
    return view('tasks');
});

Route::post('/webhook/email-inbound', [WebhookController::class, 'emailInbound'])->withoutMiddleware(VerifyCsrfToken::class);