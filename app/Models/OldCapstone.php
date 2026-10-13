<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OldCapstone extends Model
{
    protected $fillable = [
        'title',
        'abstract',
        'authors',
        'adviser',
        'program',
        'department',
        'institution',
        'publication_date',
        'metadata_image_path',
        'ocr_text',
        'status',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
        'return_reason',
    ];

    protected function casts(): array
    {
        return [
            'authors' => 'array',
            'reviewed_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
