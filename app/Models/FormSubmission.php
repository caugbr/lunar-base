<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'data',
        'ip_address',
    ];

    // Converte o JSON das respostas em Array PHP automaticamente
    protected $casts = [
        'data' => 'array',
    ];

    /**
     * Relação: Uma submissão pertence a um formulário.
     */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
