<?php

namespace Plugins\Forms\Http\Controllers;

use App\Http\Controllers\Controller;
use Plugins\Forms\Models\Form;
use Plugins\Forms\Models\FormSubmission;
use Illuminate\Http\Request;

class FormSubmissionController extends Controller
{
    /**
     * Lista as respostas de um formulário específico
     */
    public function index(Request $request, Form $form)
    {
        // Busca as respostas ordenadas da mais recente para a mais antiga
        $submissions = $form->submissions()
                            ->orderBy('created_at', 'desc')
                            ->paginate(setting('reading.pagination_max_items'));

        return view('forms::admin.submissions.index', compact('form', 'submissions'));
    }

    /**
     * Mostra o detalhe de uma única resposta
     */
    public function show(Form $form, FormSubmission $submission)
    {
        // Garante que a submissão pertence a este formulário (segurança)
        if ($submission->form_id !== $form->id) {
            abort(404);
        }

        return view('forms::admin.submissions.show', compact('form', 'submission'));
    }

    /**
     * Exclui uma resposta específica
     */
    public function destroy(Form $form, FormSubmission $submission)
    {
        if ($submission->form_id !== $form->id) {
            abort(404);
        }

        $submission->delete();

        return $this->index(request(), $form);
    }
}
