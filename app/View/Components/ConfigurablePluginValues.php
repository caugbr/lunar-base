<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ConfigurablePluginValues extends Component
{
    public string $plugin;
    public array $config = [];

    public function __construct(string $plugin)
    {
        $this->plugin = $plugin;
        $manifestPath = base_path("plugins/{$plugin}/plugin.json");

        if (file_exists($manifestPath)) {
            $manifest = json_decode(file_get_contents($manifestPath), true) ?? [];
            $this->config = $manifest['configurable'] ?? [];
        }
    }

    public function render()
    {
        return view('components.configurable-plugin-values');
    }
}
