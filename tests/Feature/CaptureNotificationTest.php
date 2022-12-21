<?php

namespace Descom\Payment\Tests\Feature;

use Descom\Payment\Events\TransactionDenied;
use Descom\Payment\Events\TransactionPaid;
use Descom\Payment\Payment;
use Descom\Payment\Tests\TestCase;
use Descom\Payment\Transaction;
use Descom\Payment\TransactionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Omnipay\OfflineDummy\App\App;
use Omnipay\OfflineDummy\Gateway as OfflineDummyGateway;

class CaptureNotificationTest extends TestCase
{
    use RefreshDatabase;

    private Payment $payment;

    public function setUp(): void
    {
        parent::setUp();

        $this->payment = Payment::for(new OfflineDummyGateway())
            ->config([
                'request' => [
                    'url_notify' => '/payment/payment1/notify',
                ],
            ])
            ->create('payment1');
    }

    public function testCompleted()
    {
        Event::fake();

        $redirect = Transaction::for($this->payment)->create(12, 1)->purchase([
            'description' => 'Test purchase',
        ]);

        $this->post(
            '/payment/payment1/notify',
            array_merge(
                $redirect->getData(),
                [
                    'status' => App::STATUS_SUCCESS,
                ]
            )
        )->assertStatus(204);

        Event::assertDispatched(TransactionPaid::class, function ($event) {
            return $event->transactionModel()->id === 1
                && $event->transactionModel()->status === TransactionStatus::PAID;
        });

        $this->assertTrue(Transaction::find(1)->isSuccessful());
    }

    public function testFailed()
    {
        Event::fake();

        $redirect = Transaction::for($this->payment)->create(12, 1)->purchase([
            'description' => 'Test purchase',
        ]);

        $this->post(
            '/payment/payment1/notify',
            array_merge(
                $redirect->getData(),
                [
                    'status' => App::STATUS_DENIED,
                ]
            )
        )->assertStatus(204);

        Event::assertDispatched(TransactionDenied::class, function ($event) {
            return $event->transactionModel()->id === 1
                && $event->transactionModel()->status === TransactionStatus::DENIED;
        });

        $this->assertTrue(Transaction::find(1)->isDenied());
    }
}
