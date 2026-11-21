<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RiderHubAssistantService
{
    public function __construct(private readonly WeatherService $weather) {}

    /**
     * Produces an accurate, context-aware reply for both RiderHub-specific queries
     * and open-ended general questions (via Gemini / OpenAI if configured, or
     * built-in math, time, and Wikipedia knowledge lookup as a zero-key fallback).
     *
     * @param  list<array{role: string, text: string}>  $history
     * @return array{reply: string, actions: list<array{label: string, url: string}>, suggestions: list<string>, source?: string}
     */
    public function respond(
        string $message,
        ?User $user,
        ?float $lat = null,
        ?float $lng = null,
        array $history = [],
    ): array {
        $text = Str::lower(trim($message));
        $fare = config('riderhub.fare', ['base' => 55.00, 'per_km' => 18.50]);

        // 1. Live RiderHub weather query
        if ($this->matchesAny($text, ['weather', 'ulan', 'rain', 'panahon', 'bagyo', 'temp', 'forecast', 'init ba'])) {
            return $this->weatherReply($user, $lat, $lng);
        }

        // 2. Live RiderHub fare estimate
        if ($this->matchesAny($text, ['fare', 'pamasahe', 'magkano', 'pletihan', 'pasahe', 'riderhub rate'])) {
            return $this->fareReply($text, $fare, $user);
        }

        // 3. Live RiderHub booking / tracking / account status
        if ($this->matchesAny($text, ['my booking', 'booking status', 'status ng booking', 'rider ko', 'track my', 'active booking', 'active trip', 'open request'])) {
            return $this->bookingStatusReply($user);
        }

        // 4. RiderHub payments & cancellation
        if ($this->matchesAny($text, ['paymongo', 'gcash', 'paymaya', 'maya', 'paano magbayad', 'payment option'])) {
            return [
                'reply' => 'RiderHub supports Cash to Rider and secure online checkout via PayMongo (GCash, Maya, Visa, and Mastercard). For online bookings, your request is automatically sent to nearby Tagoloan riders as soon as PayMongo confirms payment.',
                'actions' => $user?->role === 'customer'
                    ? [['label' => 'View my bookings', 'url' => route('customer.bookings.index')]]
                    : [],
                'suggestions' => ['Estimate fare for 5 km', 'Check Tagoloan weather', 'How do I cancel?'],
            ];
        }

        if ($this->matchesAny($text, ['cancel booking', 'how do i cancel', 'paano mag cancel', 'kansel'])) {
            return [
                'reply' => 'You can cancel any pending booking before a rider accepts it. For unpaid online bookings, RiderHub automatically expires the PayMongo checkout link so you will not be charged. Once paid or accepted by a rider, cancellation is locked to protect both sides.',
                'actions' => $user?->role === 'customer'
                    ? [['label' => 'Manage my bookings', 'url' => route('customer.bookings.index')]]
                    : [],
                'suggestions' => ['Check my booking status', 'Payment options'],
            ];
        }

        if ($this->matchesAny($text, ['about riderhub', 'ano ang riderhub', 'what is riderhub'])) {
            return [
                'reply' => 'RiderHub is a local ride-booking and express delivery platform built for Tagoloan, Misamis Oriental commuters, locals, and students. It features Tagoloan map pinning, upfront fares, live GPS rider tracking, real-time weather advisories, and PayMongo GCash/Maya checkout.',
                'actions' => $this->primaryActions($user),
                'suggestions' => ['Estimate fare for 5 km', 'Check Tagoloan weather', 'Who is Jose Rizal?'],
            ];
        }

        // 4b. Login / Register / Forgot Password / Profile / How to book / Tagoloan map (English, Tagalog & Bisaya)
        if ($this->matchesAny($text, ['log in', 'login', 'sign in', 'mag log', 'log out', 'logout', 'switch account'])) {
            if ($user) {
                return [
                    'reply' => "You're already logged in as {$user->name} ({$user->role} account)! To log out or switch accounts, tap your profile avatar at the top-right corner and click Log out.",
                    'actions' => [
                        ['label' => 'Account settings', 'url' => route('profile.edit')],
                        ...$this->primaryActions($user),
                    ],
                    'suggestions' => ['Check my booking status', 'Estimate fare for 5 km', 'Check Tagoloan weather'],
                ];
            }

            return [
                'reply' => 'You can log in on the RiderHub Login page using your registered email and password. Check "Remember me" to save your email for quick sign-in next time!',
                'actions' => [
                    ['label' => 'Go to Log in', 'url' => route('login')],
                    ['label' => 'Create an account', 'url' => route('register')],
                ],
                'suggestions' => ['Forgot password?', 'How to book a ride?', 'About RiderHub'],
            ];
        }

        if ($this->matchesAny($text, ['register', 'sign up', 'signup', 'create account', 'gagawa ng account', 'himo account', 'become a rider', 'mag rider'])) {
            return [
                'reply' => $user
                    ? "You already have an active {$user->role} account ({$user->email}). To register another account (like a Rider or Customer account), log out first from the top-right profile menu, then open Sign up."
                    : 'Creating a RiderHub account is free! Choose Customer (to book rides & deliveries) or Rider (to accept trips in Tagoloan), enter your name, email, optional phone number, and any password (at least 4 characters).',
                'actions' => $user
                    ? [['label' => 'Account settings', 'url' => route('profile.edit')]]
                    : [['label' => 'Create account', 'url' => route('register')]],
                'suggestions' => ['How to book a ride?', 'Payment options'],
            ];
        }

        if ($this->matchesAny($text, ['forgot', 'nakalimutan', 'kalimot', 'reset password', 'recover account', 'retrieve'])) {
            return [
                'reply' => 'Forgot your password or email? Open the Account & Password Recovery page! You can reset using your email OR use the "Find Account by Phone" tab with your registered phone number to get a masked email hint and reset link.',
                'actions' => $user
                    ? [['label' => 'Change password in settings', 'url' => route('profile.edit')]]
                    : [['label' => 'Open Account Recovery', 'url' => route('password.request')]],
                'suggestions' => ['Where to log in?', 'About RiderHub'],
            ];
        }

        if ($this->matchesAny($text, ['how to book', 'paano mag book', 'unsaon pag book', 'book a ride', 'mag book', 'send a package', 'padala', 'paano magpadala'])) {
            return [
                'reply' => 'Booking in Tagoloan is easy: 1) Open "Book a ride", 2) Choose Ride or Send a package, 3) Pin your Pickup and Drop-off on the Tagoloan map (or tap a quick barangay pin like Poblacion, Baluarte, Casinglot, Sta. Ana), 4) Choose Cash or PayMongo (GCash/Maya/Card) and confirm!',
                'actions' => $user?->role === 'customer'
                    ? [['label' => 'Book a ride now', 'url' => route('customer.bookings.create')]]
                    : $this->primaryActions($user),
                'suggestions' => ['Estimate fare for 5 km', 'Check Tagoloan weather', 'Payment options'],
            ];
        }

        if ($this->matchesAny($text, ['profile', 'settings', 'change photo', 'palit picture', 'change password', 'palit password', 'edit account', 'delete account'])) {
            return [
                'reply' => $user
                    ? 'You can update your profile photo, name, email, phone number, or password anytime in Account Settings (also accessible by tapping your avatar at the top-right).'
                    : 'Log in first, then tap your profile avatar at the top-right to open Account Settings.',
                'actions' => $user
                    ? [['label' => 'Open Account Settings', 'url' => route('profile.edit')]]
                    : [['label' => 'Log in', 'url' => route('login')]],
                'suggestions' => ['Check my booking status', 'Check Tagoloan weather'],
            ];
        }

        if ($this->matchesAny($text, ['service area', 'barangay', 'satellite', 'map style', 'saan available', 'asa available'])) {
            return [
                'reply' => 'RiderHub serves all of Misamis Oriental — including Tagoloan, Cagayan de Oro, Villanueva, Jasaan, Opol, El Salvador, Claveria, Balingasag, Initao, Laguindingan, and Gingoog! On the map, you can switch between Street Map, Satellite, and OpenStreetMap views.',
                'actions' => $user?->role === 'customer'
                    ? [['label' => 'Open Misamis Oriental Map', 'url' => route('customer.bookings.create')]]
                    : $this->primaryActions($user),
                'suggestions' => ['Estimate fare for 5 km', 'Check Misamis Oriental weather'],
            ];
        }

        // 5. Try real LLM (Gemini or OpenAI) if an API key is configured in .env
        if ($llmReply = $this->askConfiguredLlm($message, $user, $history)) {
            return [
                'reply' => $llmReply,
                'actions' => [],
                'suggestions' => ['Check Tagoloan weather', 'Estimate fare for 5 km', 'Ask another question'],
                'source' => 'ai',
            ];
        }

        // 6. Direct math evaluation (e.g. "what is 245 * 18", "100 + 55 / 5")
        if ($mathReply = $this->tryMath($message)) {
            return [
                'reply' => $mathReply,
                'actions' => [],
                'suggestions' => ['Estimate fare for 5 km', 'Check Tagoloan weather', 'Tell me about Tagoloan'],
            ];
        }

        // 7. Current date & Philippine time
        if ($this->matchesAny($text, ['what time', 'anong oras', 'unsa oras', 'what date', 'anong petsa', 'date today', 'today\'s date'])) {
            $now = now()->timezone('Asia/Manila');

            return [
                'reply' => 'It is currently '.$now->format('g:i A').' (Philippine Standard Time) on '.$now->format('l, F j, Y').'.',
                'actions' => [],
                'suggestions' => ['Check Tagoloan weather', 'Estimate fare for 5 km'],
            ];
        }

        // 8. Natural time-aware greetings & small talk (Philippine Standard Time)
        if (preg_match('/^(h+i+|h+e+l+o+|h+a+l+o+|h+e+y+|y+o+|s+u+p+|what\'?s\s+up|kumusta|kamusta|musta|uy+|oi+|good\s+(morning|afternoon|evening|night)|maayong\s+(buntag|hapon|gabii))[\s!?.]*$/i', trim($message))) {
            $hour = (int) now()->timezone('Asia/Manila')->format('G');
            $greetingName = $user ? ' '.Str::before($user->name, ' ') : '';
            $timeGreeting = match (true) {
                $hour >= 5 && $hour < 12 => "Hey{$greetingName}, good morning! What's up? How can I assist you today?",
                $hour >= 12 && $hour < 18 => "Hey{$greetingName}, good afternoon! What's up? How can I assist you today?",
                default => "Hey{$greetingName}, good evening! What's up? How can I assist you tonight?",
            };

            return [
                'reply' => $timeGreeting,
                'actions' => $this->primaryActions($user),
                'suggestions' => ['Check Misamis Oriental weather', 'Estimate fare for 5 km', $user ? 'Check my booking status' : 'Where to log in?'],
            ];
        }

        if ($this->matchesAny($text, ['how are you', 'kumusta ka', 'kamusta ka', 'okay ka lang', 'ok ka lang'])) {
            $hour = (int) now()->timezone('Asia/Manila')->format('G');
            $period = $hour >= 18 || $hour < 5 ? 'tonight' : 'today';

            return [
                'reply' => "I'm doing great and ready to help! How's your trip going {$period}? Need a fare estimate, live weather check, or help with a booking?",
                'actions' => $this->primaryActions($user),
                'suggestions' => ['Estimate fare for 5 km', 'Check Misamis Oriental weather', 'How to book a ride?'],
            ];
        }

        if (preg_match('/^(thanks|thank you|ty|salamat|maraming salamat|daghang salamat|tnx)[\s!?.]*$/i', trim($message))) {
            return [
                'reply' => "You're very welcome! Let me know anytime if you need help with rides, deliveries, weather, or anything else. Ride safe!",
                'actions' => [],
                'suggestions' => ['Check Misamis Oriental weather', 'Estimate fare for 5 km'],
            ];
        }

        if (preg_match('/^(bye|goodbye|see ya|see you|paalam|babye)[\s!?.]*$/i', trim($message))) {
            return [
                'reply' => 'Take care and ride safe across Misamis Oriental! Just tap me anytime you need assistance.',
                'actions' => [],
                'suggestions' => ['Check Misamis Oriental weather', 'About RiderHub'],
            ];
        }

        if ($this->matchesAny($text, ['who are you', 'what is your name', 'sino ka', 'kinsa ka', 'what should i call you'])) {
            return [
                'reply' => "I'm RiderHub AI — your smart mobility assistant for Misamis Oriental. I can check live weather, compute trip fares, track your bookings, guide you around the app, and answer general questions!",
                'actions' => $this->primaryActions($user),
                'suggestions' => ['Check Misamis Oriental weather', 'Estimate fare for 5 km', 'How to book a ride?'],
            ];
        }

        if (preg_match('/^(ok|okay|nice|cool|wow|sige|ge|goods|ayos|great|awesome)[\s!?.]*$/i', trim($message))) {
            return [
                'reply' => 'Awesome! Let me know what you want to do next — I’m right here whenever you need me.',
                'actions' => $this->primaryActions($user),
                'suggestions' => ['Estimate fare for 5 km', 'Check Misamis Oriental weather', 'How to book a ride?'],
            ];
        }

        // 9. Explicit general knowledge lookup (Wikipedia REST & Search API — only for real factual questions, never random chat)
        $isKnowledgeQuery = (bool) preg_match('/^(what\s+is|what\s+are|who\s+is|who\s+was|where\s+is|tell\s+me\s+about|explain|define|ano\s+ang|sino\s+si|saan\s+ang|unsa\s+ang|kinsa\s+si|asa\s+ang)\s+.+/i', trim($message));
        if ($isKnowledgeQuery && ($wikiReply = $this->lookupWikipedia($message))) {
            return [
                'reply' => $wikiReply,
                'actions' => [],
                'suggestions' => ['Check Misamis Oriental weather', 'Estimate fare for 5 km', 'About RiderHub'],
                'source' => 'knowledge',
            ];
        }

        // 10. Natural conversational replies for casual/random messages (when no external LLM key is configured)
        $hour = (int) now()->timezone('Asia/Manila')->format('G');
        $period = $hour >= 18 || $hour < 5 ? 'tonight' : 'today';
        $firstName = $user ? Str::before($user->name, ' ') : null;
        $nameSuffix = $firstName ? ", {$firstName}" : '';

        if ($this->matchesAny($text, ['haha', 'hehe', 'lol', 'lmao', 'hihi', 'wkwk'])) {
            return [
                'reply' => "Haha love the energy{$nameSuffix}! 😄 Anything I can help you with {$period} — maybe a ride, fare estimate, or live weather check?",
                'actions' => $this->primaryActions($user),
                'suggestions' => ['Check Misamis Oriental weather', 'Estimate fare for 5 km', 'How to book a ride?'],
            ];
        }

        if ($this->matchesAny($text, ['bored', 'boring', 'walang magawa', 'laay'])) {
            return [
                'reply' => "Feeling bored{$nameSuffix}? Maybe it's time for a quick trip or food run around Misamis Oriental! Want me to check the weather or estimate a fare for you?",
                'actions' => $this->primaryActions($user),
                'suggestions' => ['Check Misamis Oriental weather', 'Estimate fare for 5 km', 'How to book a ride?'],
            ];
        }

        if ($this->matchesAny($text, ['gutom', 'hungry', 'kain', 'food', 'eat', 'kaon'])) {
            return [
                'reply' => "Sounds like it's time to eat{$nameSuffix}! 🍜 You can book a quick RiderHub ride to your favorite spot or use Send a Package for a fast delivery.",
                'actions' => $this->primaryActions($user),
                'suggestions' => ['How to book a ride?', 'Estimate fare for 3 km', 'Check Misamis Oriental weather'],
            ];
        }

        if ($this->matchesAny($text, ['pagod', 'tired', 'antok', 'sleepy', 'kapoy'])) {
            return [
                'reply' => "Take it easy and get some rest{$nameSuffix}! If you need a safe, hassle-free ride home {$period}, RiderHub riders are ready whenever you are.",
                'actions' => $this->primaryActions($user),
                'suggestions' => ['How to book a ride?', 'Check Misamis Oriental weather'],
            ];
        }

        if ($this->matchesAny($text, ['help', 'tulong', 'tabang', 'assist', 'ano kaya mo', 'what can you do'])) {
            return [
                'reply' => "I've got you{$nameSuffix}! I can check live Misamis Oriental weather, estimate ride/delivery fares, check your booking status, guide you through booking or PayMongo payments, solve math, and answer general questions. Just ask me anything!",
                'actions' => $this->primaryActions($user),
                'suggestions' => ['Check Misamis Oriental weather', 'Estimate fare for 5 km', $user ? 'Check my booking status' : 'How to book a ride?'],
            ];
        }

        // 11. Warm, natural fallback (never dumps random Wikipedia articles or errors)
        return [
            'reply' => "Gotcha{$nameSuffix}! I'm here to help {$period} — if you're looking for something specific, try asking me about Misamis Oriental weather, trip fares (like \"Estimate fare for 5 km\"), your booking status, or ask a question like \"Tell me about Tagoloan\". What would you like to do?",
            'actions' => $this->primaryActions($user),
            'suggestions' => ['Check Misamis Oriental weather', 'Estimate fare for 5 km', $user ? 'Check my booking status' : 'How to book a ride?'],
        ];
    }

    private function weatherReply(?User $user, ?float $lat, ?float $lng): array
    {
        $data = $this->weather->current(
            $lat ?? (float) config('riderhub.map.center.lat', 8.5378),
            $lng ?? (float) config('riderhub.map.center.lng', 124.7536),
        );

        if ($data['available']) {
            return [
                'reply' => sprintf(
                    "Live weather in Tagoloan, Misamis Oriental: %s at %.1f°C (feels like %.1f°C, humidity %d%%, wind %.1f km/h, rain chance %d%%). %s",
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
            'reply' => 'Live weather data is temporarily unreachable, so please check local skies in Tagoloan before heading out.',
            'actions' => $this->primaryActions($user),
            'suggestions' => ['Estimate fare for 5 km', 'About RiderHub'],
        ];
    }

    private function fareReply(string $text, array $fare, ?User $user): array
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*(?:km|kilometer|kilometers|kilometro)/i', $text, $matches)) {
            $km = max(0.1, min(200.0, (float) $matches[1]));
            $estimated = round($fare['base'] + ($km * $fare['per_km']), 2);

            return [
                'reply' => sprintf(
                    'For a %.1f km trip in Tagoloan, the estimated RiderHub fare is ₱%s (₱%.2f base fare + ₱%.2f/km). Final fare is calculated automatically from your exact pickup and drop-off pins.',
                    $km,
                    number_format($estimated, 2),
                    $fare['base'],
                    $fare['per_km'],
                ),
                'actions' => $user?->role === 'customer'
                    ? [['label' => 'Book this trip', 'url' => route('customer.bookings.create')]]
                    : ($user ? [] : [['label' => 'Sign up & book', 'url' => route('register')]]),
                'suggestions' => ['Payment options', 'Check Tagoloan weather'],
            ];
        }

        return [
            'reply' => sprintf(
                'RiderHub fares start at a ₱%.2f base fare plus ₱%.2f per kilometer. Try asking "Estimate fare for 5 km" or pin your Tagoloan route on the booking map!',
                $fare['base'],
                $fare['per_km'],
            ),
            'actions' => $user?->role === 'customer'
                ? [['label' => 'Open booking map', 'url' => route('customer.bookings.create')]]
                : [],
            'suggestions' => ['Estimate fare for 3 km', 'Estimate fare for 8 km', 'Payment options'],
        ];
    }

    private function bookingStatusReply(?User $user): array
    {
        if (! $user) {
            return [
                'reply' => 'Log in to check your live booking status and track your rider in real time on our Tagoloan map.',
                'actions' => [
                    ['label' => 'Log in', 'url' => route('login')],
                    ['label' => 'Create account', 'url' => route('register')],
                ],
                'suggestions' => ['Estimate fare for 5 km', 'Check Tagoloan weather'],
            ];
        }

        if ($user->role === 'customer') {
            $latest = $user->bookings()->with(['rider', 'payment'])->latest()->first();

            if (! $latest) {
                return [
                    'reply' => 'You do not have any bookings yet. Ready to book your first ride or delivery in Tagoloan?',
                    'actions' => [['label' => 'Create a booking', 'url' => route('customer.bookings.create')]],
                    'suggestions' => ['Estimate fare for 5 km', 'Check Tagoloan weather'],
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
                'suggestions' => ['Check Tagoloan weather', 'How do I cancel?'],
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
                        'You have an active %s (#%d) for %s — status: %s. Pickup: %s → Drop-off: %s.',
                        $active->type,
                        $active->id,
                        $active->customer->name,
                        str_replace('_', ' ', $active->status),
                        $active->pickup_address,
                        $active->dropoff_address,
                    ),
                    'actions' => [['label' => 'Go to rider dashboard', 'url' => route('rider.dashboard')]],
                    'suggestions' => ['Check Tagoloan weather'],
                ];
            }

            return [
                'reply' => "You don't have an active trip right now, and there are {$openCount} open booking request(s) waiting in Tagoloan.",
                'actions' => [['label' => 'Browse open requests', 'url' => route('rider.bookings.index')]],
                'suggestions' => ['Check Tagoloan weather'],
            ];
        }

        $activeCount = Booking::whereIn('status', ['accepted', 'picked_up'])->count();
        $pendingCount = Booking::where('status', 'pending')->count();
        $completedCount = Booking::where('status', 'completed')->count();

        return [
            'reply' => "Platform snapshot: {$activeCount} active trip(s), {$pendingCount} pending booking(s), and {$completedCount} completed trip(s).",
            'actions' => [
                ['label' => 'Operations dashboard', 'url' => route('admin.dashboard')],
                ['label' => 'Manage bookings', 'url' => route('admin.bookings.index')],
            ],
            'suggestions' => ['Check Tagoloan weather'],
        ];
    }

    /**
     * Calls Gemini, Groq, or OpenAI if an API key is configured in .env.
     *
     * @param  list<array{role: string, text: string}>  $history
     */
    private function askConfiguredLlm(string $message, ?User $user, array $history): ?string
    {
        $systemPrompt = $this->buildSystemPrompt($user);

        // 1. Google Gemini API (supports both AI Studio "AIza..." keys and Vertex AI Express "AQ...." keys)
        $geminiKey = config('services.gemini.api_key');
        if (is_string($geminiKey) && trim($geminiKey) !== '') {
            $cleanKey = trim($geminiKey);
            $isVertexExpressKey = str_starts_with($cleanKey, 'AQ.');
            $configuredModel = (string) config('services.gemini.model', 'gemini-2.5-flash');
            $candidateModels = array_values(array_unique([$configuredModel, 'gemini-2.0-flash', 'gemini-1.5-flash']));

            $contents = [];
            foreach (array_slice($history, -8) as $turn) {
                $contents[] = [
                    'role' => $turn['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $turn['text']]],
                ];
            }
            $contents[] = ['role' => 'user', 'parts' => [['text' => $message]]];

            foreach ($candidateModels as $model) {
                try {
                    $endpoint = $isVertexExpressKey
                        ? "https://aiplatform.googleapis.com/v1/publishers/google/models/{$model}:generateContent?key=".urlencode($cleanKey)
                        : "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=".urlencode($cleanKey);

                    $response = Http::acceptJson()
                        ->timeout(15)
                        ->post($endpoint, [
                            'system_instruction' => ['parts' => [['text' => $systemPrompt]]],
                            'contents' => $contents,
                            'generationConfig' => [
                                'temperature' => 0.7,
                                'maxOutputTokens' => 450,
                            ],
                        ]);

                    $reply = data_get($response->json(), 'candidates.0.content.parts.0.text');
                    if ($response->successful() && is_string($reply) && trim($reply) !== '') {
                        return trim($reply);
                    }
                } catch (\Throwable) {
                    // Try next model or fall through cleanly without throwing errors.
                }
            }
        }

        // 2. Groq API (Ultra-fast & free tier available via console.groq.com)
        $groqKey = config('services.groq.api_key');
        if (is_string($groqKey) && trim($groqKey) !== '') {
            $configuredModel = (string) config('services.groq.model', 'openai/gpt-oss-120b');
            $candidateModels = array_values(array_unique([$configuredModel, 'openai/gpt-oss-120b', 'openai/gpt-oss-20b', 'llama-3.3-70b-versatile']));

            $messages = [['role' => 'system', 'content' => $systemPrompt]];
            foreach (array_slice($history, -8) as $turn) {
                $messages[] = ['role' => $turn['role'], 'content' => $turn['text']];
            }
            $messages[] = ['role' => 'user', 'content' => $message];

            foreach ($candidateModels as $model) {
                try {
                    $response = Http::withToken(trim($groqKey))
                        ->acceptJson()
                        ->timeout(15)
                        ->post('https://api.groq.com/openai/v1/chat/completions', [
                            'model' => $model,
                            'messages' => $messages,
                            'temperature' => 0.7,
                            'max_tokens' => 450,
                        ]);

                    $reply = data_get($response->json(), 'choices.0.message.content');
                    if ($response->successful() && is_string($reply) && trim($reply) !== '') {
                        return trim($reply);
                    }
                } catch (\Throwable) {
                    // Try next model or fall through cleanly.
                }
            }
        }

        // 3. OpenAI API
        $openaiKey = config('services.openai.api_key');
        if (is_string($openaiKey) && trim($openaiKey) !== '') {
            try {
                $model = (string) config('services.openai.model', 'gpt-4o-mini');
                $messages = [['role' => 'system', 'content' => $systemPrompt]];
                foreach (array_slice($history, -8) as $turn) {
                    $messages[] = ['role' => $turn['role'], 'content' => $turn['text']];
                }
                $messages[] = ['role' => 'user', 'content' => $message];

                $response = Http::withToken(trim($openaiKey))
                    ->acceptJson()
                    ->timeout(15)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model' => $model,
                        'messages' => $messages,
                        'temperature' => 0.7,
                        'max_tokens' => 400,
                    ]);

                $reply = data_get($response->json(), 'choices.0.message.content');
                if ($response->successful() && is_string($reply) && trim($reply) !== '') {
                    return trim($reply);
                }
            } catch (\Throwable) {
                // Fall through cleanly.
            }
        }

        return null;
    }

    private function buildSystemPrompt(?User $user): string
    {
        $fare = config('riderhub.fare', ['base' => 55.00, 'per_km' => 18.50]);
        $now = now()->timezone('Asia/Manila')->format('l, F j, Y g:i A');
        $userContext = $user ? "Signed-in user: {$user->name} (role: {$user->role})." : 'User is a guest visitor.';

        return "You are RiderHub AI, a warm, natural, intelligent assistant built into RiderHub — a ride-booking and express delivery platform in Misamis Oriental (including Tagoloan, Cagayan de Oro, Villanueva, Jasaan, Opol, El Salvador, and nearby towns), Philippines. "
            ."Current Philippine time: {$now}. "
            ."Fare formula: ₱{$fare['base']} base + ₱{$fare['per_km']}/km. Supports Cash and PayMongo (GCash, Maya, Card). {$userContext} "
            ."Respond naturally like a real helpful human assistant in whatever language the user uses (English, Tagalog, Taglish, or Bisaya/Cebuano), even for casual or random messages. Keep replies clear, accurate, friendly, and concise (1-4 sentences).";
    }

    private function tryMath(string $message): ?string
    {
        // Extract a safe arithmetic expression containing digits, operators, parentheses, and decimals.
        if (! preg_match('/(?:^|[\s:])(\(?-?\d+(?:\.\d+)?(?:\s*[+\-*\/x×÷]\s*\(?-?\d+(?:\.\d+)?\)?)+)/i', $message, $match)) {
            return null;
        }

        $expr = str_replace(['x', 'X', '×', '÷'], ['*', '*', '*', '/'], trim($match[1]));
        if (! preg_match('/^[0-9+\-*\/().\s]+$/', $expr)) {
            return null;
        }

        try {
            $result = $this->evaluateSimpleExpression($expr);
            if ($result === null || ! is_finite($result)) {
                return null;
            }
            $formatted = fmod($result, 1.0) === 0.0 ? number_format($result, 0) : rtrim(rtrim(number_format($result, 4, '.', ','), '0'), '.');

            return "{$expr} = {$formatted}";
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Safe recursive-descent arithmetic evaluator (no eval()).
     */
    private function evaluateSimpleExpression(string $expr): ?float
    {
        $tokens = [];
        $len = strlen($expr);
        $i = 0;
        while ($i < $len) {
            if (ctype_space($expr[$i])) {
                $i++;
                continue;
            }
            if (ctype_digit($expr[$i]) || $expr[$i] === '.') {
                $num = '';
                while ($i < $len && (ctype_digit($expr[$i]) || $expr[$i] === '.')) {
                    $num .= $expr[$i++];
                }
                $tokens[] = (float) $num;
                continue;
            }
            if (in_array($expr[$i], ['+', '-', '*', '/', '(', ')'], true)) {
                $tokens[] = $expr[$i++];
                continue;
            }

            return null;
        }

        $pos = 0;
        $parseExpr = function () use (&$tokens, &$pos, &$parseTerm): float {
            $left = $parseTerm();
            while (isset($tokens[$pos]) && in_array($tokens[$pos], ['+', '-'], true)) {
                $op = $tokens[$pos++];
                $right = $parseTerm();
                $left = $op === '+' ? $left + $right : $left - $right;
            }

            return $left;
        };
        $parseTerm = function () use (&$tokens, &$pos, &$parseFactor): float {
            $left = $parseFactor();
            while (isset($tokens[$pos]) && in_array($tokens[$pos], ['*', '/'], true)) {
                $op = $tokens[$pos++];
                $right = $parseFactor();
                if ($op === '/' && $right == 0.0) {
                    throw new \DivisionByZeroError();
                }
                $left = $op === '*' ? $left * $right : $left / $right;
            }

            return $left;
        };
        $parseFactor = function () use (&$tokens, &$pos, &$parseExpr, &$parseFactor): float {
            if (! isset($tokens[$pos])) {
                throw new \RuntimeException('Unexpected end');
            }
            if ($tokens[$pos] === '-') {
                $pos++;

                return -$parseFactor();
            }
            if ($tokens[$pos] === '(') {
                $pos++;
                $val = $parseExpr();
                if (($tokens[$pos] ?? null) !== ')') {
                    throw new \RuntimeException('Missing )');
                }
                $pos++;

                return $val;
            }
            if (is_float($tokens[$pos])) {
                return $tokens[$pos++];
            }
            throw new \RuntimeException('Invalid token');
        };

        $val = $parseExpr();

        return $pos === count($tokens) ? $val : null;
    }

    /**
     * Searches Wikipedia for an accurate, concise factual summary (free, no API key needed).
     */
    private function lookupWikipedia(string $message): ?string
    {
        $query = preg_replace(
            '/^(what is|what are|who is|who was|where is|tell me about|explain|define|ano ang|sino si|saan ang|unsa ang|kinsa si|asa ang)\s+/i',
            '',
            trim($message),
        );
        $query = trim((string) $query, " \t\n\r\0\x0B?!.");

        if (strlen($query) < 2) {
            return null;
        }

        try {
            $search = Http::withHeaders(['User-Agent' => 'RiderHubCapstone/1.0 (https://riderhub.online)'])
                ->acceptJson()
                ->timeout(6)
                ->get('https://en.wikipedia.org/w/api.php', [
                    'action' => 'query',
                    'list' => 'search',
                    'srsearch' => $query,
                    'srlimit' => 1,
                    'format' => 'json',
                ]);

            $title = data_get($search->json(), 'query.search.0.title');
            if (! $search->successful() || ! is_string($title) || $title === '') {
                return null;
            }

            // Ensure the Wikipedia title actually matches at least one meaningful word from the query.
            $stopWords = ['the', 'and', 'for', 'with', 'from', 'that', 'this', 'san', 'saan', 'mag', 'ang', 'mga', 'how', 'what', 'who', 'where', 'when', 'why', 'can', 'you', 'ako', 'naman', 'lang', 'ba'];
            $queryWords = array_values(array_filter(
                preg_split('/[^a-z0-9]+/i', Str::lower($query)) ?: [],
                fn (string $w): bool => strlen($w) >= 3 && ! in_array($w, $stopWords, true),
            ));
            $lowerTitle = Str::lower($title);
            $hasMatch = false;
            foreach ($queryWords as $word) {
                if (Str::contains($lowerTitle, $word)) {
                    $hasMatch = true;
                    break;
                }
            }
            if (! $hasMatch) {
                return null;
            }

            $summary = Http::withHeaders(['User-Agent' => 'RiderHubCapstone/1.0 (https://riderhub.online)'])
                ->acceptJson()
                ->timeout(6)
                ->get('https://en.wikipedia.org/api/rest_v1/page/summary/'.rawurlencode(str_replace(' ', '_', $title)));

            $extract = data_get($summary->json(), 'extract');
            if (! $summary->successful() || ! is_string($extract) || trim($extract) === '') {
                return null;
            }

            return Str::limit(trim($extract), 420);
        } catch (ConnectionException) {
            return null;
        }
    }

    /**
     * @param  list<string>  $needles
     */
    private function matchesAny(string $haystack, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (Str::contains($haystack, $needle)) {
                return true;
            }
        }

        return false;
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
