<?php

namespace Descom\Payment\Http\Controllers;

use Descom\Payment\Models\TransactionModel;
use Descom\Payment\Payment;
use Descom\Payment\Transaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Omnipay\Common\Message\ResponseInterface;

class PaymentNotificationController extends Controller
{
    public function __invoke(Request $request, string $payment_key)
    {
        $payment = $this->getPayment($payment_key);

        $response = $payment->responseCompletePurchase($request->all());

        $responseTransformed = $this->unapplyTransformer($payment, $response);

        $merchantId = $responseTransformed['transaction_id'] ?? $response->getTransactionId();
        $paymentId = $payment->paymentModel->id;

        $transactionId = TransactionModel::where('merchant_id', $merchantId)
            ->where('payment_id', $paymentId)
            ->firstOrFail()
            ->id;

        Transaction::find($transactionId)->notifyPurchase($request->all());

        return response()->noContent();
    }

    private function getPayment(string $payment_key): Payment
    {
        return Payment::find($payment_key);
    }

    private function unapplyTransformer(Payment $payment, ResponseInterface $response): array
    {
        $transformer = $payment->transformer ?? null;


        if ($transformer) {
            $transformer = new $transformer();

            return $transformer->unapply($response);
        }

        return [];
    }
}
