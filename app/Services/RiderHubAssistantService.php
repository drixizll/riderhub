<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Support\Str;

class RiderHubAssistantService
{
    public function __construct(private readonly WeatherService $weather) {}

    /**
     * Produces a helpful, context-aware reply in English or Taglish along with
     * optional quick-action links and follow-up suggestion chips.
     *
     * @return array{reply: string, actions: list<array{label: string, url: string}>, suggestions: list<string>}
     */
    public function respond(string $message, ?User $user, ?float $lat = null, ?float $lng = null): array
    {
        $text = Str::lower(trim($message));
        $fare = config('riderhub.fare', ['base' => 55.00, 'per_km' => 18.50]);

        // 1. Weather inquiry
        if (Str::contains($text, ['weather', 'ulan', 'rain', 'panahon', 'bagyo', 'init', 'temp', 'forecast'])) {
            $data = $this->weather->current(
                $lat ?? (float) config('riderhub.map.center.lat', 8.5378),
                $lng ?? (float) config('riderhub.map.center.lng', 124.7536),
            );

            if ($data['available']) {
                return [
                    'reply' => sprintf(
                        "Current weather is %s at %.1f°C (feels like %.1f°C, humidity %d%%, wind %.1f km/h, rain chance %d%%). %s",
                        $data['label'],
                        $data['temperature'],
                        $data['feels_like'],
                        $data['humidity'],
                        $data['wind_kmh'],
                        $data['rain_chance'],
                        $data['advisory'],
                    ),
                    'actions' => $this->primaryActions($user),
                    'suggestions' => ['Estimate fare for 5 km', 'Check my booking status', 'Payment options'],
                ];
            }

            return [
                'reply' => 'Live weather data is temporarily unreachable, so please check your local skies before heading out. Riders automatically adjust speed during rain for safety.',
                'actions' => $this->primaryActions($user),
                'suggestions' => ['Estimate fare for 5 km', 'How does live tracking work?'],
            ];
        }

        // 2. Fare calculation (e.g., "how much for 5 km", "magkano 3.5km")
        if (Str::contains($text, ['fare', 'price', 'magkano', 'pamasahe', 'cost', 'rate', 'compute', 'estimate', 'km'])) {
            if (preg_match('/(\d+(?:\.\d+)?)\s*(?:km|kilometer|kilometers|kilometro)/i', $text, $matches)) {
                $km = max(0.1, min(200.0, (float) $matches[1]));
                $estimated = round($fare['base'] + ($km * $fare['per_km']), 2);

                return [
                    'reply' => sprintf(
                        'For a %.1f km trip, the estimated RiderHub fare is ₱%s (₱%.2f base fare + ₱%.2f/km). Final fare is calculated automatically from your exact pickup and drop-off pins.',
                        $km,
                        number_format($estimated, 2),
                        $fare['base'],
                        $fare['per_km'],
                    ),
                    'actions' => $user?->role === 'customer'
                        ? [['label' => 'Book this trip', 'url' => route('customer.bookings.create')]]
                        : ($user ? [] : [['label' => 'Sign up & book', 'url' => route('register')]]),
                    'suggestions' => ['Payment options', 'Check current weather', 'How does live tracking work?'],
                ];
            }

            return [
                'reply' => sprintf(
                    'RiderHub fares start at a ₱%.2f base fare plus ₱%.2f per kilometer. Try asking me "Estimate fare for 5 km" or pin your route on the booking map for an instant quote!',
                    $fare['base'],
                    $fare['per_km'],
                ),
                'actions' => $user?->role === 'customer'
                    ? [['label' => 'Open fare estimator & map', 'url' => route('customer.bookings.create')]]
                    : [],
                'suggestions' => ['Estimate fare for 3 km', 'Estimate fare for 10 km', 'Payment options'],
            ];
        }

        // 3. Booking status / tracking / active trip
        if (Str::contains($text, ['status', 'booking', 'track', 'nasaan', 'where is', 'rider ko', 'trip', 'order', 'active'])) {
            if (! $user) {
                return [
                    'reply' => 'Sign in to check your live booking status and track your rider in real time on our interactive map with ETA and distance updates.',
                    'actions' => [
                        ['label' => 'Log in', 'url' => route('login')],
                        ['label' => 'Create account', 'url' => route('register')],
                    ],
                    'suggestions' => ['Estimate fare for 5 km', 'Payment options', 'About RiderHub'],
                ];
            }

            if ($user->role === 'customer') {
                $latest = $user->bookings()->with(['rider', 'payment'])->latest()->first();

                if (! $latest) {
                    return [
                        'reply' => 'You do not have any bookings yet. Ready to book your first ride or express delivery?',
                        'actions' => [['label' => 'Create a booking', 'url' => route('customer.bookings.create')]],
                        'suggestions' => ['Estimate fare for 5 km', 'Check current weather', 'Payment options'],
                    ];
                }

                $statusText = str_replace('_', ' ', $latest->status);
                $riderNote = $latest->rider ? " Assigned rider: {$latest->rider->name}." : ' Waiting for a nearby rider to accept.';
                $paymentNote = $latest->payment ? " Payment ({$latest->payment->method}): {$latest->payment->status}." : '';

                return [
                    'reply' => sprintf(
                        'Your latest %s (#%d) from %s to %s is currently %s (₱%s).%s%s',
                        $latest->type,
                        $latest->id,
                        $latest->pickup_address,
                        $latest->dropoff_address,
                        $statusText,
                        number_format($latest->fare, 2),
                        $riderNote,
                        $paymentNote,
                    ),
                    'actions' => [
                        ['label' => 'Open booking & live map', 'url' => route('customer.bookings.show', $latest)],
                        ['label' => 'All bookings', 'url' => route('customer.bookings.index')],
                    ],
                    'suggestions' => ['Check current weather', 'How do I cancel?', 'Payment options'],
                ];
            }

            if ($user->role === 'rider') {
                $active = $user->riderBookings()->with('customer')->whereIn('status', ['accepted', 'picked_up'])->latest()->first();
                $openCount = Booking::query()
                    ->whereNull('rider_id')
                    ->where('status', 'pending')
                    ->where(fn ($q) => $q->where('payment_method', 'cash')->orWhereHas('payment', fn ($p) => $p->where('status', 'paid')))
                    ->count();

                if ($active) {
                    return [
                        'reply' => sprintf(
                            'You have an active %s (#%d) for %s — status: %s. Pickup: %s → Drop-off: %s. Remember to keep live location sharing turned on!',
                            $active->type,
                            $active->id,
                            $active->customer->name,
                            str_replace('_', ' ', $active->status),
                            $active->pickup_address,
                            $active->dropoff_address,
                        ),
                        'actions' => [['label' => 'Go to rider dashboard', 'url' => route('rider.dashboard')]],
                        'suggestions' => ['Check current weather', 'How does live location work?'],
                    ];
                }

                return [
                    'reply' => "You don't have an active trip right now, and there are {$openCount} open booking request(s) ready to accept.",
                    'actions' => [['label' => 'Browse open requests', 'url' => route('rider.bookings.index')]],
                    'suggestions' => ['Check current weather', 'Rider tips'],
                ];
            }

            if ($user->isAdmin()) {
                $activeCount = Booking::whereIn('status', ['accepted', 'picked_up'])->count();
                $pendingCount = Booking::where('status', 'pending')->count();
                $completedCount = Booking::where('status', 'completed')->count();

                return [
                    'reply' => "Platform snapshot: {$activeCount} active trip(s), {$pendingCount} pending booking(s), and {$completedCount} completed trip(s) so far.",
                    'actions' => [
                        ['label' => 'Operations dashboard', 'url' => route('admin.dashboard')],
                        ['label' => 'Manage bookings', 'url' => route('admin.bookings.index')],
                    ],
                    'suggestions' => ['Check current weather', 'Payment options'],
                ];
            }
        }

        // 4. Payments (GCash, Maya, Card, Cash, PayMongo)
        if (Str::contains($text, ['pay', 'gcash', 'maya', 'card', 'cash', 'bayad', 'paymongo', 'refund'])) {
            return [
                'reply' => 'RiderHub supports Cash to Rider and secure online checkout via PayMongo (GCash, Maya, Visa, and Mastercard). For online bookings, your request is automatically sent to riders the moment PayMongo confirms payment.',
                'actions' => $user?->role === 'customer'
                    ? [['label' => 'View my bookings', 'url' => route('customer.bookings.index')]]
                    : [],
                'suggestions' => ['Estimate fare for 5 km', 'How do I cancel?', 'Check my booking status'],
            ];
        }

        // 5. Cancellation help
        if (Str::contains($text, ['cancel', 'kansel', 'stop'])) {
            return [
                'reply' => 'You can cancel any pending booking before a rider accepts it. For unpaid online bookings, RiderHub automatically expires the PayMongo checkout link so you will not be charged accidentally. Once paid or accepted by a rider, cancellation is locked to protect both sides.',
                'actions' => $user?->role === 'customer'
                    ? [['label' => 'Manage my bookings', 'url' => route('customer.bookings.index')]]
                    : [],
                'suggestions' => ['Check my booking status', 'Payment options'],
            ];
        }

        // 6. Safety / Delivery / About RiderHub
        if (Str::contains($text, ['about', 'who are you', 'ano ang riderhub', 'safety', 'safe', 'delivery', 'package', 'padala'])) {
            return [
                'reply' => 'RiderHub connects your city with fast rides and reliable express package deliveries. Every active trip includes live GPS rider tracking, real-time weather advisories, transparent upfront fares, verified ratings, and secure PayMongo payments.',
                'actions' => $this->primaryActions($user),
                'suggestions' => ['Estimate fare for 5 km', 'Check current weather', 'Payment options'],
            ];
        }

        // 7. Greetings / Default helpful overview
        $greetingName = $user ? ' '.Str::before($user->name, ' ') : '';

        return [
            'reply' => "Hi{$greetingName}! I'm the RiderHub AI Assistant. I can check your live booking status, compute trip fares (e.g. \"Estimate fare for 5 km\"), show real-time weather advisories, or help with GCash/Maya PayMongo payments. What would you like to do?",
            'actions' => $this->primaryActions($user),
            'suggestions' => [
                'Check current weather',
                'Estimate fare for 5 km',
                $user ? 'Check my booking status' : 'About RiderHub',
                'Payment options',
            ],
        ];
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    private function primaryActions(?User $user): array
    {
        if (! $user) {
            return [
                ['label' => 'Book a ride', 'url' => route('register')],
                ['label' => 'Log in', 'url' => route('login')],
            ];
        }

        return match ($user->role) {
            'customer' => [['label' => 'New booking', 'url' => route('customer.bookings.create')]],
            'rider' => [['label' => 'Open requests', 'url' => route('rider.bookings.index')]],
            'admin' => [['label' => 'Operations overview', 'url' => route('admin.dashboard')]],
            default => [],
        };
    }
}
