<?php

namespace Descom\Payment\Models;

trait hasTransition
{
    public function transitions(): HasMany
    {
        return $this->morphMany(TransitionModel::class, 'source');
    }
}
