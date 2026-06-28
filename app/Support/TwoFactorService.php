<?php

namespace App\Support;

use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class TwoFactorService
{
    public static function sendOtpEmail(User $user, $purpose = 'login')
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Salva no banco (assumindo que o usuário já tem o registro TwoFactorSetting)
        $user->twoFactorSetting->update([
            'otp_code' => Hash::make($code),
            'otp_expires_at' => now()->addMinutes(10)
        ]);

        // Dispara o novo Mailable
        Mail::to($user->email)
            ->send(new \App\Mail\TwoFactorCodeMail($code, $purpose));
    }
}
