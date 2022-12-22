<?php

namespace Descom\Payment\Http\Controllers;

use Descom\Payment\Transaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentRedirectController extends Controller
{
    public function __invoke(Request $request, string $payment_key)
    {
        $transaction = Transaction::find($payment_key);

        $response = $transaction->redirectPurchase($request->all());

        if ($response->isSuccessful() && $transaction->payment->config->return_url) {
            return redirect()->away($transaction->payment->config->return_url);
        }

        if (! $response->isSuccessful() && $transaction->payment->config->cancel_url) {
            return redirect()->away($transaction->payment->config->cancel_url);
        }

        return response()->noContent();
    }
}
