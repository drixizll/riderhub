<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:ride,delivery'],
            'pickup_address' => ['required', 'string', 'max:255'],
            'pickup_lat' => ['required', 'numeric', 'between:-90,90'],
            'pickup_lng' => ['required', 'numeric', 'between:-180,180'],
            'dropoff_address' => ['required', 'string', 'max:255'],
            'dropoff_lat' => ['required', 'numeric', 'between:-90,90'],
            'dropoff_lng' => ['required', 'numeric', 'between:-180,180'],
            'item_description' => ['nullable', 'required_if:type,delivery', 'string', 'max:255'],
            'payment_method' => ['required', 'in:cash,online'],
        ];
    }

    /**
     * Reject bookings whose pickup and drop-off pins are (almost) the same point.
     */
    public function after(): array
    {
        return [
            function (\Illuminate\Validation\Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $lat1 = deg2rad((float) $this->input('pickup_lat'));
                $lat2 = deg2rad((float) $this->input('dropoff_lat'));
                $deltaLat = $lat2 - $lat1;
                $deltaLng = deg2rad((float) $this->input('dropoff_lng') - (float) $this->input('pickup_lng'));
                $a = sin($deltaLat / 2) ** 2 + cos($lat1) * cos($lat2) * sin($deltaLng / 2) ** 2;
                $distanceKm = 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));

                if ($distanceKm < 0.05) {
                    $validator->errors()->add('dropoff_address', 'Pickup and drop-off must be at least 50 meters apart. Adjust the pins on the map.');
                }
            },
        ];
    }
}
