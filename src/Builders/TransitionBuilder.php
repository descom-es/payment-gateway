<?php

namespace Descom\Payment\Builders;

use Descom\Payment\Payment;
use Descom\Payment\Transition;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class TransitionBuilder
{
    public function __construct(private Payment $payment)
    {
    }

    public function create(float $amount, string|int $merchantId, array $request = []): Transition
    {
        $request = [
            'amount' => $amount,
            'merchant_id' => $merchantId,
            'gateway_request' => $request,
        ];

        $validator = Validator::make($request, [
            'amount' => 'required|min:0',
            'merchant_id' => 'required|min:1',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $transitionModel = $this->payment->paymentModel->transitions()->create($request);

        return new Transition($transitionModel);
    }
}
