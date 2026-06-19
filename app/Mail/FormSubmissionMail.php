<?php

namespace App\Mail;

use App\Models\Form;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FormSubmissionMail extends Mailable
{
    use Queueable, SerializesModels;

    public Form $form;
    public array $data;

    /**
     * @param Form $form O modelo do formulário (contém título, slug, etc)
     * @param array $data Os dados enviados pelo usuário (já validados)
     */
    public function __construct(Form $form, array $data)
    {
        $this->form = $form;
        $this->data = $data;
    }

    public function envelope(): Envelope
    {
        // Assunto dinâmico baseado no título do formulário
        return new Envelope(
            subject: "Nova resposta: {$this->form->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.form-submission',
        );
    }
}
