<?php

namespace Descom\Payment\Tests\Support;

use Illuminate\Database\Eloquent\Model;

class OrderModel extends Model
{
    public function getKey(): mixed
    {
        return 1;
    }
}
