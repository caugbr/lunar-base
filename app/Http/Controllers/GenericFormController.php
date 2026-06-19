<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormSubmission;
use App\Mail\FormSubmissionMail; // <-- Importando o Mailable
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class GenericFormController extends Controller
{
    /**
     * Exibe o formulário no frontend
     */
    public function show(string $slug)
    {
        $form = Form::active()->where('slug', $slug)->firstOrFail();

        return view('forms.show', compact('form'));
    }

    /**
     * Processa o envio do formulário
     */
    public function submit(Request $request, string $slug)
    {
        // 1. Busca o formulário ativo
        $form = Form::active()->where('slug', $slug)->firstOrFail();

        // 2. Constrói as regras de validação dinamicamente
        $rules = [];
        foreach ($form->fields_schema as $field) {
            $key = $field['key'];

            // Se for um checkbox ou select múltiplo, valida como array
            if (in_array($field['type'], ['checkbox', 'select']) && !empty($field['options'])) {
                // Dica: no HTML, o name será "key[]", então a regra no Laravel é "key.*"
                $validationKey = "{$key}.*";
            } else {
                $validationKey = $key;
            }

            $rules[$validationKey] = $field['rules'] ?? 'nullable';
        }

        // 3. Valida a requisição
        $validatedData = $request->validate($rules);

        // 4. Limpeza dos dados (remove tokens do Laravel)
        $cleanData = collect($validatedData)->except(['_token', '_method'])->toArray();

        // 5. Tratamento futuro de uploads (Future-proof)
        // Quando quiser usar o tipo 'image' ou 'file', descomente e ajuste:
        /*
        foreach ($cleanData as $key => $value) {
            if ($request->hasFile($key)) {
                $cleanData[$key] = $request->file($key)->store('form-attachments', 'public');
            }
        }
        */

        // 6. Salva a submissão no banco de dados
        FormSubmission::create([
            'form_id'    => $form->id,
            'data'       => $cleanData,
            'ip_address' => $request->ip(),
        ]);

        // 7. Envio de E-mail (se configurado)
        if (!empty($form->email_to)) {
            Mail::to($form->email_to)->send(new FormSubmissionMail($form, $cleanData));
        }

        // 8. Redireciona com sucesso
        return back()->with('success', $form->submit_message);
    }
}
