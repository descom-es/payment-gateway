<?php

namespace Descom\Payment\Builders;

use Descom\Payment\Payment;
use Descom\Payment\Transaction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class TransactionBuilder
{
    private ?Model $sourceModel = null;

    public function __construct(private Payment $payment)
    {
    }

    public function model(Model $model): self
    {
        $this->sourceModel = $model;

        return $this;
    }

    public function create(float $amount, string|int $merchantId, array $request = []): Transaction
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

        if ($this->sourceModel) {
            $request['model_type'] = get_class($this->sourceModel);
            $request['model_id'] = $this->sourceModel->getKey();
        }

        $transactionModel = $this->payment->paymentModel->transactions()->create($request);

        return new Transaction($transactionModel);
    }
}
