<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;

class MailConfigServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (app()->runningInConsole() && !app()->runningUnitTests()) {
            return;
        }

        $this->applyMailSettings();
    }

    private function applyMailSettings(): void
    {
        $settings = [
            'mail.mailers.smtp.host'       => 'mail.mail_host',
            'mail.mailers.smtp.port'       => 'mail.mail_port',
            'mail.mailers.smtp.encryption' => 'mail.mail_encryption',
            'mail.mailers.smtp.username'   => 'mail.mail_username',
            'mail.mailers.smtp.password'   => 'mail.mail_password',
            'mail.from.address'            => 'mail.mail_from_address',
            'mail.from.name'               => 'mail.mail_from_name',
        ];

        foreach ($settings as $configKey => $settingKey) {
            $value = setting($settingKey);
            if ($value !== null && $value !== '') {
                Config::set($configKey, $value);
            }
        }

        app()->forgetInstance('mail.manager');
    }
}
