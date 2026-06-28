<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TwoFactorCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    // É obrigatório que estas propriedades sejam PUBLIC
    public $code;
    public $purpose;

    public function __construct($code, $purpose = 'login')
    {
        $this->code = $code;
        $this->purpose = $purpose;
    }

    public function envelope(): Envelope
    {
        $subject = ($this->purpose === 'setup')
            ? 'Ativação de 2FA'
            : 'Código de verificação de login';

        return new Envelope(
            subject: $subject
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.two-factor',
            // Opcional: passar os dados explicitamente garante consistência
            with: [
                'code' => $this->code,
                'purpose' => $this->purpose,
            ],
        );
    }
}
