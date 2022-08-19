<?php

namespace Descom\Payment\Tests\Feature;

use Descom\Payment\Models\TransitionModel;
use Descom\Payment\Payment;
use Descom\Payment\Tests\TestCase;
use Descom\Payment\Transition;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
                'notify_url' => 'http://localhost/payment/notify',
                'return_url' => 'http://localhost/checkout/return',
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

    public function testPurchaseTransition()
    {
        $response = Transition::for($this->payment)->create(12, 1)->purchase([
            'description' => 'Test purchase',
        ]);

        $this->assertTrue($response->isRedirect());

        dd($response->getData());
    }
}
