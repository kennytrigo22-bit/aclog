<?php

use App\Http\Controllers\Api\ChatbotController;
use Illuminate\Support\Facades\Route;

Route::post('/chatbot', [ChatbotController::class, 'handle'])->middleware('throttle:30,1,chatbot');
