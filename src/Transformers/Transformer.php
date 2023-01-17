<?php

namespace Descom\Payment\Transformers;

use Omnipay\Common\Message\ResponseInterface;

interface Transformer
{
    public function apply(array $request): array;

    public function unapply(ResponseInterface $response): array;
}
