<?php

namespace Descom\Payment;

use Descom\Payment\Builders\TransitionBuilder;
use Descom\Payment\Events\TransitionCompleted;
use Descom\Payment\Events\TransitionFailed;
use Descom\Payment\Models\TransitionModel;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;

/**
 * @property int $id
 * @property string|float $amount
 * @property string $merchant_id
 * @property \Descom\Payment\Models\PaymentModel $payment  Retrieve payment model
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
        return new Transition(TransitionModel::with('payment')->findOrFail($id));
    }

    public function purchase(array $request = []): ResponseInterface
    {
        $paymentRequest = isset($this->payment->config->request)
            ? json_decode(json_encode($this->payment->config->request), true)
            : [];

        $data = array_merge(
            $request,
            $paymentRequest,
            [
                'amount' => $this->transitionModel->amount,
                'transactionId' => $this->transitionModel->id,
            ]
        );

        $this->transitionModel->gateway_request = $data;

        return $this->gateway()->purchase($data)->send();
    }

    public function redirectPurchase(array $request): ResponseInterface
    {
        return $this->gateway()->completePurchase($request)->send();
    }

    public function notifyPurchase(array $request): ResponseInterface
    {
        $response = $this->redirectPurchase($request);

        $this->transitionModel->gateway_id = $response->getTransactionReference();
        $this->transitionModel->gateway_response = $response->getData();
        $this->transitionModel->status = $response->isSuccessful()
            ? TransitionStatus::PAID :
            TransitionStatus::DENIED;

        $this->transitionModel->save();

        $event = $response->isSuccessful()
            ? new TransitionCompleted($this->transitionModel)
            : new TransitionFailed($this->transitionModel);

        event($event);

        return $response;
    }

    public function __get(string $param)
    {
        return $this->transitionModel->$param ?? null;
    }

    public function isSuccessful(): bool
    {
        return $this->transitionModel->isSuccessful();
    }

    public function isDenied(): bool
    {
        return $this->transitionModel->isDenied();
    }

    private function gateway(): GatewayInterface
    {
        $paymentKey = $this->transitionModel->payment->key;

        return Payment::find($paymentKey)->gateway();
    }
}
