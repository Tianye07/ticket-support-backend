<?php

use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::group([
    'controller' => TicketController::class,
    'prefix' => 'tickets',
], function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('/{id}', 'show');
    Route::put('/{id}', 'update');
    Route::delete('/{id}', 'delete');
});
