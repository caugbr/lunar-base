<?php

namespace App\Traits;

trait HasMeta
{
    public function initializeHasMeta(): void
    {
        $this->casts['meta'] = 'json';
        $this->attributes['meta'] = $this->attributes['meta'] ?? '{}';
    }

    public function getMeta(string $key, mixed $default = null): mixed
    {
        return data_get($this->meta ?? [], $key, $default);
    }

    public function setMeta(string|array $key, mixed $value = null): static
    {
        $meta = $this->meta ?? [];

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                data_set($meta, $k, $v);
            }
        } else {
            data_set($meta, $key, $value);
        }

        $this->meta = $meta;

        return $this;
    }

    public function removeMeta(string $key): static
    {
        $meta = $this->meta ?? [];
        data_forget($meta, $key);
        $this->meta = $meta;

        return $this;
    }

    public function hasMeta(string $key): bool
    {
        return array_key_exists($key, $this->meta ?? []);
    }

    public function allMeta(): array
    {
        return $this->meta ?? [];
    }

    public function clearMeta(): static
    {
        $this->meta = [];

        return $this;
    }
}
