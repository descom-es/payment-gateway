<?php

namespace Descom\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property float $amount
 * @property string $merchant_id
 * @property string $status
 * @property string $gateway_id
 * @property object $gateway_response
 */
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

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    public function isDenied(): bool
    {
        return $this->status === 'denied';
    }
}
