<?php

namespace Descom\Payment\Tests\Feature;

use Descom\Payment\Events\TransactionDenied;
use Descom\Payment\Events\TransactionPaid;
use Descom\Payment\Models\TransactionModel;
use Descom\Payment\Payment;
use Descom\Payment\Tests\Support\OrderModel;
use Descom\Payment\Tests\TestCase;
use Descom\Payment\Transaction;
use Descom\Payment\TransactionStatus;
use Descom\Redsys\Merchants\MerchantBuilder;
use Descom\Redsys\Parameters;
use Descom\Redsys\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Omnipay\OfflineDummy\App\App;
use Omnipay\OfflineDummy\Gateway as OfflineDummyGateway;
use Omnipay\Redsys\Message\Rest\BuildFromRedsysResponse;

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
                    'url_return' => 'https://ok.makey/{transactionId}/redirect',
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

    public function testRequestVariable()
    {
        $response = Transaction::for($this->payment)->create(12, 1)->purchase([
            'description' => 'Test purchase',
        ]);

        $this->assertEquals('https://ok.makey/1/redirect', $response->getData()['url_return']);
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
            TransactionDenied::class,
            fn (TransactionDenied $event) => $event->transactionModel()->status === TransactionStatus::DENIED
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
            TransactionPaid::class,
            fn (TransactionPaid $event) => $event->transactionModel()->status === TransactionStatus::PAID
        );
    }

    public function testPurchaseFillRequestInModelWithResponse()
    {
        Event::fake();

        $transaction = Transaction::for($this->payment)->create(12, 1);

        $transaction->purchase([
            'description' => 'Test purchase',
        ]);

        $transaction->responsePurchase(new BuildFromRedsysResponse(new Response(
            MerchantBuilder::testing(),
            new Parameters([
                'Ds_MerchantCode' => '999008881',
                'Ds_Terminal' => '1',
                'Ds_Response' => '0000',
                'Ds_Amount' => '145',
                'Ds_Order' => '12346',
                'Ds_AuthorisationCode' => '145',
            ])
        )));

        $this->assertNotEmpty(TransactionModel::find(1)->gateway_request);
    }

    public function testPurchaseCompletedFailedWithResponse()
    {
        Event::fake();

        $transaction = Transaction::for($this->payment)->create(12, 1);

        $transaction->purchase([
            'description' => 'Test purchase',
        ]);

        $transaction->responsePurchase(new BuildFromRedsysResponse(new Response(
            MerchantBuilder::testing(),
            new Parameters([
                'Ds_MerchantCode' => '999008881',
                'Ds_Terminal' => '1',
                'Ds_Response' => '1000',
                'Ds_Amount' => '145',
                'Ds_Order' => '12346',
                'Ds_AuthorisationCode' => '145',
            ])
        )));

        Event::assertDispatched(
            TransactionDenied::class,
            fn (TransactionDenied $event) => $event->transactionModel()->status === TransactionStatus::DENIED
        );
    }

    public function testPurchaseCompletedCompletedWithResponse()
    {
        Event::fake();

        $transaction = Transaction::for($this->payment)->create(12, 1);

        $transaction->purchase([
            'description' => 'Test purchase',
        ]);

        $transaction->responsePurchase(new BuildFromRedsysResponse(new Response(
            MerchantBuilder::testing(),
            new Parameters([
                'Ds_MerchantCode' => '999008881',
                'Ds_Terminal' => '1',
                'Ds_Response' => '0000',
                'Ds_Amount' => '145',
                'Ds_Order' => '12346',
                'Ds_AuthorisationCode' => '145',
            ])
        )));

        Event::assertDispatched(
            TransactionPaid::class,
            fn (TransactionPaid $event) => $event->transactionModel()->status === TransactionStatus::PAID
        );
    }
}
