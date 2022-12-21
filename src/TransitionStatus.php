<?php

namespace Descom\Payment;

final class TransitionStatus
{
    public const PENDING = 'pending';
    public const PAID = 'paid';
    public const DENIED = 'denied';
    public const VOIDED = 'voided';
}
