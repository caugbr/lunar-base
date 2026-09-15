<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PhpHookServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('php_hooks', function () {
            return new class {
                protected array $actions = [];
                protected array $filters = [];

                /* =========================================================================
                 * ACTIONS (Eventos de ciclo de vida / Efeitos colaterais - Sem retorno)
                 * ========================================================================= */

                /**
                 * Registra uma nova action.
                 */
                public function addAction(string $tag, callable $callback, int $priority = 10): void
                {
                    $this->actions[$tag][$priority][] = $callback;
                    ksort($this->actions[$tag]);
                }

                /**
                 * Executa todas as actions associadas a uma tag.
                 */
                public function doAction(string $tag, mixed ...$args): void
                {
                    if (!isset($this->actions[$tag])) {
                        return;
                    }

                    foreach ($this->actions[$tag] as $callbacks) {
                        foreach ($callbacks as $callback) {
                            $callback(...$args);
                        }
                    }
                }

                /**
                 * Verifica se uma determinada action possui listeners.
                 */
                public function hasAction(string $tag): bool
                {
                    return !empty($this->actions[$tag]);
                }

                /* =========================================================================
                 * FILTERS (Transformação sequencial de dados - Com retorno obrigatório)
                 * ========================================================================= */

                /**
                 * Registra um novo filtro.
                 */
                public function addFilter(string $tag, callable $callback, int $priority = 10): void
                {
                    $this->filters[$tag][$priority][] = $callback;
                    ksort($this->filters[$tag]);
                }

                /**
                 * Aplica os filtros sobre um valor passado, passando argumentos extras se houver.
                 */
                public function applyFilters(string $tag, mixed $value, mixed ...$args): mixed
                {
                    if (!isset($this->filters[$tag])) {
                        return $value;
                    }

                    foreach ($this->filters[$tag] as $callbacks) {
                        foreach ($callbacks as $callback) {
                            $value = $callback($value, ...$args);
                        }
                    }

                    return $value;
                }

                /**
                 * Verifica se um determinado filtro possui listeners.
                 */
                public function hasFilter(string $tag): bool
                {
                    return !empty($this->filters[$tag]);
                }
            };
        });
    }
}
