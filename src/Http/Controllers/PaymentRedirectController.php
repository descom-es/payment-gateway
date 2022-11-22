<?php

namespace Descom\Payment\Http\Controllers;

use Descom\Payment\Transition;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PaymentRedirectController extends Controller
{
    public function __invoke(Request $request, int $id)
    {
        $transition = Transition::find($id);

        $response = $transition->redirectPurchase($request->all());

        if ($response->isSuccessful() && $transition->payment->url_redirect_client_completed) {
            return redirect()->away($transition->payment->url_redirect_client_completed);
        }

        if (! $response->isSuccessful() && $transition->payment->url_redirect_client_failed) {
            return redirect()->away($transition->payment->url_redirect_client_failed);
        }

        return response()->noContent();
    }
}
