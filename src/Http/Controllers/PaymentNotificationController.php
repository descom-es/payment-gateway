<?php

namespace Descom\Payment\Http\Controllers;

use Descom\Payment\Transition;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentNotificationController extends Controller
{
    public function __invoke(Request $request, string $payment_key)
    {
        $transactionId = (int)$request->input('transaction_id');

        logger()->info('transition notification', $request->all());

        Transition::find($transactionId)->notifyPurchase($request->all());

        return response()->noContent();
    }
}
