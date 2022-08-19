<?php

namespace Descom\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransitionModel extends Model
{
    protected $table = 'payment_transitions';

    protected $casts = [
        'amount' => 'double',
        'gateway_request' => 'array',
        'gateway_response' => 'object',
    ];

    protected $fillable = [
        'amount',
        'merchant_id',
        'gateway_request',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PaymentModel::class, 'payment_id', null, 'payments');
    }
}
