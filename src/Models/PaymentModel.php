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
 * @property ?string $transformer
 */
class PaymentModel extends Model
{
    protected $table = 'payments';

    protected $casts = [
        'config' => 'encrypted:object',
    ];

    protected $fillable = [
        'key',
        'gateway',
        'config',
        'name',
        'transformer',
    ];

    /**
     * Get the transactions for the payment.
     *
     * @return HasMany<TransactionModel, PaymentModel>
     */
    public function transactions(): HasMany
    {
        /** @var HasMany<TransactionModel, PaymentModel> */
        return $this->hasMany(TransactionModel::class, 'payment_id');
    }
}
