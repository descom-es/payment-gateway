<?php

namespace Descom\Payment\Http\Controllers;

use Descom\Payment\Transaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentRedirectController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $transaction = Transaction::find((int)$id);

        $response = $transaction->redirectPurchase($request->all());

        if ($response->isSuccessful() && $transaction->payment->config->return_url) {
            $url = $this->replaceVariables($transaction->payment->config->return_url, $transaction);

            return redirect()->away($url);
        }

        if (! $response->isSuccessful() && $transaction->payment->config->cancel_url) {
            $url = $this->replaceVariables($transaction->payment->config->cancel_url, $transaction);

            return redirect()->away($url);
        }

        return response()->noContent();
    }

    private function replaceVariables(string $url, Transaction $transaction): string
    {
        $url = str_replace('{transactionId}', (string)$transaction->id, $url);
        $url = str_replace('{merchantId}', $transaction->merchant_id, $url);
        $url = str_replace('{paymentId}', $transaction->payment_id, $url);

        return $url;
    }
}
