<?php

namespace Descom\Payment;

use Descom\Payment\Builders\TransitionBuilder;
use Descom\Payment\Models\TransitionModel;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Omnipay;

/**
 * @property int $id
 * @property string|float $amount
 * @property string $merchant_id
 */
final class Transition
{
    public function __construct(private TransitionModel $transitionModel)
    {
    }

    public static function for(Payment $payment): TransitionBuilder
    {
        return new TransitionBuilder($payment);
    }

    public static function find(int $id): Transition
    {
        return new Transition(TransitionModel::findOrFail($id));
    }

    public function purchase(array $request = []): ResponseInterface
    {
        return $this->gateway()->purchase(
            array_merge(
                $request,
                [
                    'amount' => $this->transitionModel->amount,
                    'transactionId' => $this->transitionModel->merchant_id,
                ]
        ))->send();
    }

    private function gateway(): GatewayInterface
    {
        $paymentKey = $this->transitionModel->payment->key;

        return Payment::find($paymentKey)->gateway();
    }

    public function __get(string $param)
    {
        return $this->transitionModel->$param ?? null;
    }
}
