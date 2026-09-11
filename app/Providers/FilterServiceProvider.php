<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class FilterServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('filter', function () {
            return new class {
                protected $filters = [];

                public function add($tag, $callback, $priority = 10) {
                    $this->filters[$tag][$priority][] = $callback;
                    ksort($this->filters[$tag]); // Ordena por prioridade
                }

                // Adiciona suporte a múltiplos argumentos (...$args)
                public function apply($tag, $value, ...$args) {
                    if (isset($this->filters[$tag])) {
                        foreach ($this->filters[$tag] as $callbacks) {
                            foreach ($callbacks as $callback) {
                                $value = $callback($value, ...$args);
                            }
                        }
                    }
                    return $value;
                }
            };
        });
    }
}
