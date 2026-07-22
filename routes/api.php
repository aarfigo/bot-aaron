<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth:api'])->prefix('v1')->group(function(){
    Route::get('orders/current','Api\OrderController@current');
    Route::post('orders/{order}/items','Api\OrderController@addItem');
    // New AJAX route - addItem via JSON
    Route::post('orders/{order}/items/json', [\App\Http\Controllers\Api\OrderApiController::class, 'addItem']);
});
