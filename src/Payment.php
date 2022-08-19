<?php

namespace Descom\Payment;

use Descom\Payment\Builders\PaymentBuilder;
use Descom\Payment\Builders\TransitionBuilder;
use Descom\Payment\Models\PaymentModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Omnipay\Common\AbstractGateway;
use Omnipay\Omnipay;

/**
 * @property string $key  The key of the payment.
 * @property string $gateway  The gateway of the payment.
 * @property string $name  The name of the payment.
 * @property array $config  The config of the payment.
 * @property PaymentModel $payment  The payment model.
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

    public static function find(string $key): Payment
    {
        $payment = PaymentModel::where('key', $key)->firstOrFail();

        return new Payment($payment);
    }

    public function transitionBuilder(): TransitionBuilder
    {
        return new TransitionBuilder($this);
    }

    public function gateway(): AbstractGateway
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

    private function objectToArray(object $object): array
    {
        return json_decode(json_encode($object), true);
    }
}
