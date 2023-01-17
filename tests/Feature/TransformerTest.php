<?php

namespace Descom\Payment\Tests\Feature;

use Descom\Payment\Events\TransactionPaid;
use Descom\Payment\Payment;
use Descom\Payment\Tests\Support\TransformerCustom;
use Descom\Payment\Tests\TestCase;
use Descom\Payment\Transaction;
use Descom\Payment\TransactionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Omnipay\OfflineDummy\App\App;
use Omnipay\OfflineDummy\Gateway as OfflineDummyGateway;

class TransformerTest extends TestCase
{
    use RefreshDatabase;

    private Payment $payment;

    public function setUp(): void
    {
        parent::setUp();

        $this->payment = Payment::for(new OfflineDummyGateway())
            ->config([
                'request' => [
                    'url_notify' => 'https://ok.makey',
                    'url_return' => 'https://ok.makey/{transactionId}/redirect',
                ],
            ])
            ->transformer(new TransformerCustom())
            ->create('payment1');
    }

    public function testPurchaseTransaction()
    {
        $response = Transaction::for($this->payment)->create(12, 1)->purchase([
            'description' => 'Test purchase',
        ]);

        $this->assertTrue($response->isRedirect());
        $this->assertEquals('0000Y1', $response->getData()['transaction_id']);
        $this->assertEquals(12.00, $response->getData()['amount']);
        $this->assertEquals('https://ok.makey', $response->getData()['url_notify']);
    }

    public function testPurchaseCompletedCompleted()
    {
        Event::fake();

        $transaction = Transaction::for($this->payment)->create(12, 1);

        $transaction->purchase([
            'description' => 'Test purchase',
        ]);

        $transaction->notifyPurchase([
            'transaction_id' => '0000Y1',
            'amount' => 12.00,
            'status' => App::STATUS_SUCCESS,
        ]);

        Event::assertDispatched(
            TransactionPaid::class,
            fn (TransactionPaid $event) => $event->transactionModel()->status === TransactionStatus::PAID
        );
    }

    public function testCapturePaid()
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
            return $event->transactionModel()->merchant_id === '1'
                && $event->transactionModel()->status === TransactionStatus::PAID;
        });

        $this->assertTrue(Transaction::find(1)->isSuccessful());
    }
}
