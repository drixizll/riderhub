<?php

use App\Models\Payment;
use App\Services\PayMongoCheckoutService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('paymongo:sync', function (PayMongoCheckoutService $checkoutService) {
    if (! $checkoutService->isConfigured()) {
        $this->error('PAYMONGO_SECRET_KEY is not set.');

        return 1;
    }

    $payments = Payment::query()
        ->with('booking.customer')
        ->where('method', 'online')
        ->where('status', 'pending')
        ->whereNotNull('paymongo_reference')
        ->get();

    $confirmed = 0;

    foreach ($payments as $payment) {
        $booking = $payment->booking;
        $booking->setRelation('payment', $payment);

        if ($checkoutService->retrieveAndConfirmPaidSession($booking)) {
            $confirmed++;
            $this->info("Payment #{$payment->id} (booking #{$booking->id}) confirmed as paid.");
        } else {
            $this->line("Payment #{$payment->id} (booking #{$booking->id}) is still unpaid.");
        }
    }

    $this->comment("Checked {$payments->count()} pending online payment(s); {$confirmed} confirmed.");

    return 0;
})->purpose('Reconcile pending online payments with PayMongo checkout sessions');

// Safety net for missed webhooks (e.g. local development where PayMongo cannot reach the app).
Schedule::command('paymongo:sync')->everyFiveMinutes()->withoutOverlapping();
