<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminNoticeDismissal extends Model
{
    public $timestamps = false;
    protected $fillable = ['notice_id', 'user_id', 'dismissed_at'];
    protected $casts = ['dismissed_at' => 'datetime'];
}
