<?php

use App\Http\Controllers\DocsController;
use App\Http\Controllers\GraphqlController;
use App\Http\Controllers\TransactionController;
use App\Http\Middleware\RequireIaeApiKey;
use Illuminate\Support\Facades\Route;

Route::options('/{any}', fn () => response()->noContent())->where('any', '.*');

Route::get('/api-docs', [DocsController::class, 'swaggerUi']);
Route::get('/api-docs/', [DocsController::class, 'swaggerUi']);
Route::get('/openapi.json', [DocsController::class, 'openApi']);

Route::get('/graphql', [GraphqlController::class, 'playground']);

Route::middleware(RequireIaeApiKey::class)->group(function (): void {
    Route::get('/api/v1/transactions', [TransactionController::class, 'index']);
    Route::get('/api/v1/transactions/{id}', [TransactionController::class, 'show']);
    Route::post('/api/v1/transactions', [TransactionController::class, 'store']);
    Route::post('/api/v1/transactions/{id}', [TransactionController::class, 'action']);
    Route::post('/api/v1/transactions/{id}/checkout', [TransactionController::class, 'checkout']);
    Route::post('/api/v1/transactions/{id}/pay', [TransactionController::class, 'pay']);

    Route::post('/graphql', [GraphqlController::class, 'query']);
});
