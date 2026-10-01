<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\AdminNoticeDismissal;

class AdminNotice extends Model
{
    protected $fillable = [
        'message',
        'type',
        'target_type',
        'target_values',
        'is_active',
        'expires_at',
        'created_by',
    ];

    protected $casts = [
        'target_values' => 'array',
        'is_active'     => 'boolean',
        'expires_at'    => 'datetime',
    ];

    public function dismissals()
    {
        return $this->hasMany(AdminNoticeDismissal::class, 'notice_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
