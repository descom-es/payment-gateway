<?php

namespace Descom\Payment\Tests\Feature\Drivers;

use Descom\Payment\Events\TransactionPaid;
use Descom\Payment\Payment;
use Descom\Payment\Tests\Support\TransformerCustom;
use Descom\Payment\Tests\TestCase;
use Descom\Payment\Transaction;
use Descom\Payment\TransactionStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Omnipay\OfflineDummy\App\App;
use Omnipay\Redsys\Gateway;

class RedsysTest extends TestCase
{
    use RefreshDatabase;

    private Payment $payment;

    public function setUp(): void
    {
        parent::setUp();

        $this->payment = Payment::for(new Gateway())
            ->config([
                'merchantCode' => '999008881',
                'merchantTerminal' => '1',
                'merchantSignatureKey' => 'sq7HjrUOBfKmC576ILgskD5srU870gJ7',
                'testMode' => true,
                'request' => [
                    'url_notify' => 'https://ok.makey',
                    'url_return' => 'https://ok.makey/{transactionId}/redirect',
                ],
            ])
            ->transformer(new TransformerCustom())
            ->create('payment1');
    }


    public function testCapturePaid()
    {
        Event::fake();

        $redirect = Transaction::for($this->payment)->create(12, 120)->purchase([
            'description' => 'Test purchase',
        ]);

        $this->post('/payment/payment1/notify', [
            'SignatureVersion' => 'HMAC_SHA256_V1',
            'Ds_MerchantParameters' => 'eyJEc19EYXRlIjoiMTclMkYwMSUyRjIwMjMiLCJEc19Ib3VyIjoiMTUlM0E1MiIsIkRzX1NlY3VyZVBheW1lbnQiOiIxIiwiRHNfQ2FyZF9OdW1iZXIiOiI0NTQ4ODEqKioqKiowMDAzIiwiRHNfQ2FyZF9Db3VudHJ5IjoiNzI0IiwiRHNfQW1vdW50IjoiNjc4NCIsIkRzX0N1cnJlbmN5IjoiOTc4IiwiRHNfT3JkZXIiOiIwMDEyWTAiLCJEc19NZXJjaGFudENvZGUiOiI5OTkwMDg4ODEiLCJEc19UZXJtaW5hbCI6IjAwMSIsIkRzX1Jlc3BvbnNlIjoiMDAwMCIsIkRzX01lcmNoYW50RGF0YSI6IiIsIkRzX1RyYW5zYWN0aW9uVHlwZSI6IjAiLCJEc19Db25zdW1lckxhbmd1YWdlIjoiMSIsIkRzX0F1dGhvcmlzYXRpb25Db2RlIjoiMTY0Mzc4IiwiRHNfQ2FyZF9CcmFuZCI6IjEiLCJEc19QZXJtaXRlUmVpbnRlbnRvIjoiU0kiLCJEc19Qcm9jZXNzZWRQYXlNZXRob2QiOiI3OCIsIkRzX0NvbnRyb2xfMTY3Mzk2NzE3MTQ1NyI6IjE2NzM5NjcxNzE0NTcifQ==',
            'Ds_Signature' => 'a13oIHnhMls-qcfKBwCV7LCcymp6_KQ5OxxEkB6DD40=',
        ])->assertStatus(204);

        Event::assertDispatched(TransactionPaid::class, function ($event) {
            return $event->transactionModel()->merchant_id === '120'
                && $event->transactionModel()->status === TransactionStatus::PAID;
        });

        $this->assertTrue(Transaction::find(1)->isSuccessful());
    }
}
