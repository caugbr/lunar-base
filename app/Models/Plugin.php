<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Plugin extends Model
{
    protected $table = 'plugins';

    protected $fillable = [
        'name',
        'folder_name',
        'service_provider_class',
        'version',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Verifica se uma pasta de plugin possui migrações no disco.
     */
    public static function checkMigrations(string $folderName): bool
    {
        $path = base_path("plugins/{$folderName}/database/migrations");

        return File::isDirectory($path) && !empty(File::glob("{$path}/*.php"));
    }

    /**
     * Atalho para instâncias do Model ($plugin->hasMigrations())
     */
    public function hasMigrations(): bool
    {
        return self::checkMigrations($this->folder_name);
    }
}
