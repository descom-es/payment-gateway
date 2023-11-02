<?php

namespace Descom\Payment;

use Descom\Payment\Builders\PaymentBuilder;
use Descom\Payment\Builders\TransactionBuilder;
use Descom\Payment\Models\PaymentModel;
use Omnipay\Common\AbstractGateway;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use Omnipay\Omnipay;

/**
 * @property string $key  The key of the payment.
 * @property string $gateway  The gateway of the payment.
 * @property string $name  The name of the payment.
 * @property string $visibility  The visibility of the payment.
 * @property object|array $config  The config of the payment.
 * @property PaymentModel $paymentModel  The payment model.
 */
class Payment
{
    public function __construct(private PaymentModel $payment)
    {
    }

    public static function for(AbstractGateway $gateway): PaymentBuilder
    {
        return new PaymentBuilder($gateway);
    }

    public static function exists(string $key): bool
    {
        return PaymentModel::where('key', $key)->exists();
    }

    public static function find(string $key): Payment
    {
        $payment = PaymentModel::where('key', $key)->firstOrFail();

        return new Payment($payment);
    }

    public function transactionBuilder(): TransactionBuilder
    {
        return new TransactionBuilder($this);
    }

    public function gateway(): GatewayInterface
    {
        $gatewayClassName = $this->payment->gateway;

        $config = $this->objectToArray($this->payment->config);

        return Omnipay::create($gatewayClassName)
            ->initialize($config);
    }

    public function __get(string $key)
    {
        if ($key === 'paymentModel') {
            return $this->payment;
        }

        return $this->payment->$key ?? null;
    }

    public function responseCompletePurchase(array $request): ResponseInterface
    {
        $paymentRequest = isset($this->payment->config->request)
            ? json_decode(json_encode($this->payment->config->request), true)
            : [];

        return $this->gateway()->completePurchase(array_merge($paymentRequest, $request))->send();
    }

    private function objectToArray(array|object $object): array
    {
        return is_array($object) ? $object : json_decode(json_encode($object), true);
    }
}
