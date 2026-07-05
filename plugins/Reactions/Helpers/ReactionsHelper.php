<?php

namespace Plugins\Reactions\Helpers;

use Illuminate\Support\Facades\Request;
use Illuminate\Database\Eloquent\Model;

class ReactionsHelper
{
    protected static string $reactionMetaKey = 'reactions';
    protected static string $uniqueMetaKey = 'unique_reactions';

    protected static function visitorHash(): string
    {
        $ip = Request::ip() ?? 'unknown';
        $salt = config('app.key');
        return hash('sha256', $ip . $salt);
    }

    protected static function reactionId(): ?string
    {
        $userId = auth()->id();
        return $userId ? 'u_' . $userId : 'h_' . self::visitorHash();
    }

    public static function uniqueReaction(Model $model): bool
    {
        $localSetting = null;

        if (method_exists($model, 'getMeta')) {
            $localSetting = $model->getMeta(self::$uniqueMetaKey, null);
        }

        if ($localSetting !== null) {
            return (bool) $localSetting;
        }

        return (bool) setting('reading.unique_reaction', true);
    }

    public static function getReactions(Model $model): array
    {
        $raw = [];

        if (method_exists($model, 'getMeta')) {
            $raw = $model->getMeta(self::$reactionMetaKey, []);
        }

        if (!is_array($raw)) {
            return json_decode($raw, true) ?? [];
        }

        return $raw;
    }

    protected static function getAllVotes(Model $model): array
    {
        $reactions = self::getReactions($model);
        $allVotes = [];
        $isUnique = self::uniqueReaction($model);

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

    public static function reactionScore(Model $model): int
    {
        return array_sum(self::getAllVotes($model));
    }

    public static function positiveCount(Model $model): int
    {
        return count(array_filter(self::getAllVotes($model), fn($v) => $v > 0));
    }

    public static function negativeCount(Model $model): int
    {
        return count(array_filter(self::getAllVotes($model), fn($v) => $v < 0));
    }

    public static function userReaction(Model $model): ?int
    {
        if (!self::uniqueReaction($model)) return null;

        $id = self::reactionId();
        $reactions = self::getReactions($model);

        if (isset($reactions[$id]) && !empty($reactions[$id])) {
            return end($reactions[$id]);
        }

        return null;
    }

    public static function react(Model $model, int $value): void
    {
        $reactions = self::getReactions($model);
        $id = self::reactionId();

        if (!isset($reactions[$id])) {
            $reactions[$id] = [];
        }

        if (self::uniqueReaction($model)) {
            $currentReaction = end($reactions[$id]);

            if ($currentReaction === $value) {
                unset($reactions[$id]);
            } else {
                $reactions[$id][] = $value;
            }
        } else {
            $reactions[$id][] = $value;
        }

        if (method_exists($model, 'setMeta')) {
            $model->setMeta(self::$reactionMetaKey, $reactions);
            if ($model->exists) {
                $model->save();
            }
        }
    }
}
