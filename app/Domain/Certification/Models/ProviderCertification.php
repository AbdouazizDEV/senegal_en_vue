<?php

namespace App\Domain\Certification\Models;

use App\Domain\Certification\Enums\CertificationStatus;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderCertification extends Model
{
    use HasFactory;

    protected $fillable = [
        'provider_id',
        'certification_id',
        'issued_at',
        'expires_at',
        'status',
        'issued_by',
        'notes',
        'certificate_file',
    ];

    protected function casts(): array
    {
        return [
            'issued_at' => 'date',
            'expires_at' => 'date',
            'status' => CertificationStatus::class,
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'provider_id');
    }

    public function certification(): BelongsTo
    {
        return $this->belongsTo(Certification::class);
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', CertificationStatus::ACTIVE);
    }
}

