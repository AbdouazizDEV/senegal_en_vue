<?php

namespace App\Presentation\Http\Resources;

use App\Domain\Certification\Models\Certification;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CertificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Certification $certification */
        $certification = $this->resource;

        return [
            'id' => $certification->id,
            'uuid' => $certification->uuid,
            'name' => $certification->name,
            'slug' => $certification->slug,
            'description' => $certification->description,
            'type' => $certification->type,
            'badge_image' => $certification->badge_image,
            'criteria' => $certification->criteria,
            'validity_months' => $certification->validity_months,
            'is_active' => $certification->is_active,
            'created_at' => $certification->created_at->toIso8601String(),
            'updated_at' => $certification->updated_at->toIso8601String(),
        ];
    }
}

