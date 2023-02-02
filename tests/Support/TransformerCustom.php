<?php

namespace Descom\Payment\Tests\Support;

use Descom\Payment\Transformers\Transformer;
use Omnipay\Common\Message\ResponseInterface;

class TransformerCustom implements Transformer
{
    public function apply(array $request): array
    {
        $transactionId = $request['transactionId'];
        $transactionReference = $request['transactionReference'] ?? null;

        if ($transactionReference) {
            $transactionId = sprintf('%05d-%05d', $transactionId, $transactionReference);
        } else {
            $transactionId = sprintf('%05d', $transactionId);
        }

        $request['transactionId'] = preg_replace('/^(\d{4})(\d+)/', '${1}Y${2}', $transactionId);

        return $request;
    }

    public function unapply(ResponseInterface $response): array
    {
        if (! method_exists($response, 'getTransactionId')) {
            return [];
        }

        $data = explode(',', str_replace('Y', '', $response->getTransactionId()));

        $transactionId = (int)$data[0];
        $transactionReference = $data[1] ?? null;

        return [
            'transaction_id' => $transactionId,
            'transaction_reference' => $transactionReference,
        ];
    }
}
