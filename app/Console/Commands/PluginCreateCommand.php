<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PluginCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     * We made description an optional second argument.
     */
    protected $signature = 'plugin:create {name : The name of the plugin} {description? : An optional description for the plugin}';

    /**
     * The console command description.
     */
    protected $description = 'Interactive generator to scaffold a tailored dynamic plugin';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $inputName = $this->argument('name');

        // Formata para PascalCase (ex: "Meu Plugin" -> "MeuPlugin")
        $studlyName = Str::studly($inputName);
        $kebabName = Str::kebab($studlyName);

        $pluginPath = base_path("plugins/{$studlyName}");

        if (File::exists($pluginPath)) {
            $this->error("Plugin '{$studlyName}' already exists!");
            return Command::FAILURE;
        }

        // --- PERGUNTAS INTERATIVAS NO TERMINAL ---
        $this->info("Setting up configuration for '{$studlyName}'...");

        $hasDatabase = $this->confirm('Will this plugin require database tables and an Eloquent Model?', true);
        $hasController = $this->confirm('Will this plugin require a web Controller and Routes?', true);
        $hasViews = $this->confirm('Will this plugin require frontend Blade views?', true);

        // Define a descrição do plugin (pega do argumento ou usa um valor padrão)
        $description = $this->argument('description') ?? "A custom {$studlyName} plugin for Lunar Base CMS.";

        $this->info("\nGenerating directories and files...");

        // 1. Criação Dinâmica de Diretórios
        $directories = [$pluginPath]; // A pasta base sempre existe

        if ($hasController) {
            $directories[] = $pluginPath . '/Http/Controllers';
        }
        if ($hasDatabase) {
            $directories[] = $pluginPath . '/Models';
            $directories[] = $pluginPath . '/database/migrations';
        }
        if ($hasViews) {
            $directories[] = $pluginPath . '/resources/views';
        }

        foreach ($directories as $directory) {
            File::ensureDirectoryExists($directory, 0755, true);
        }

        // 2. Geração Condicional de Arquivos
        $this->generateManifest($pluginPath, $studlyName, $description);
        $this->generateServiceProvider($pluginPath, $studlyName, $kebabName, $hasDatabase, $hasController, $hasViews);

        if ($hasController) {
            $this->generateRoutes($pluginPath, $studlyName, $kebabName);
            $this->generateController($pluginPath, $studlyName, $kebabName);
        }

        if ($hasDatabase) {
            $this->generateModel($pluginPath, $studlyName);
            $this->generateBlankMigration($pluginPath, $studlyName);
        }

        if ($hasViews) {
            $this->generateViews($pluginPath, $studlyName, $kebabName);
        }

        $this->info("--------------------------------------------------");
        $this->info("Plugin '{$studlyName}' created successfully!");
        $this->warn("Path: plugins/{$studlyName}");
        $this->info("--------------------------------------------------");

        return Command::SUCCESS;
    }

    protected function generateManifest(string $path, string $name, string $description): void
    {
        $content = json_encode([
            'name' => Str::headline($name),
            'description' => $description,
            'version' => '1.0.0',
            'provider' => "Plugins\\{$name}\\{$name}ServiceProvider",
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        File::put($path . '/plugin.json', $content);
    }

    protected function generateServiceProvider(
        string $path,
        string $name,
        string $kebabName,
        bool $hasDatabase,
        bool $hasController,
        bool $hasViews
    ): void {
        // Monta o método boot() dinamicamente com base nas respostas do usuário
        $bootLines = [];
        if ($hasDatabase) {
            $bootLines[] = "        \$this->loadMigrationsFrom(__DIR__ . '/database/migrations');";
        }
        if ($hasController) {
            $bootLines[] = "        \$this->loadRoutesFrom(__DIR__ . '/routes.php');";
        }
        if ($hasViews) {
            $bootLines[] = "        \$this->loadViewsFrom(__DIR__ . '/resources/views', '{$kebabName}');";
        }

        $bootContent = implode("\n", $bootLines);

        $content = "<?php\n\n" .
            "namespace Plugins\\{$name};\n\n" .
            "use Illuminate\Support\ServiceProvider;\n\n" .
            "class {$name}ServiceProvider extends ServiceProvider\n" .
            "{\n" .
            "    public function register(): void\n" .
            "    {\n" .
            "        //\n" .
            "    }\n\n" .
            "    public function boot(): void\n" .
            "    {\n" .
            ($bootContent ? $bootContent . "\n" : "") .
            "    }\n" .
            "}\n";

        File::put($path . "/{$name}ServiceProvider.php", $content);
    }

    protected function generateRoutes(string $path, string $name, string $kebabName): void
    {
        $content = "<?php\n\n" .
            "use Illuminate\Support\Facades\Route;\n" .
            "use Plugins\\{$name}\\Http\\Controllers\\{$name}Controller;\n\n" .
            "Route::middleware(['web'])->group(function () {\n" .
            "    // Route::get('/{$kebabName}', [{$name}Controller::class, 'index'])->name('{$kebabName}.index');\n" .
            "});\n";

        File::put($path . '/routes.php', $content);
    }

    protected function generateController(string $path, string $name, string $kebabName): void
    {
        $content = "<?php\n\n" .
            "namespace Plugins\\{$name}\\Http\\Controllers;\n\n" .
            "use App\Http\Controllers\Controller;\n" .
            "use Illuminate\Http\Request;\n\n" .
            "class {$name}Controller extends Controller\n" .
            "{\n" .
            "    public function index()\n" .
            "    {\n" .
            "        return view('{$kebabName}::index');\n" .
            "    }\n" .
            "}\n";

        File::put($path . "/Http/Controllers/{$name}Controller.php", $content);
    }

    protected function generateModel(string $path, string $name): void
    {
        $tableName = Str::snake(Str::plural($name));
        $content = "<?php\n\n" .
            "namespace Plugins\\{$name}\\Models;\n\n" .
            "use Illuminate\Database\Eloquent\Model;\n\n" .
            "class {$name} extends Model\n" .
            "{\n" .
            "    protected \$table = '{$tableName}';\n\n" .
            "    protected \$fillable = [];\n" .
            "}\n";

        File::put($path . "/Models/{$name}.php", $content);
    }

    protected function generateBlankMigration(string $path, string $name): void
    {
        $tableName = Str::snake(Str::plural($name));
        $datePrefix = date('Y_m_d_His');

        $content = "<?php\n\n" .
            "use Illuminate\Database\Migrations\Migration;\n" .
            "use Illuminate\Database\Schema\Blueprint;\n" .
            "use Illuminate\Support\Facades\Schema;\n\n" .
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

    protected function generateViews(string $path, string $name, string $kebabName): void
    {
        $headlineName = Str::headline($name);
        $content = "<div>\n" .
            "    <h3>Welcome to {$headlineName} Plugin</h3>\n" .
            "</div>\n";

        File::put($path . "/resources/views/index.blade.php", $content);
    }
}
