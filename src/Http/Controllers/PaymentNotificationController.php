<?php

namespace Descom\Payment\Http\Controllers;

use Descom\Payment\Transaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentNotificationController extends Controller
{
    public function __invoke(Request $request, string $payment_key)
    {
        $transactionId = (int)$request->input('transaction_id');

        Transaction::find($transactionId)->notifyPurchase($request->all());

        return response()->noContent();
    }
}
