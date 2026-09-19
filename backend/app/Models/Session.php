<?php

namespace App\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Session extends Model
{
    use HasFactory;

    protected $table = 'sessions';

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'ip_address',
        'user_agent',
        'last_activity',
        'is_current',
    ];

    protected $casts = [
        'is_current' => 'boolean',
    ];

    protected function lastActivity(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === null ? null : Carbon::createFromTimestamp((int) $value),
            set: fn ($value) => [
                'last_activity' => $value instanceof DateTimeInterface
                    ? $value->getTimestamp()
                    : (int) $value,
            ],
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function securityEvents()
    {
        return $this->hasMany(SecurityEvent::class);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }
}
