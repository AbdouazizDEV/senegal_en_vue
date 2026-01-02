<?php

namespace App\Domain\Experience\Models;

use App\Domain\Experience\Enums\ReportReason;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExperienceReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'experience_id',
        'reporter_id',
        'reason',
        'description',
        'status',
        'reviewed_by',
        'reviewed_at',
        'admin_notes',
    ];

    protected function casts(): array
    {
        return [
            'reason' => ReportReason::class,
            'reviewed_at' => 'datetime',
        ];
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}

