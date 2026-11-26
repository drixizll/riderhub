@props(['booking'])

@php
    $status = $booking->status;
    $isCancelled = $status === 'cancelled';
    $stepIndex = match ($status) {
        'pending' => 0,
        'accepted' => 1,
        'picked_up' => 2,
        'completed' => 3,
        default => 0,
    };
    $progressPercent = match ($stepIndex) {
        0 => 10,
        1 => 42,
        2 => 74,
        3 => 100,
        default => 0,
    };
    $steps = [
        ['label' => 'Booked', 'desc' => 'Request sent'],
        ['label' => 'Assigned', 'desc' => 'Rider en route'],
        ['label' => $booking->type === 'delivery' ? 'In Transit' : 'On the Way', 'desc' => 'Picked up'],
        ['label' => 'Completed', 'desc' => 'Arrived safely'],
    ];
@endphp

@if ($isCancelled)
    <div class="flex items-center gap-3 rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3 text-sm text-rose-800">
        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-xl bg-rose-100 text-rose-600 font-black">✕</span>
        <div>
            <p class="font-extrabold">Booking Cancelled</p>
            <p class="text-xs text-rose-700/90">{{ $booking->cancellation_reason ?: 'This booking was cancelled before completion.' }}</p>
        </div>
    </div>
@else
    <div class="rounded-2xl border border-indigo-100/80 bg-gradient-to-r from-indigo-50/70 via-white to-violet-50/70 px-4 py-4 sm:px-6" aria-label="Live trip progress">
        <div class="relative">
            <!-- Base track -->
            <div class="absolute left-4 right-4 top-4 h-1.5 rounded-full bg-slate-200/90" aria-hidden="true"></div>
            <!-- Animated active track -->
            <div
                class="absolute left-4 top-4 h-1.5 rounded-full bg-gradient-to-r from-indigo-600 via-violet-500 to-emerald-500 transition-all duration-700"
                style="width: calc((100% - 2rem) * {{ $progressPercent / 100 }});"
                aria-hidden="true"
            ></div>

            <ol class="relative grid grid-cols-4 gap-2">
                @foreach ($steps as $idx => $step)
                    @php
                        $done = $idx < $stepIndex || ($status === 'completed' && $idx === 3);
                        $current = $idx === $stepIndex && $status !== 'completed';
                    @endphp
                    <li class="flex flex-col items-center text-center">
                        <span class="relative flex h-8 w-8 items-center justify-center rounded-full text-xs font-extrabold transition-all duration-300 {{ $done ? 'bg-emerald-500 text-white shadow-md shadow-emerald-500/30' : ($current ? 'bg-gradient-to-br from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-600/35 ring-4 ring-indigo-200/80' : 'border-2 border-slate-200 bg-white text-slate-400') }}">
                            @if ($current)
                                <span class="absolute -inset-1 animate-ping rounded-full bg-indigo-400/40" aria-hidden="true"></span>
                            @endif
                            @if ($done)
                                <svg class="relative h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M16.704 5.29a1 1 0 0 1 .006 1.414l-7.25 7.31a1 1 0 0 1-1.42.004l-3.75-3.75a1 1 0 0 1 1.414-1.414l3.04 3.04 6.544-6.598a1 1 0 0 1 1.416-.006Z" clip-rule="evenodd" /></svg>
                            @else
                                <span class="relative">{{ $idx + 1 }}</span>
                            @endif
                        </span>
                        <span class="mt-2 text-[11px] font-extrabold leading-tight {{ $done ? 'text-emerald-700' : ($current ? 'text-indigo-700' : 'text-slate-400') }} sm:text-xs">
                            {{ $step['label'] }}
                        </span>
                        <span class="hidden text-[10px] font-medium text-slate-400 sm:block">{{ $step['desc'] }}</span>
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
@endif
