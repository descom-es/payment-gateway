<?php

namespace Descom\Payment\Builders;

use Descom\Payment\Models\PaymentModel;
use Descom\Payment\Payment;
use Descom\Payment\Transformers\Transformer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Omnipay\Common\AbstractGateway;

final class PaymentBuilder
{
    private string $name = '';

    private ?Transformer $transformer = null;

    private array $config = [];

    public function __construct(private AbstractGateway $gateway)
    {
    }

    public function name(string $name): PaymentBuilder
    {
        $this->name = $name;

        return $this;
    }

    public function config(array $config): PaymentBuilder
    {
        $this->config = $config;

        return $this;
    }

    public function transformer(?Transformer $transformer): PaymentBuilder
    {
        $this->transformer = $transformer;

        return $this;
    }

    public function create(string $key): Payment
    {
        $request = [
            'key' => $key,
            'name' => $this->name ?: $key,
            'config' => $this->config,
            'gateway' => $this->gateway::class,
            'transformer' => $this->transformer ? $this->transformer::class : null,
        ];

        $validator = Validator::make($request, [
            'key' => [
                'required',
                'unique:payments,key',
                'min:3',
                'regex:/^[a-zA-Z0-9_]+$/',
            ],
            'name' => 'required|min:3',
            'config' => 'array',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $payment = PaymentModel::create($request);

        return new Payment($payment);
    }
}
