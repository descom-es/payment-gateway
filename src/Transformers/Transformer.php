<?php

namespace Descom\Payment\Transformers;

interface Transformer
{
    public function apply(array $request): array;

    public function unapply(array $response): array;
}
