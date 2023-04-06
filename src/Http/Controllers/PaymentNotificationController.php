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

        $merchantId = $responseTransformed['transaction_id'] ?? $this->getTransactionId($response);
        $transactionModelId = $responseTransformed['transaction_model_id'] ?? null;
        $paymentId = $payment->paymentModel->id;

        $transactionModelQuery = TransactionModel::where('merchant_id', $merchantId)->where('payment_id', $paymentId);

        if (!is_null($transactionModelId)) {
            $transactionModelQuery->where('id', $transactionModelId);
        }

        $transactionId = $transactionModelQuery->firstOrFail()->id;

        Transaction::find($transactionId)->notifyPurchase($request->all());

        return response()->noContent();
    }

    private function getTransactionId($response)
    {
        if (! (method_exists($response, 'getTransactionId'))) {
            throw new \Exception("Error CompletePurchased require getTransactionId method", 1);
        }

        return $response->getTransactionId();
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
