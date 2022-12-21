<?php

namespace Descom\Payment\Tests\Feature;

use Descom\Payment\Events\TransitionCompleted;
use Descom\Payment\Events\TransitionFailed;
use Descom\Payment\Models\TransitionModel;
use Descom\Payment\Payment;
use Descom\Payment\Tests\Support\OrderModel;
use Descom\Payment\Tests\TestCase;
use Descom\Payment\Transition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Omnipay\OfflineDummy\App\App;
use Omnipay\OfflineDummy\Gateway as OfflineDummyGateway;

class TransitionTest extends TestCase
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

    public function testCreateTransition()
    {
        $transition = Transition::for($this->payment)->create(12, 1);

        $this->assertEquals(12, $transition->amount);
        $this->assertEquals(1, $transition->merchant_id);
    }

    public function testCreateModelWhenCreateATransition()
    {
        $transition = Transition::for($this->payment)->create(12, 1);

        $this->assertNotNull(TransitionModel::find($transition->id));
    }

    public function testCreateModelWithSource()
    {
        $transition = Transition::for($this->payment)
            ->model(new OrderModel)
            ->create(12, 1);

        $this->assertEquals(OrderModel::class, $transition->source_type); // @phpstan-ignore-line
        $this->assertEquals((new OrderModel())->getKey(), $transition->id);
    }

    public function testPurchaseTransition()
    {
        $response = Transition::for($this->payment)->create(12, 1)->purchase([
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

        $transition = Transition::for($this->payment)->create(12, 1);

        $transition->purchase([
            'description' => 'Test purchase',
        ]);

        $transition->notifyPurchase([
            'transaction_id' => 1,
            'amount' => 12.00,
        ]);

        $this->assertNotEmpty(TransitionModel::find(1)->gateway_request);
    }

    public function testPurchaseCompletedFailed()
    {
        Event::fake();

        $transition = Transition::for($this->payment)->create(12, 1);

        $transition->purchase([
            'description' => 'Test purchase',
        ]);

        $transition->notifyPurchase([
            'transaction_id' => 1,
            'amount' => 12.00,
        ]);

        Event::assertDispatched(
            TransitionFailed::class,
            fn (TransitionFailed $event) => $event->transitionModel()->status === 'denied'
        );
    }

    public function testPurchaseCompletedCompleted()
    {
        Event::fake();

        $transition = Transition::for($this->payment)->create(12, 1);

        $transition->purchase([
            'description' => 'Test purchase',
        ]);

        $transition->notifyPurchase([
            'transaction_id' => 1,
            'amount' => 12.00,
            'status' => App::STATUS_SUCCESS,
        ]);

        Event::assertDispatched(
            TransitionCompleted::class,
            fn (TransitionCompleted $event) => $event->transitionModel()->status === 'success'
        );
    }
}
