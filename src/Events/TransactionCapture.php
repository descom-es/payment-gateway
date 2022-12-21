<?php

namespace Descom\Payment\Events;

use Descom\Payment\Models\TransactionModel;

abstract class TransactionCapture
{
    public function __construct(private TransactionModel $model)
    {
    }

    public function id(): int
    {
        return $this->model->id;
    }

    public function merchantId(): string
    {
        return $this->model->merchant_id;
    }

    public function transactionModel(): TransactionModel
    {
        return $this->model;
    }
}
