<?php

namespace Descom\Payment\Http\Controllers;

use Descom\Payment\Transaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentReturnController extends Controller
{
    public function __invoke(Request $request, string $payment_key, int $transaction)
    {
        $tx = Transaction::find($transaction);

        if ($request->boolean('cancel')) {
            return $this->redirectTo($tx->payment->config->cancel_url ?? null, $tx);
        }

        try {
            $tx->notifyPurchase($request->all());
        } catch (\Throwable $e) {
            // The capture may have already succeeded and only a post-payment listener
            // (invoice creation, paid notification…) failed. Report it instead of
            // hiding it, and let the persisted transaction status decide the outcome.
            report($e);
        }

        $tx = Transaction::find($transaction);
        $config = $tx->payment->config;

        $target = $tx->isSuccessful()
            ? ($config->return_url ?? null)
            : ($config->cancel_url ?? null);

        return $this->redirectTo($target, $tx);
    }

    private function redirectTo(?string $url, Transaction $transaction)
    {
        return redirect()->away($this->replaceVariables($url, $transaction));
    }

    private function replaceVariables(?string $url, Transaction $transaction): string
    {
        if (! $url) {
            return url('/');
        }

        $url = str_replace('{transactionId}', (string) $transaction->id, $url);
        $url = str_replace('{merchantId}', (string) $transaction->merchant_id, $url);
        $url = str_replace('{paymentId}', (string) $transaction->payment_id, $url);

        return $url;
    }
}
