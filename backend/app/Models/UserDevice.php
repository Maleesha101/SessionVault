<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'device_name',
        'device_type',
        'device_identifier',
        'ip_address',
        'user_agent',
        'last_used_at',
        'is_current_device',
        'is_trusted',
    ];

    protected $casts = [
        'last_used_at' => 'datetime',
        'is_current_device' => 'boolean',
        'is_trusted' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
