<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function adminPdf(Request $request): View
    {
        $bookings = $this->filteredAdminBookings($request)->get();

        return view('reports.bookings-pdf', [
            'title' => 'Platform Bookings & Revenue Report',
            'subtitle' => 'Misamis Oriental Operations Summary',
            'generatedBy' => Auth::user()->name.' (Administrator)',
            'bookings' => $bookings,
            'showCustomer' => true,
            'showRider' => true,
            'backUrl' => route('admin.bookings.index', $request->query()),
            'summary' => [
                ['label' => 'Total Bookings', 'value' => number_format($bookings->count())],
                ['label' => 'Completed', 'value' => number_format($bookings->where('status', 'completed')->count())],
                ['label' => 'Pending / Active', 'value' => number_format($bookings->whereIn('status', ['pending', 'accepted', 'picked_up'])->count())],
                ['label' => 'Completed Fare Volume', 'value' => '₱'.number_format((float) $bookings->where('status', 'completed')->sum('fare'), 2)],
            ],
        ]);
    }

    public function adminCsv(Request $request): StreamedResponse
    {
        $bookings = $this->filteredAdminBookings($request)->get();
        $filename = 'riderhub-bookings-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($bookings): void {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM so Microsoft Excel displays ₱ and accented characters properly.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Booking #', 'Date', 'Customer', 'Rider', 'Service', 'Pickup', 'Drop-off', 'Payment Method', 'Payment Status', 'Booking Status', 'Fare (PHP)']);

            foreach ($bookings as $booking) {
                fputcsv($handle, [
                    $booking->id,
                    $booking->created_at?->timezone('Asia/Manila')->format('Y-m-d g:i A') ?? '—',
                    $booking->customer?->name ?? 'N/A',
                    $booking->rider?->name ?? 'Unassigned',
                    ucfirst($booking->type),
                    $booking->pickup_address,
                    $booking->dropoff_address,
                    ucfirst($booking->payment_method),
                    ucfirst($booking->payment?->status ?? 'pending'),
                    ucfirst(str_replace('_', ' ', $booking->status)),
                    number_format((float) $booking->fare, 2, '.', ''),
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function customerPdf(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,accepted,picked_up,completed,cancelled'],
            'type' => ['nullable', 'in:ride,delivery'],
        ]);

        $bookings = Auth::user()->bookings()
            ->with(['rider', 'payment'])
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->latest()
            ->get();

        return view('reports.bookings-pdf', [
            'title' => 'My Booking History Report',
            'subtitle' => 'RiderHub Misamis Oriental Customer Statement',
            'generatedBy' => Auth::user()->name,
            'bookings' => $bookings,
            'showCustomer' => false,
            'showRider' => true,
            'backUrl' => route('customer.bookings.index', $request->query()),
            'summary' => [
                ['label' => 'Total Bookings', 'value' => number_format($bookings->count())],
                ['label' => 'Completed Trips', 'value' => number_format($bookings->where('status', 'completed')->count())],
                ['label' => 'Rides / Deliveries', 'value' => $bookings->where('type', 'ride')->count().' / '.$bookings->where('type', 'delivery')->count()],
                ['label' => 'Total Fare Spent', 'value' => '₱'.number_format((float) $bookings->where('status', 'completed')->sum('fare'), 2)],
            ],
        ]);
    }

    public function customerReceipt(Booking $booking): View
    {
        $this->authorize('view', $booking);
        $booking->load(['customer', 'rider', 'payment', 'rating']);

        return view('reports.receipt-pdf', [
            'booking' => $booking,
            'backUrl' => route('customer.bookings.show', $booking),
        ]);
    }

    public function riderPdf(): View
    {
        $bookings = Auth::user()->riderBookings()
            ->with(['customer', 'payment'])
            ->latest()
            ->get();

        return view('reports.bookings-pdf', [
            'title' => 'Rider Trips & Earnings Report',
            'subtitle' => 'RiderHub Misamis Oriental Rider Statement',
            'generatedBy' => Auth::user()->name.' (Rider)',
            'bookings' => $bookings,
            'showCustomer' => true,
            'showRider' => false,
            'backUrl' => route('rider.dashboard'),
            'summary' => [
                ['label' => 'Total Assigned', 'value' => number_format($bookings->count())],
                ['label' => 'Completed Trips', 'value' => number_format($bookings->where('status', 'completed')->count())],
                ['label' => 'Active Trips', 'value' => number_format($bookings->whereIn('status', ['accepted', 'picked_up'])->count())],
                ['label' => 'Total Earnings', 'value' => '₱'.number_format((float) $bookings->where('status', 'completed')->sum('fare'), 2)],
            ],
        ]);
    }

    private function filteredAdminBookings(Request $request)
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:pending,accepted,picked_up,completed,cancelled'],
            'type' => ['nullable', 'in:ride,delivery'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);

        return Booking::with(['customer', 'rider', 'payment'])
            ->when($validated['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($validated['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($validated['search'] ?? null, function ($q, $search): void {
                $q->where(function ($q) use ($search): void {
                    $q->where('pickup_address', 'like', "%{$search}%")
                        ->orWhere('dropoff_address', 'like', "%{$search}%")
                        ->orWhereHas('customer', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest();
    }
}
