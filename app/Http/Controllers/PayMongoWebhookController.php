<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PayMongoCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    public function __invoke(
        Request $request,
        PayMongoCheckoutService $checkoutService,
    ): JsonResponse {
        if (! config('services.paymongo.webhook_secret')) {
            return response()->json(['message' => 'PayMongo webhook is not configured.'], 503);
        }

        $payload = $request->getContent();

        $signature = $request->header('Paymongo-Signature')
            ?? $request->header('X-Paymongo-Signature');

        if (! $checkoutService->hasValidWebhookSignature($payload, $signature)) {
            Log::warning('PayMongo webhook signature validation failed.', [
                'signature_header_present' => $signature !== null,
                'livemode' => (bool) config('services.paymongo.livemode'),
            ]);

            return response()->json(['message' => 'Invalid webhook signature.'], 401);
        }

        $event = $request->json()->all();

        if (data_get($event, 'data.type') !== 'checkout_session.payment.paid') {
            return response()->json(['received' => true]);
        }

        $session = data_get($event, 'data.data');
        $attributes = data_get($session, 'attributes');
        $sessionId = data_get($session, 'id');

        if (
            ! is_string($sessionId)
            || ! is_string(data_get($attributes, 'reference_number'))
            || data_get($event, 'data.livemode') !== (bool) config('services.paymongo.livemode')
        ) {
            return response()->json(['message' => 'Invalid paid checkout event.'], 422);
        }

        $payment = Payment::query()
            ->with('booking')
            ->where('paymongo_reference', $sessionId)
            ->first();

        if (! $payment) {
            return response()->json(['message' => 'Checkout session was not found.'], 404);
        }

        if (! $checkoutService->confirmPaidSession($payment, $session, (bool) data_get($event, 'data.livemode'))) {
            return response()->json(['message' => 'Paid checkout details did not match the booking.'], 422);
        }

        return response()->json(['received' => true]);
    }
}
