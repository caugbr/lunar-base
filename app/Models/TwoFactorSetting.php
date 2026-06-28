<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;

class TwoFactorSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'secret',
        'confirmed_at',
        'otp_code',
        'otp_expires_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActive(): bool
    {
        return !is_null($this->confirmed_at);
    }

    public function setSecretAttribute(?string $value): void
    {
        $this->attributes['secret'] = $value ? Crypt::encryptString($value) : null;
    }

    public function getSecretAttribute(?string $value): ?string
    {
        return $value ? Crypt::decryptString($value) : null;
    }
}
