<?php

namespace App\Presentation\Http\Resources;

use App\Domain\Payment\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Payment $payment */
        $payment = $this->resource;

        return [
            'id' => $payment->id,
            'uuid' => $payment->uuid,
            'status' => $payment->status->value,
            'status_label' => $payment->status->label(),
            'type' => $payment->type->value,
            'type_label' => $payment->type->label(),
            'amount' => $payment->amount,
            'commission_amount' => $payment->commission_amount,
            'provider_amount' => $payment->provider_amount,
            'currency' => $payment->currency,
            'payment_method' => $payment->payment_method,
            'payment_gateway' => $payment->payment_gateway,
            'transaction_id' => $payment->transaction_id,
            'gateway_reference' => $payment->gateway_reference,
            'gateway_status' => $payment->gateway_status,
            'processed_at' => $payment->processed_at?->toIso8601String(),
            'transferred_at' => $payment->transferred_at?->toIso8601String(),
            'failure_reason' => $payment->failure_reason,
            'refund_reason' => $payment->refund_reason,
            'metadata' => $payment->metadata,
            'booking' => $payment->relationLoaded('booking') 
                ? new BookingResource($payment->booking) 
                : null,
            'traveler' => $payment->relationLoaded('traveler') 
                ? new UserResource($payment->traveler) 
                : null,
            'provider' => $payment->relationLoaded('provider') 
                ? new UserResource($payment->provider) 
                : null,
            'disputes_count' => $payment->relationLoaded('disputes') 
                ? $payment->disputes->count() 
                : null,
            'created_at' => $payment->created_at->toIso8601String(),
            'updated_at' => $payment->updated_at->toIso8601String(),
        ];
    }
}

