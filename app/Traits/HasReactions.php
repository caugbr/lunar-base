<?php

namespace App\Traits;

use Illuminate\Support\Facades\Request;

trait HasReactions
{
    protected string $reactionMetaKey = 'reactions';
    protected string $uniqueMetaKey = 'unique_reactions';

    protected function visitorHash(): string
    {
        $ip = Request::ip() ?? 'unknown';
        $salt = config('app.key');
        return hash('sha256', $ip . $salt);
    }

    protected function reactionId(): ?string
    {
        $userId = auth()->id();
        return $userId ? 'u_' . $userId : 'h_' . $this->visitorHash();
    }

/**
     * Define se a reação é única.
     * Prioridade: Meta do Objeto -> Configuração Global -> Padrão (true)
     */
    protected function uniqueReaction(): bool
    {
        // Aqui buscamos o METADADO real do objeto no banco (Post, Page, etc.)
        $localSetting = $this->getMeta($this->uniqueMetaKey, null);

        // Se o objeto tiver esse metadado preenchido (true ou false), usa ele!
        if ($localSetting !== null) {
            return (bool) $localSetting;
        }

        // Se o metadado não existir no objeto, cai na configuração global do sistema
        return (bool) setting('reading.unique_reaction', true);
    }

    /**
     * Recupera as reações do banco.
     * Estrutura padrão: ['user_id' => [votos...]]
     */
    protected function getReactions(): array
    {
        $raw = $this->getMeta($this->reactionMetaKey, []);

        if (!is_array($raw)) {
            return json_decode($raw, true) ?? [];
        }

        return $raw;
    }

    /**
     * Filtra e aplana os votos baseando-se na configuração atual do sistema.
     */
    protected function getAllVotes(): array
    {
        $reactions = $this->getReactions();
        $allVotes = [];
        $isUnique = $this->uniqueReaction();

        foreach ($reactions as $votes) {
            if (empty($votes)) continue;

            if ($isUnique) {
                // Modo Único: Apenas o último clique do usuário entra na conta
                $allVotes[] = end($votes);
            } else {
                // Modo Ilimitado: Todos os cliques de todo mundo contam
                $allVotes = array_merge($allVotes, $votes);
            }
        }

        return $allVotes;
    }

    public function reactionScore(): int
    {
        return array_sum($this->getAllVotes());
    }

    public function positiveCount(): int
    {
        return count(array_filter($this->getAllVotes(), fn($v) => $v > 0));
    }

    public function negativeCount(): int
    {
        return count(array_filter($this->getAllVotes(), fn($v) => $v < 0));
    }

    public function userReaction(): ?int
    {
        if (!$this->uniqueReaction()) return null;

        $id = $this->reactionId();
        $reactions = $this->getReactions();

        if (isset($reactions[$id]) && !empty($reactions[$id])) {
            return end($reactions[$id]);
        }

        return null;
    }

    public function react(int $value): void
    {
        $reactions = $this->getReactions();
        $id = $this->reactionId();

        if (!isset($reactions[$id])) {
            $reactions[$id] = [];
        }

        if ($this->uniqueReaction()) {
            $currentReaction = end($reactions[$id]);

            if ($currentReaction === $value) {
                // Se clicou no mesmo botão, desfaz a reação (remove o histórico dele)
                unset($reactions[$id]);
            } else {
                // Se mudou o voto (ex: de +1 para -1), adiciona ao histórico
                $reactions[$id][] = $value;
            }
        } else {
            // Modo Ilimitado: Apenas empilha o clique no histórico do usuário
            $reactions[$id][] = $value;
        }

        $this->setMeta($this->reactionMetaKey, $reactions);
    }
}
