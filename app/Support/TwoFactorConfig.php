<?php

namespace App\Support;

class TwoFactorConfig
{
    /**
     * 2FA está habilitado globalmente no sistema?
     */
    public static function enabled(): bool
    {
        return (bool) setting('auth.2fa_enabled', false);
    }

    /**
     * Janela de tolerância de tempo em segundos para validação do TOTP.
     * Padrão RFC 6238: 30 segundos.
     */
    public static function timeWindow(): int
    {
        return (int) setting('auth.time_window', 30);
    }

    /**
     * Quantos períodos de tempo anteriores/futuros aceitar?
     * 1 = ±1 período (padrão conservador)
     * 2 = ±2 períodos (mais tolerante a desvio de relógio)
     */
    public static function windowPeriods(): int
    {
        return (int) setting('auth.window_periods', 1);
    }

    /**
     * Máximo de tentativas de código por minuto no challenge.
     */
    public static function maxAttemptsPerMinute(): int
    {
        return (int) setting('auth.max_attempts_per_minute', 5);
    }

    /**
     * Tempo de expiração da sessão de login parcial (em minutos).
     * Se o usuário não completar o 2FA nesse tempo, volta ao login.
     */
    public static function partialLoginTimeout(): int
    {
        return (int) setting('auth.partial_login_timeout', 5);
    }

    /**
     * Tamanho do QR code em pixels (para renderização).
     */
    public static function qrCodeSize(): int
    {
        return (int) setting('auth.qr_code_size', 200);
    }

    /**
     * Label do emissor no QR code (nome que aparece no app autenticador).
     */
    public static function issuer(): string
    {
        return (string) setting('auth.issuer', config('app.name', 'Aplicação'));
    }
}
