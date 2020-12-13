<?php

use Sowren\ShurjoPay\Http\Controllers\ShurjoPayController;

// Route to handle ShurjoPay response
Route::post('/response', [ShurjoPayController::class, 'response'])->name('shurjopay.response');
