<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCapstoneSubmission extends Model
{
    protected $fillable = [
        'submitted_by',
        'title',
        'abstract',
        'adviser',
        'content_hash',
        'submitted_date',
        'study_path',
        'manual_path',
        'system_path',
        'mentee_form_path',
        'status',
        'return_reason',
        'views_count',
    ];

    protected function casts(): array
    {
        return [
            'submitted_date' => 'date',
            'views_count' => 'integer',
        ];
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function adviserUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adviser');
    }

    public function authors(): HasMany
    {
        return $this->hasMany(StudentCapstoneAuthor::class);
    }

    public function filePath(string $file): string
    {
        return match ($file) {
            'study' => $this->study_path,
            'manual' => $this->manual_path,
            'system' => $this->system_path,
            'mentee-form' => $this->mentee_form_path,
            default => abort(404),
        };
    }
}
