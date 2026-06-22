<?php

namespace App\Support;

use Illuminate\Support\Facades\RateLimiter;

class TwoFactorRateLimiter
{
    public static function key(int $userId): string
    {
        return 'two-factor-challenge:' . $userId;
    }

    public static function tooManyAttempts(int $userId): bool
    {
        return RateLimiter::tooManyAttempts(
            self::key($userId),
            TwoFactorConfig::maxAttemptsPerMinute()
        );
    }

    public static function hit(int $userId): void
    {
        RateLimiter::hit(self::key($userId), 60);
    }

    public static function remaining(int $userId): int
    {
        return RateLimiter::remaining(
            self::key($userId),
            TwoFactorConfig::maxAttemptsPerMinute()
        );
    }

    public static function clear(int $userId): void
    {
        RateLimiter::clear(self::key($userId));
    }
}
