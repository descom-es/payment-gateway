<?php

namespace Descom\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaymentModel extends Model
{
    protected $table = 'payments';

    protected $casts = [
        'config' => 'object',
    ];

    protected $fillable = [
        'key',
        'gateway',
        'config',
        'name',
    ];

    public function transitions(): HasMany
    {
        return $this->hasMany(TransitionModel::class, 'payment_id', null, 'payment_transitions');
    }
}
