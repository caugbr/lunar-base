<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ContentExportService;
use App\Services\ContentImportService;
use App\Support\PublicationTypes;
use Illuminate\Http\Request;
// use Illuminate\Support\Facades\File;

class ContentTransferController extends Controller
{
    /**
     * Exibe a página de Importação / Exportação
     */
    public function index()
    {
        $types = PublicationTypes::labels();
        return view('admin.tools.content-transfer', compact('types'));
    }

    /**
     * Gera e baixa o arquivo JSON de exportação
     */
    public function export(Request $request, ContentExportService $exporter)
    {
        $selected = $request->input('export_types', []);

        try {
            $filePath = $exporter->generateExport($selected);

            log_admin("Exportação de conteúdo realizada: " . implode(', ', $selected), "system");

            return response()->download($filePath)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao exportar: ' . $e->getMessage());
        }
    }

    /**
     * Processa o upload e importação do arquivo JSON
     */
    public function import(Request $request, ContentImportService $importer)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:json,txt',
            'strategy'    => 'required|in:skip,overwrite,draft'
        ]);

        try {
            $file = $request->file('import_file');
            $strategy = $request->input('strategy');

            $stats = $importer->import($file->getRealPath(), $strategy);

            log_admin("Importação de conteúdo realizada via JSON", "system");

            // Monta mensagem de sucesso detalhada
            $msg = "Importação concluída! Taxonomias: {$stats['taxonomies']}, Termos: {$stats['terms']}. ";
            foreach ($stats['items'] as $type => $data) {
                $msg .= ucfirst($type) . ": ({$data['created']} criados, {$data['updated']} atualizados). ";
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Erro ao importar conteúdo: ' . $e->getMessage());
        }
    }
}
