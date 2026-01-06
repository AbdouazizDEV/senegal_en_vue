<?php

namespace App\Domain\Favorite\Models;

use App\Domain\Experience\Models\Experience;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Favorite extends Model
{
    use HasUuids;

    protected $table = 'favorites';

    protected $fillable = [
        'user_id',
        'experience_id',
        'notify_on_price_drop',
        'notify_on_availability',
        'notify_on_new_reviews',
        'notified_at',
    ];

    protected $casts = [
        'notify_on_price_drop' => 'boolean',
        'notify_on_availability' => 'boolean',
        'notify_on_new_reviews' => 'boolean',
        'notified_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($favorite) {
            if (empty($favorite->uuid)) {
                $favorite->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }
}



