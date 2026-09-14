<?php

namespace App\Traits;

use App\Models\TwoFactorSetting;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasTwoFactor
{
    public function twoFactorSetting(): HasOne
    {
        return $this->hasOne(TwoFactorSetting::class);
    }

    public function hasTwoFactorEnabled(): bool
    {
        $setting = $this->twoFactorSetting()->first();
        return $setting && $setting->isActive();
    }
}
