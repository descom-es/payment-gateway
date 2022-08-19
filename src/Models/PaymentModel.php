<?php

namespace Descom\Payment\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $key The key of the payment.
 * @property string $gateway The gateway of the payment.
 * @property string $name The name of the payment.
 * @property object $config The config of the payment.
 * @property PaymentModel $payment The payment model.
 */
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
        return $this->hasMany(TransitionModel::class, 'payment_id');
    }
}
