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
 * @property ?string $url_redirect_client_completed
 * @property ?string $url_redirect_client_failed
 * @property ?string $key_url_notify
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
        'key_notify_url',
        'url_redirect_client_completed',
        'url_redirect_client_failed',
    ];

    public function transitions(): HasMany
    {
        return $this->hasMany(TransitionModel::class, 'payment_id');
    }
}
