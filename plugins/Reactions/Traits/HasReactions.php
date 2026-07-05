<?php

namespace Plugins\Reactions\Traits;

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

    protected function uniqueReaction(): bool
    {
        $localSetting = $this->getMeta($this->uniqueMetaKey, null);

        if ($localSetting !== null) {
            return (bool) $localSetting;
        }

        return (bool) setting('reading.unique_reaction', true);
    }

    protected function getReactions(): array
    {
        $raw = $this->getMeta($this->reactionMetaKey, []);

        if (!is_array($raw)) {
            return json_decode($raw, true) ?? [];
        }

        return $raw;
    }

    protected function getAllVotes(): array
    {
        $reactions = $this->getReactions();
        $allVotes = [];
        $isUnique = $this->uniqueReaction();

        foreach ($reactions as $votes) {
            if (empty($votes)) continue;

            if ($isUnique) {
                $allVotes[] = end($votes);
            } else {
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
                unset($reactions[$id]);
            } else {
                $reactions[$id][] = $value;
            }
        } else {
            $reactions[$id][] = $value;
        }

        $this->setMeta($this->reactionMetaKey, $reactions);
    }
}
