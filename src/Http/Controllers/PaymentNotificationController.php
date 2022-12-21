<?php

namespace Descom\Payment\Http\Controllers;

use Descom\Payment\Transition;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentNotificationController extends Controller
{
    public function __invoke(Request $request, string $payment_key)
    {
        Transition::find($request->input('transition_id'))->notifyPurchase($request->all());

        return response()->noContent();
    }
}
