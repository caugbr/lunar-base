<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PluginCreateCommand extends Command
{
    protected $signature = 'plugin:create {name : The name of the plugin} {description? : An optional description}';
    protected $description = 'Gera a estrutura completa de um plugin (diretórios, assets e manifest)';

    public function handle(): int
    {
        $inputName = $this->argument('name');

        // 1. Gera o slug limpo (ex: "FAQ" -> "faq" | "Prism Highlight" -> "prism-highlight")
        $slugName = Str::slug($inputName);

        // 2. Gera o StudlyCase limpo para pastas e classes PHP (ex: "faq" -> "Faq" | "prism-highlight" -> "PrismHighlight")
        $studlyName = Str::studly($slugName);

        $singularName = Str::singular($studlyName);
        $pluginPath   = base_path("plugins/{$studlyName}");

        if (File::exists($pluginPath)) {
            $this->error("Plugin '{$studlyName}' já existe!");
            return Command::FAILURE;
        }

        $this->info("Iniciando setup para '{$studlyName}'...");

        $hasDatabase   = $this->confirm('Requer banco de dados?', true);
        $hasController = $this->confirm('Requer Controller e Rotas?', true);
        $hasViews      = $this->confirm('Requer Views?', true);

        $description = $this->argument('description') ?? "Plugin {$studlyName} for Lunar Base.";

        // 1. Criação da estrutura de diretórios
        $this->createDirectories($pluginPath, $hasDatabase, $hasController, $hasViews);

        // 2. Geração de arquivos
        $this->generateManifest($pluginPath, $inputName, $description);
        $this->generateServiceProvider($pluginPath, $studlyName, $singularName, $slugName, $hasDatabase, $hasController, $hasViews);

        if ($hasController) {
            $this->generateRoutes($pluginPath, $studlyName, $singularName, $slugName);
            $this->generateController($pluginPath, $studlyName, $singularName, $slugName);
        }

        if ($hasDatabase) {
            $this->generateModel($pluginPath, $studlyName, $singularName);
            $this->generateBlankMigration($pluginPath, $studlyName);
        }

        if ($hasViews) {
            $this->generateViews($pluginPath, $studlyName, $slugName);
        }

        $this->info("--------------------------------------------------");
        $this->info("Plugin '{$studlyName}' criado com sucesso!");
        $this->info("Path: plugins/{$studlyName}");
        $this->info("Namespace de Views: {$slugName}::view-name");
        $this->info("--------------------------------------------------");

        return Command::SUCCESS;
    }

    protected function createDirectories(string $path, bool $db, bool $ctrl, bool $views): void
    {
        $dirs = [$path];
        if ($ctrl) $dirs[] = $path . '/Http/Controllers';
        if ($db) {
            $dirs[] = $path . '/Models';
            $dirs[] = $path . '/database/migrations';
        }
        if ($views) $dirs[] = $path . '/resources/views';
        $dirs[] = $path . '/resources/help-views';

        $dirs[] = $path . '/resources/assets/css';
        $dirs[] = $path . '/resources/assets/js';

        foreach ($dirs as $dir) {
            File::ensureDirectoryExists($dir, 0755, true);
        }
    }

    protected function generateManifest(string $path, string $inputName, string $description): void
    {
        $content = json_encode([
            'name'        => Str::headline($inputName),
            'description' => $description,
            'version'     => '1.0.0',
            'provider'    => "Plugins\\" . Str::studly(Str::slug($inputName)) . "\\" . Str::studly(Str::slug($inputName)) . "ServiceProvider",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        File::put($path . '/plugin.json', $content);
    }

    protected function generateServiceProvider(
        string $path,
        string $name,
        string $singularName,
        string $slugName,
        bool $hasDatabase,
        bool $hasController,
        bool $hasViews
    ): void {
        $registerLines = [];
        if ($hasController) {
            $registerLines[] = "        \$routesFile = __DIR__ . '/routes.php';";
            $registerLines[] = "        if (file_exists(\$routesFile)) {";
            $registerLines[] = "            \$this->loadRoutesFrom(\$routesFile);";
            $registerLines[] = "        }";
        }
        $registerContent = $registerLines ? implode("\n", $registerLines) : "        //";

        $bootLines = [];
        if ($hasDatabase) {
            $bootLines[] = "        \$this->loadMigrationsFrom(__DIR__ . '/database/migrations');";
        }
        if ($hasViews) {
            $bootLines[] = "        \$this->loadViewsFrom(__DIR__ . '/resources/views', '{$slugName}');";
        }
        $bootContent = $bootLines ? implode("\n", $bootLines) : "        //";

        $content = "<?php\n\n" .
            "namespace Plugins\\{$name};\n\n" .
            "use Illuminate\\Support\\ServiceProvider;\n\n" .
            "class {$name}ServiceProvider extends ServiceProvider\n" .
            "{\n" .
            "    public function register(): void\n" .
            "    {\n" .
            $registerContent . "\n" .
            "    }\n\n" .
            "    public function boot(): void\n" .
            "    {\n" .
            $bootContent . "\n" .
            "    }\n" .
            "}\n";

        File::put($path . "/{$name}ServiceProvider.php", $content);
    }

    protected function generateRoutes(string $path, string $name, string $singularName, string $slugName): void
    {
        $content = "<?php\n\n" .
            "use Illuminate\\Support\\Facades\\Route;\n" .
            "use Plugins\\{$name}\\Http\\Controllers\\{$singularName}Controller;\n\n" .
            "Route::middleware(['web', 'auth'])->group(function () {\n" .
            "    // Route::get('/{$slugName}', [{$singularName}Controller::class, 'index'])->name('{$slugName}.index');\n" .
            "});\n";

        File::put($path . '/routes.php', $content);
    }

    protected function generateController(string $path, string $name, string $singularName, string $slugName): void
    {
        $content = "<?php\n\n" .
            "namespace Plugins\\{$name}\\Http\\Controllers;\n\n" .
            "use App\\Http\\Controllers\\Controller;\n" .
            "use Illuminate\\Http\\Request;\n\n" .
            "class {$singularName}Controller extends Controller\n" .
            "{\n" .
            "    public function index()\n" .
            "    {\n" .
            "        return view('{$slugName}::index');\n" .
            "    }\n" .
            "}\n";

        File::put($path . "/Http/Controllers/{$singularName}Controller.php", $content);
    }

    protected function generateModel(string $path, string $name, string $singularName): void
    {
        $tableName = Str::snake(Str::plural($singularName));

        $content = "<?php\n\n" .
            "namespace Plugins\\{$name}\\Models;\n\n" .
            "use Illuminate\\Database\\Eloquent\\Model;\n\n" .
            "class {$singularName} extends Model\n" .
            "{\n" .
            "    protected \$table = '{$tableName}';\n" .
            "    protected \$fillable = [];\n" .
            "}\n";

        File::put($path . "/Models/{$singularName}.php", $content);
    }

    protected function generateBlankMigration(string $path, string $name): void
    {
        $tableName = Str::snake($name);
        $datePrefix = date('Y_m_d_His');

        $content = "<?php\n\n" .
            "use Illuminate\\Database\\Migrations\\Migration;\n" .
            "use Illuminate\\Database\\Schema\\Blueprint;\n" .
            "use Illuminate\\Support\\Facades\\Schema;\n\n" .
            "return new class extends Migration\n" .
            "{\n" .
            "    public function up(): void\n" .
            "    {\n" .
            "        Schema::create('{$tableName}', function (Blueprint \$table) {\n" .
            "            \$table->id();\n" .
            "            \$table->timestamps();\n" .
            "        });\n" .
            "    }\n\n" .
            "    public function down(): void\n" .
            "    {\n" .
            "        Schema::dropIfExists('{$tableName}');\n" .
            "    }\n" .
            "};\n";

        File::put($path . "/database/migrations/{$datePrefix}_create_{$tableName}_table.php", $content);
    }

    protected function generateViews(string $path, string $name, string $slugName): void
    {
        $headlineName = Str::headline($name);
        $content = "<div>\n" .
            "    <h3>Welcome to {$headlineName} Plugin</h3>\n" .
            "</div>\n";

        File::put($path . "/resources/views/index.blade.php", $content);

        $help = '
<div class="plugin-help-content">
    <header>
        <h3>
            <x-lucide-puzzle class="lucid-icon" />
            ' . $headlineName . '
        </h3>
        <p>
            Breve descrição sobre o plugin.
        </p>
    </header>

    <h4>Sobre como o plugin funciona</h4>
    <p>Instruções para usar o plugin</p>
</div>';

        File::put($path . "/resources/help-views/help.blade.php", $help);
    }
}
