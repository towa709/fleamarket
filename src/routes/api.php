<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;

Route::middleware('auth:sanctum')->group(function () {

  Route::get('/chat/list', [ChatController::class, 'list']);
  Route::get('/chat/partner/{transaction_id}', [ChatController::class, 'getPartner']);
  Route::get('/chat/{transaction_id}', [ChatController::class, 'getMessages']);
  Route::post('/chat/{transaction_id}', [ChatController::class, 'storeMessage']);
  Route::post('/chat/{transaction_id}/read', [ChatController::class, 'markAsRead']);

  Route::patch('/chat/message/{message}', [ChatController::class, 'update']);
  Route::delete('/chat/message/{message}', [ChatController::class, 'destroy']);

  Route::get('/mypage/progress-list', [ProfileController::class, 'progressList']);
  Route::get('/notifications/unread-total', [ProfileController::class, 'unreadTotal']);
  Route::get('/notifications/unread-list', [ProfileController::class, 'unreadList']);
});
