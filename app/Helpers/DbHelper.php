<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

if (!function_exists('dbAvailable')) {
    function dbAvailable(?string $table = null): bool
    {
        try {
            DB::connection()->getPdo();
            return $table ? Schema::hasTable($table) : true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
