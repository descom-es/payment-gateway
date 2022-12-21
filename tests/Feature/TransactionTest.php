<?php

namespace Descom\Payment\Tests\Feature;

use Descom\Payment\Events\TransactionCompleted;
use Descom\Payment\Events\TransactionFailed;
use Descom\Payment\Models\TransactionModel;
use Descom\Payment\Payment;
use Descom\Payment\Tests\Support\OrderModel;
use Descom\Payment\Tests\TestCase;
use Descom\Payment\Transaction;
use Descom\Payment\TransactionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Omnipay\OfflineDummy\App\App;
use Omnipay\OfflineDummy\Gateway as OfflineDummyGateway;

class TransactionTest extends TestCase
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
                ],
            ])
            ->create('payment1');
    }

    public function testCreateTransaction()
    {
        $transaction = Transaction::for($this->payment)->create(12, 1);

        $this->assertEquals(12, $transaction->amount);
        $this->assertEquals(1, $transaction->merchant_id);
    }

    public function testCreateModelWhenCreateATransaction()
    {
        $transaction = Transaction::for($this->payment)->create(12, 1);

        $this->assertNotNull(TransactionModel::find($transaction->id));
    }

    public function testCreateModelWithRelation()
    {
        $transaction = Transaction::for($this->payment)
            ->model(new OrderModel())
            ->create(12, 1);

        $this->assertEquals(OrderModel::class, $transaction->model_type); // @phpstan-ignore-line
        $this->assertEquals((new OrderModel())->getKey(), $transaction->id);
    }

    public function testPurchaseTransaction()
    {
        $response = Transaction::for($this->payment)->create(12, 1)->purchase([
            'description' => 'Test purchase',
        ]);

        $this->assertTrue($response->isRedirect());
        $this->assertEquals(1, $response->getData()['transaction_id']);
        $this->assertEquals(12.00, $response->getData()['amount']);
        $this->assertEquals('https://ok.makey', $response->getData()['url_notify']);
    }

    public function testPurchaseFillRequestInModel()
    {
        Event::fake();

        $transaction = Transaction::for($this->payment)->create(12, 1);

        $transaction->purchase([
            'description' => 'Test purchase',
        ]);

        $transaction->notifyPurchase([
            'transaction_id' => 1,
            'amount' => 12.00,
        ]);

        $this->assertNotEmpty(TransactionModel::find(1)->gateway_request);
    }

    public function testPurchaseCompletedFailed()
    {
        Event::fake();

        $transaction = Transaction::for($this->payment)->create(12, 1);

        $transaction->purchase([
            'description' => 'Test purchase',
        ]);

        $transaction->notifyPurchase([
            'transaction_id' => 1,
            'amount' => 12.00,
        ]);

        Event::assertDispatched(
            TransactionFailed::class,
            fn (TransactionFailed $event) => $event->transactionModel()->status === TransactionStatus::DENIED
        );
    }

    public function testPurchaseCompletedCompleted()
    {
        Event::fake();

        $transaction = Transaction::for($this->payment)->create(12, 1);

        $transaction->purchase([
            'description' => 'Test purchase',
        ]);

        $transaction->notifyPurchase([
            'transaction_id' => 1,
            'amount' => 12.00,
            'status' => App::STATUS_SUCCESS,
        ]);

        Event::assertDispatched(
            TransactionCompleted::class,
            fn (TransactionCompleted $event) => $event->transactionModel()->status === TransactionStatus::PAID
        );
    }
}
