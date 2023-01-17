<?php

namespace Descom\Payment;

use Descom\Payment\Builders\TransactionBuilder;
use Descom\Payment\Events\TransactionDenied;
use Descom\Payment\Events\TransactionPaid;
use Descom\Payment\Models\TransactionModel;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;

/**
 * @property int $id
 * @property string|float $amount
 * @property string $merchant_id
 * @property \Descom\Payment\Models\PaymentModel $payment  Retrieve payment model
 */
final class Transaction
{
    public function __construct(private TransactionModel $transactionModel)
    {
    }

    public static function for(Payment $payment): TransactionBuilder
    {
        return new TransactionBuilder($payment);
    }

    public static function find(int $id): Transaction
    {
        return new Transaction(TransactionModel::with('payment')->findOrFail($id));
    }

    public function purchase(array $request = []): ResponseInterface
    {
        $paymentRequest = isset($this->payment->config->request)
            ? json_decode(json_encode($this->payment->config->request), true)
            : [];

        $paymentRequest = array_map(
            fn ($value) => str_replace(
                ['{transactionId}'],
                [$this->transactionModel->id],
                $value
            ),
            $paymentRequest
        );

        $data = array_merge(
            $request,
            $paymentRequest,
            [
                'amount' => $this->transactionModel->amount,
                'transactionId' => $this->transactionModel->merchant_id,
            ]
        );

        $this->transactionModel->gateway_request = $data;

        return $this->gateway()->purchase($data)->send();
    }

    public function redirectPurchase(array $request): ResponseInterface
    {
        return $this->gateway()->completePurchase($request)->send();
    }

    public function notifyPurchase(array $request): ResponseInterface
    {
        $response = $this->redirectPurchase($request);

        $this->transactionModel->gateway_id = $response->getTransactionReference();
        $this->transactionModel->gateway_response = $response->getData();
        $this->transactionModel->status = $response->isSuccessful()
            ? TransactionStatus::PAID :
            TransactionStatus::DENIED;

        $this->transactionModel->save();

        $event = $response->isSuccessful()
            ? new TransactionPaid($this->transactionModel)
            : new TransactionDenied($this->transactionModel);

        event($event);

        return $response;
    }

    public function __get(string $param)
    {
        return $this->transactionModel->$param ?? null;
    }

    public function isSuccessful(): bool
    {
        return $this->transactionModel->isSuccessful();
    }

    public function isDenied(): bool
    {
        return $this->transactionModel->isDenied();
    }

    private function gateway(): GatewayInterface
    {
        $paymentKey = $this->transactionModel->payment->key;

        return Payment::find($paymentKey)->gateway();
    }
}
