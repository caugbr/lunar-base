<?php

namespace Plugins\Reactions\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Plugins\Reactions\Helpers\ReactionsHelper;

class ReactionController extends Controller
{
    protected array $models = [
        'post' => \App\Models\Post::class,
        'page' => \App\Models\Page::class,
        'media' => \App\Models\Media::class,
    ];

    public function store(Request $request, string $type, int $id, string $value)
    {
        $modelClass = $this->models[$type] ?? null;

        if (!$modelClass || !class_exists($modelClass)) {
            return response()->json(['error' => 'Tipo inválido'], 400);
        }

        $item = $modelClass::findOrFail($id);

        $intValue = match($value) {
            'plus' => 1,
            'minus' => -1,
            default => 1,
        };

        if ($intValue === -1 && !setting('reading.post_negative_reaction', false)) {
            return response()->json(['error' => 'Reação negativa desabilitada'], 403);
        }

        // Executa a reação de forma estática sem precisar da trait no model
        ReactionsHelper::react($item, $intValue);

        return response()->json([
            'positive' => ReactionsHelper::positiveCount($item),
            'negative' => ReactionsHelper::negativeCount($item),
            'total' => ReactionsHelper::reactionScore($item),
            'user_reaction' => ReactionsHelper::userReaction($item),
        ]);
    }
}
