<?php

namespace Descom\Payment\Models;

trait HasTransaction
{
    public function transactions(): HasMany
    {
        return $this->morphMany(TransactionModel::class, 'model');
    }
}
