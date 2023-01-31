<?php

namespace Descom\Payment\Models;

use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTransaction
{
    public function transactions(): MorphMany
    {
        return $this->morphMany(TransactionModel::class, 'model');
    }
}
