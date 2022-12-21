<?php

namespace Descom\Payment\Events;

use Descom\Payment\Models\TransitionModel;

abstract class TransitionCapture
{
    public function __construct(private TransitionModel $model)
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

    public function transitionModel(): TransitionModel
    {
        return $this->model;
    }
}
