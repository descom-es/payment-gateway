<?php

namespace Descom\Payment\Tests\Feature;

use Descom\Payment\Payment;
use Descom\Payment\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Omnipay\OfflineDummy\Gateway as OfflineDummyGateway;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatePayment()
    {
        $payment = Payment::for(new OfflineDummyGateway())
            ->name('Forma de pago 1')
            ->config([
                'notify_url' => 'http://localhost/notify',
                'return_url' => 'http://localhost/completed',
                'cancel_url' => 'http://localhost/failed',
            ])
            ->create('payment1');

        $this->assertEquals('payment1', $payment->key);
        $this->assertEquals('Forma de pago 1', $payment->name);
        $this->assertEquals(OfflineDummyGateway::class, $payment->gateway);
        $this->assertEquals('http://localhost/notify', $payment->config->notify_url);
        $this->assertEquals('http://localhost/completed', $payment->config->return_url);
        $this->assertEquals('http://localhost/failed', $payment->config->cancel_url);
    }

    public function testGetGatewayByKey()
    {
        Payment::for(new OfflineDummyGateway())
            ->name('Forma de pago 1')
            ->create('payment1');

        $payment = Payment::find('payment1');

        $this->assertEquals('payment1', $payment->key);
        $this->assertEquals('Forma de pago 1', $payment->name);
        $this->assertEquals(OfflineDummyGateway::class, $payment->gateway);
    }

    public function testCreatePaymentFailedIfKeyExists()
    {
        Payment::for(new OfflineDummyGateway())
            ->name('Forma de pago 1')
            ->create('payment1');

        $this->expectException(ValidationException::class);

        Payment::for(new OfflineDummyGateway())
            ->name('Forma de pago 2')
            ->create('payment1');
    }

    public function testNotExistsPayment()
    {
        $this->assertFalse(Payment::exists('payment1'));
    }

    public function testExistsPayment()
    {
        Payment::for(new OfflineDummyGateway())
            ->name('Forma de pago 1')
            ->create('payment1');

        $this->assertTrue(Payment::exists('payment1'));
    }
}
