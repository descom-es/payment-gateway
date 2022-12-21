<?php

namespace Descom\Payment\Tests\Feature;

use Descom\Payment\Events\TransitionCompleted;
use Descom\Payment\Events\TransitionFailed;
use Descom\Payment\Models\TransitionModel;
use Descom\Payment\Payment;
use Descom\Payment\Tests\TestCase;
use Descom\Payment\Transition;
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

        $redirect = Transition::for($this->payment)->create(12, 1)->purchase([
            'description' => 'Test purchase',
        ]);

        $this->post(
            '/payment/payment1/notify',
            array_merge(
                $redirect->getRedirectData(),
                [
                    'status' => App::STATUS_SUCCESS,
                ]
            )
        )->assertStatus(204);

        Event::assertDispatched(TransitionCompleted::class, function ($event) {
            return $event->transitionModel()->id === 1
                && $event->transitionModel()->status === 'success';
        });

        $this->assertTrue(Transition::find(1)->isSuccessful());
    }

    public function testFailed()
    {
        Event::fake();

        $redirect = Transition::for($this->payment)->create(12, 1)->purchase([
            'description' => 'Test purchase',
        ]);

        $this->post(
            '/payment/payment1/notify',
            array_merge(
                $redirect->getRedirectData(),
                [
                    'status' => App::STATUS_DENIED,
                ]
            )
        )->assertStatus(204);

        Event::assertDispatched(TransitionFailed::class, function ($event) {
            return $event->transitionModel()->id === 1
                && $event->transitionModel()->status === 'denied';
        });

        $this->assertTrue(Transition::find(1)->isDenied());
    }
}
