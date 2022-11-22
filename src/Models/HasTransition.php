<?php

namespace Descom\Payment\Models;

trait HasTransition
{
    public function transitions(): HasMany
    {
        return $this->morphMany(TransitionModel::class, 'source');
    }
}
