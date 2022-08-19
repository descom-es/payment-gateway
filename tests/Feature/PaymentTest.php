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
        $Payment = Payment::for(new OfflineDummyGateway())
            ->name('Forma de pago 1')
            ->config([
                'notify_url' => 'http://localhost',
            ])
            ->create('payment1');

        $this->assertEquals('payment1', $Payment->key);
        $this->assertEquals('Forma de pago 1', $Payment->name);
        $this->assertEquals(OfflineDummyGateway::class, $Payment->gateway);
        $this->assertEquals('http://localhost', $Payment->config->notify_url);
    }

    public function test_get_gateway_by_key()
    {
        Payment::for(new OfflineDummyGateway())
            ->name('Forma de pago 1')
            ->config([
                'notify_url' => 'http://localhost',
            ])
            ->create('payment1');

        $Payment = Payment::find('payment1');

        $this->assertEquals('payment1', $Payment->key);
        $this->assertEquals('Forma de pago 1', $Payment->name);
        $this->assertEquals(OfflineDummyGateway::class, $Payment->gateway);
        $this->assertEquals('http://localhost', $Payment->config->notify_url);
    }
}
