<?php

namespace Descom\Payment\Builders;

use Descom\Payment\Models\PaymentModel;
use Descom\Payment\Payment;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Omnipay\Common\AbstractGateway;

final class PaymentBuilder
{
    private string $name = '';

    private ?string $keyUrlNotify = null;
    private ?string $urlRedirectClientCompleted = null;
    private ?string $urlRedirectClientFailed = null;

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

    public function keyUrlNotify(string $keyUrlNotify): PaymentBuilder
    {
        $this->keyUrlNotify = $keyUrlNotify;

        return $this;
    }

    public function redirectClientTo(string $urlCompleted, string $urlFailed): PaymentBuilder
    {
        $this->urlRedirectClientCompleted = $urlCompleted;
        $this->urlRedirectClientFailed = $urlFailed;

        return $this;
    }

    public function create(string $key): Payment
    {
        $request = [
            'key' => $key,
            'name' => $this->name ?: $key,
            'config' => $this->config,
            'gateway' => $this->gateway::class,
            'url_redirect_client_completed' => $this->urlRedirectClientCompleted,
            'url_redirect_client_failed' => $this->urlRedirectClientFailed,
            'key_notify_url' => $this->keyUrlNotify,
        ];

        $validator = Validator::make($request, [
            'key' => [
                'required',
                'unique:payments,key',
                'min:3',
                'regex:/^[a-zA-Z0-9]+$/',
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
