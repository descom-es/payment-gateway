# Descom Payment Gateway

DPG is a package to Laravel that allows you to integrate the Payment Gateway into your application.
This package use [Omnipay](https://github.com/thephpleague/omnipay) to integrate the payment gateway.

[![tests](https://github.com/descom-es/payment-gateway/actions/workflows/tests.yml/badge.svg)](https://github.com/descom-es/payment-gateway/actions/workflows/tests.yml)
[![analyze](https://github.com/descom-es/payment-gateway/actions/workflows/analyse.yml/badge.svg)](https://github.com/descom-es/payment-gateway/actions/workflows/analyse.yml)
[![style-fix](https://github.com/descom-es/payment-gateway/actions/workflows/style-fix.yml/badge.svg)](https://github.com/descom-es/payment-gateway/actions/workflows/style-fix.yml)

## Installation

```sh
composer require descom/payment-gateway
```

You can install any module to `Omnipay`, sample:

```sh
composer require descom/omnipay-offline-dummy
```

## Usage

### Create a Payment Method

```php
<?php

use Descom\Payment\Payment;
use Omnipay\OfflineDummy\Gateway as OfflineDummyGateway;

Payment::for(new OfflineDummyGateway())
            ->name('Method Name')
            ->config([
                'notify_url' => 'http:/api.localhost/payment/gateway/notify',
                'return_url' => 'http:/www.localhost/checkout/success',
                'cancel_url' => 'http:/www.localhost/checkout/cancel',
            ]))
            ->create('paymentdemo');
```

### Access a Payment Method

```php
<?php
use Descom\Payment\Payment;

$payment = Payment::find('paymentdemo');
```

### Create Transaction

```php
<?php
use Descom\Payment\Payment;
use Descom\Payment\Transition;

$payment = Payment::find('paymentdemo');

$transition = Transition::for($payment)->create([
    'amount' => '10.00',
    'merchant_id' => 'order_1',
]);
```

## Capture Notification

Create Listener to events:

- `Descom\Payment\Events\TransitionCompleted`
- `Descom\Payment\Events\TransitionFailed`
