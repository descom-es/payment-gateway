<?php

namespace Descom\Payment\Events;

use Descom\Payment\Models\TransitionModel;

class TransitionFailed
{
    public function __construct(private TransitionModel $model)
    {
    }

    public function transitionModel(): TransitionModel
    {
        return $this->model;
    }
}
