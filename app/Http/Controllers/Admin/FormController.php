<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function index(Request $request)
    {
        $query = Form::query();

        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }
        if ($request->has('is_active') && $request->is_active !== '') {
            $query->where('is_active', $request->is_active);
        }

        // 🔥 O PULO DO GATO: Traz a contagem de respostas de uma vez só
        $forms = $query->withCount('submissions')
                    ->orderBy('created_at', 'desc')
                    ->paginate(15);

        return view('admin.forms.index', compact('forms'));
    }

    public function create()
    {
        return view('admin.forms.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'slug' => 'required|string|unique:forms,slug|alpha_dash',
            'email_to' => 'nullable|email',
            'is_active' => 'boolean',
            'fields_schema' => 'required|string', // Vem como string JSON do Alpine
            'submit_message' => 'nullable|string|max:255',
            'submit_button_label' => 'nullable|string|max:50',
        ]);

        // Decodifica o JSON e normaliza as opções
        $schema = json_decode($validated['fields_schema'], true) ?? [];
        $validated['fields_schema'] = $this->normalizeSchema($schema);
        $validated['is_active'] = $request->boolean('is_active');

        Form::create($validated);

        return redirect()->route('admin.forms.index')->with('success', 'Formulário criado com sucesso!');
    }

    public function edit(Form $form)
    {
        return view('admin.forms.edit', compact('form'));
    }

    public function update(Request $request, Form $form)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'slug' => 'required|string|alpha_dash|unique:forms,slug,' . $form->id,
            'email_to' => 'nullable|email',
            'is_active' => 'boolean',
            'fields_schema' => 'required|string',
            'submit_message' => 'nullable|string|max:255',
            'submit_button_label' => 'nullable|string|max:50',
        ]);

        $schema = json_decode($validated['fields_schema'], true) ?? [];
        $validated['fields_schema'] = $this->normalizeSchema($schema);
        $validated['is_active'] = $request->boolean('is_active');

        $form->update($validated);

        return redirect()->route('admin.forms.index')->with('success', 'Formulário atualizado!');
    }

    public function show(Form $form)
    {
        return view('admin.forms.show', compact('form'));
    }

    public function destroy(Form $form)
    {
        $form->delete();
        return redirect()->route('admin.forms.index')->with('success', 'Formulário excluído.');
    }

    /**
     * Converte o array de opções de volta para o formato objeto (chave => valor)
     * e remove campos vazios.
     */
    private function normalizeSchema(array $schema): array
    {
        return collect($schema)->map(function ($field) {
            // Se tiver optionsText (do textarea), converte para objeto
            if (isset($field['optionsText']) && in_array($field['type'], ['select', 'radio', 'checkbox'])) {
                $options = [];
                $lines = explode("\n", $field['optionsText']);
                foreach ($lines as $line) {
                    $parts = explode('|', $line);
                    $val = trim($parts[0]);
                    $label = isset($parts[1]) ? trim($parts[1]) : $val;
                    if ($val) $options[$val] = $label;
                }
                $field['options'] = $options;
            }

            // Limpa campos vazios para não poluir o JSON
            unset($field['optionsText']);
            return array_filter($field, fn($v) => $v !== null && $v !== '');
        })->values()->toArray();
    }
}
