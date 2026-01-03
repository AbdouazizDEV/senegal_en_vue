<?php

namespace App\Presentation\Http\Resources;

use App\Domain\Booking\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Booking $booking */
        $booking = $this->resource;

        return [
            'id' => $booking->id,
            'uuid' => $booking->uuid,
            'status' => $booking->status->value,
            'status_label' => $booking->status->label(),
            'booking_date' => $booking->booking_date->format('Y-m-d'),
            'booking_time' => $booking->booking_time?->format('H:i'),
            'participants_count' => $booking->participants_count,
            'total_amount' => $booking->total_amount,
            'currency' => $booking->currency,
            'payment_status' => $booking->payment_status->value,
            'payment_status_label' => $booking->payment_status->label(),
            'payment_method' => $booking->payment_method,
            'payment_reference' => $booking->payment_reference,
            'payment_date' => $booking->payment_date?->toIso8601String(),
            'special_requests' => $booking->special_requests,
            'cancellation_reason' => $booking->cancellation_reason,
            'cancelled_at' => $booking->cancelled_at?->toIso8601String(),
            'confirmed_at' => $booking->confirmed_at?->toIso8601String(),
            'completed_at' => $booking->completed_at?->toIso8601String(),
            'metadata' => $booking->metadata,
            'experience' => $booking->relationLoaded('experience') 
                ? new ExperienceResource($booking->experience) 
                : null,
            'traveler' => $booking->relationLoaded('traveler') 
                ? new UserResource($booking->traveler) 
                : null,
            'provider' => $booking->relationLoaded('provider') 
                ? new UserResource($booking->provider) 
                : null,
            'disputes_count' => $booking->relationLoaded('disputes') 
                ? $booking->disputes->count() 
                : null,
            'created_at' => $booking->created_at->toIso8601String(),
            'updated_at' => $booking->updated_at->toIso8601String(),
        ];
    }
}

