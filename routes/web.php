<?php

// Route to handle ShurjoPay response
Route::post('/response', 'ShurjoPayController@response')->name('shurjopay.response');
