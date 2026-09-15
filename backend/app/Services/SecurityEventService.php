<?php

namespace App\Services;

use App\Models\SecurityEvent;
use Illuminate\Http\Request;

class SecurityEventService
{
    /**
     * Log a security event.
     *
     * @param string $eventType
     * @param int|null $userId
     * @param string|null $sessionId
     * @param array|null $details
     * @param Request|null $request
     * @return SecurityEvent
     */
    public static function log(string $eventType, ?int $userId = null, ?string $sessionId = null, ?array $details = null, ?Request $request = null): SecurityEvent
    {
        $request = $request ?? request();

        $event = SecurityEvent::create([
            'event_type' => $eventType,
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'details' => $details ? json_encode($details) : null,
            'event_timestamp' => now(),
        ]);

        return $event;
    }
}