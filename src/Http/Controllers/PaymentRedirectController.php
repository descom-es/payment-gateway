<?php

namespace Descom\Payment\Http\Controllers;

use Descom\Payment\Transition;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentRedirectController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $transition = Transition::find((int)$id);

        $response = $transition->redirectPurchase($request->all());

        if ($response->isSuccessful() && $transition->payment->config->return_url) {
            return redirect()->away($transition->payment->config->return_url);
        }

        if (! $response->isSuccessful() && $transition->payment->config->cancel_url) {
            return redirect()->away($transition->payment->config->cancel_url);
        }

        return response()->noContent();
    }
}
