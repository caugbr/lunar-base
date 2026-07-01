<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $table = 'themes';

    protected $fillable = [
        'name',
        'folder_name',
        'version',
        'description',
        'author',
        'screenshot',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
