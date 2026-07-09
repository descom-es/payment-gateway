<?php

use Descom\Payment\Http\Controllers\PaymentNotificationController;
use Descom\Payment\Http\Controllers\PaymentRedirectController;
use Descom\Payment\Http\Controllers\PaymentReturnController;
use Illuminate\Support\Facades\Route;

Route::match(
    ['get', 'post'],
    '/payment/{payment_key}/notify',
    PaymentNotificationController::class
)->name('payment.notify');

Route::get(
    '/payment/{payment_key}/return/{transaction}',
    PaymentReturnController::class
)->name('payment.return');

Route::get(
    '/payment/{id}/redirect',
    PaymentRedirectController::class
)->name('payment.redirect');
