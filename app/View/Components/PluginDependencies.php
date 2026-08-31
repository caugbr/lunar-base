<?php

namespace App\View\Components;

use Illuminate\Support\Facades\File;
use Illuminate\View\Component;

class PluginDependencies extends Component
{
    public string $plugin;
    public array $composerDeps = [];
    public ?string $phpVersion = null;

    public function __construct(string $plugin)
    {
        $this->plugin = $plugin;
        $this->loadRequirements();
    }

    protected function loadRequirements(): void
    {
        $jsonPath = base_path("plugins/{$this->plugin}/plugin.json");

        if (File::exists($jsonPath)) {
            $data = json_decode(File::get($jsonPath), true);
            $requirements = $data['requirements'] ?? [];

            $this->composerDeps = $requirements['composer'] ?? [];
            $this->phpVersion = $requirements['php'] ?? null;
        }
    }

    public function render()
    {
        return view('components.plugin-dependencies');
    }
}
