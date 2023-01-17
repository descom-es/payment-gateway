<?php

namespace Descom\Payment\Tests\Support;

use Descom\Payment\Transformers\Transformer;
use Omnipay\Common\Message\ResponseInterface;

class TransformerCustom implements Transformer
{
    public function apply(array $request): array
    {
        $transactionId = $request['transactionId'];
        $transactionId = sprintf('%05d', $transactionId);
        $request['transactionId'] = preg_replace('/(\d{4})(\d+)/', '${1}Y${2}', $transactionId);

        return $request;
    }

    public function unapply(ResponseInterface $response): array
    {
        if (! method_exists($response, 'getTransactionId')) {
            return [];
        }

        return [
            'transaction_id' => (int)str_replace('Y', '', $response->getTransactionId()),
        ];
    }
}
