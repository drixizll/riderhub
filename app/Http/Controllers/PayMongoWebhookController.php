<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Services\PayMongoCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayMongoWebhookController extends Controller
{
    /**
     * Handles PayMongo webhook events. PayMongo sends events shaped like:
     * { "data": { "id": "evt_...", "type": "event", "attributes": {
     *     "type": "checkout_session.payment.paid", "livemode": false,
     *     "data": { "id": "cs_...", "type": "checkout_session", "attributes": { ... } } } } }
     */
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

        $event = json_decode($payload, true);
        $eventAttributes = is_array($event) ? data_get($event, 'data.attributes') : null;

        if (! is_array($eventAttributes) || data_get($eventAttributes, 'type') !== 'checkout_session.payment.paid') {
            return response()->json(['received' => true]);
        }

        $session = data_get($eventAttributes, 'data');
        $sessionId = data_get($session, 'id');
        $livemode = data_get($eventAttributes, 'livemode');

        if (
            ! is_array($session)
            || ! is_string($sessionId)
            || ! is_string(data_get($session, 'attributes.reference_number'))
            || $livemode !== (bool) config('services.paymongo.livemode')
        ) {
            return response()->json(['message' => 'Invalid paid checkout event.'], 422);
        }

        $payment = Payment::query()
            ->with('booking')
            ->where('paymongo_reference', $sessionId)
            ->first();

        if (! $payment) {
            // Acknowledge so PayMongo does not keep retrying (e.g. sessions from another app on the same account).
            Log::info('PayMongo webhook referenced an unknown checkout session.', ['checkout_session_id' => $sessionId]);

            return response()->json(['received' => true, 'ignored' => true]);
        }

        if (! $checkoutService->confirmPaidSession($payment, $session, $livemode)) {
            return response()->json(['message' => 'Paid checkout details did not match the booking.'], 422);
        }

        return response()->json(['received' => true]);
    }
}
