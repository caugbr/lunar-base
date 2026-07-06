<?php

namespace Plugins\Avatars\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AvatarController extends Controller
{
    public function update(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // Máx 5MB
        ]);

        $user = auth()->user();
        $file = $request->file('avatar');

        try {
            // Garante que a pasta avatars exista no storage
            if (!Storage::disk('public')->exists('avatars')) {
                Storage::disk('public')->makeDirectory('avatars');
            }

            // Processamento de Imagem (Intervention Image v3)
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);

            // Recorta e centraliza em 150x150 (formato quadrado perfeito)
            $image->cover(150, 150);

            // Transforma em WebP com boa qualidade
            $encoded = $image->toWebp(90);

            // Salva substituindo o arquivo anterior
            Storage::disk('public')->put("avatars/{$user->id}.webp", (string) $encoded);

            return back()->with('success', 'Foto de perfil atualizada com sucesso!');

        } catch (\Exception $e) {
            \Log::error("Erro no upload do avatar: " . $e->getMessage());
            return back()->with('error', 'Falha ao processar a imagem do avatar.');
        }
    }

    public function destroy()
    {
        $user = auth()->user();
        $fileName = "avatars/{$user->id}.webp";

        if (Storage::disk('public')->exists($fileName)) {
            Storage::disk('public')->delete($fileName);
            return back()->with('success', 'Foto de perfil removida.');
        }

        return back()->with('error', 'Nenhuma foto de perfil para remover.');
    }
}
