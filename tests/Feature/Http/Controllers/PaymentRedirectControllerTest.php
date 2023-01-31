<?php

namespace Descom\Payment\Tests\Feature\Http\Controllers;

use Descom\Payment\Payment;
use Descom\Payment\Tests\TestCase;
use Descom\Payment\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Omnipay\OfflineDummy\App\App;
use Omnipay\OfflineDummy\Gateway as OfflineDummyGateway;

class PaymentRedirectControllerTest extends TestCase
{
    use RefreshDatabase;

    private Payment $payment;

    public function setUp(): void
    {
        parent::setUp();

        $this->payment = Payment::for(new OfflineDummyGateway())
            ->config([
                'return_url' => '/payment/{transactionId}/return',
                'cancel_url' => '/payment/{transactionId}/cancel',
                'request' => [
                    'url_notify' => '/payment/payment1/notify',
                    'url_return' => '/payment/payment1/redirect',
                ],
            ])
            ->create('payment1');
    }

    public function testCompleted()
    {
        Event::fake();

        $redirect = Transaction::for($this->payment)->create(12, 'A01')->purchase([
            'description' => 'Test purchase',
        ]);

        $this->get(
            '/payment/1/redirect',
            array_merge(
                $redirect->getData(),
                [
                    'status' => App::STATUS_SUCCESS,
                ]
            )
        )->assertStatus(302)
        ->assertRedirect('http://localhost/payment/1/cancel');
    }
}
