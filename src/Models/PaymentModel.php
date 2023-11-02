<?php

namespace Descom\Payment\Models;

use Descom\Payment\Enums\PaymentVisibilityEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property string $key The key of the payment.
 * @property string $gateway The gateway of the payment.
 * @property string $name The name of the payment.
 * @property object $config The config of the payment.
 * @property PaymentModel $payment The payment model.
 * @property ?string $transformer
 * @property PaymentVisibilityEnum $visibility
 */
class PaymentModel extends Model
{
    protected $table = 'payments';

    protected $casts = [
        'config' => 'encrypted:object',
        'visibility' => PaymentVisibilityEnum::class,
    ];

    protected $fillable = [
        'key',
        'gateway',
        'config',
        'name',
        'transformer',
        'visibility',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(TransactionModel::class, 'payment_id');
    }
}
