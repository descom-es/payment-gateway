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
                'return_url' => 'http:/www.localhost/checkout/success',
                'cancel_url' => 'http:/www.localhost/checkout/cancel',
                'request' => [
                    'notify_url' => 'http:/api.localhost/payment/paymentdemo/notify',
                    'return_url' => 'http:/api.localhost/payment/{parameterId}/redirect',
                ],
            ]))
            ->transformer() // Optional, you can use your own transformer with interface Descom\Payment\Transformers\Transformer
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
use Descom\Payment\Transaction;

$payment = Payment::find('paymentdemo');

$transaction = Transaction::for($payment)->create([
    'amount' => '10.00',
    'merchant_id' => 'order_1',
]);
```

You optionally can add a relation of a external model:

```php
<?php
use Descom\Payment\Payment;
use Descom\Payment\Transaction;

$payment = Payment::find('paymentdemo');

$transaction = Transaction::for($payment)
    ->model(Order::find(1))
    ->create([
        'amount' => '10.00',
        'merchant_id' => 'order_1',
    ]);
```

```php

## Capture Notification

Create Listener to events:

- `Descom\Payment\Events\TransactionPaid`
- `Descom\Payment\Events\TransactionDenied`
