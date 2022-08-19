<?php

namespace Descom\Payment\Tests\Feature;

use Descom\Payment\Payment;
use Descom\Payment\Services\PaymentService;
use Descom\Payment\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Omnipay\OfflineDummy\Gateway as OfflineDummyGateway;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_payment()
    {
        $payment = Payment::for(new OfflineDummyGateway())
            ->name('Forma de pago 1')
            ->config([
                'url_notify' => 'http://localhost',
            ])
            ->create('payment1');

        $this->assertEquals('payment1', $payment->key);
        $this->assertEquals('Forma de pago 1', $payment->name);
        $this->assertEquals(OfflineDummyGateway::class, $payment->gateway);
        $this->assertEquals('http://localhost', $payment->config->url_notify);
    }

    public function test_get_gateway_by_key()
    {
        Payment::for(new OfflineDummyGateway())
            ->name('Forma de pago 1')
            ->config([
                'url_notify' => 'http://localhost',
            ])
            ->create('payment1');

        $payment = Payment::find('payment1');

        $this->assertEquals('payment1', $payment->key);
        $this->assertEquals('Forma de pago 1', $payment->name);
        $this->assertEquals(OfflineDummyGateway::class, $payment->gateway);
        $this->assertEquals('http://localhost', $payment->config->url_notify);
    }
}
