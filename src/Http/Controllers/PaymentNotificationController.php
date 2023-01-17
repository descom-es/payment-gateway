<?php

namespace Descom\Payment\Http\Controllers;

use Descom\Payment\Models\TransactionModel;
use Descom\Payment\Payment;
use Descom\Payment\Transaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentNotificationController extends Controller
{
    public function __invoke(Request $request, string $payment_key)
    {
        $merchantId = $request->input('transaction_id');
        $paymentId = Payment::find($payment_key)->paymentModel->id;


        $transactionId = TransactionModel::where('merchant_id', $merchantId)
            ->where('payment_id', $paymentId)
            ->firstOrFail()
            ->id;

        Transaction::find($transactionId)->notifyPurchase($request->all());

        return response()->noContent();
    }
}
