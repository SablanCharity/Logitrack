<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroupCapstoneSubmission extends Model
{
    protected $fillable = [
        'submitted_by',
        'program',
        'title',
        'abstract',
        'adviser',
        'submitted_date',
        'study_path',
        'system_path',
        'document_path',
        'mentee_form_path',
        'content_hash',
        'status',
    ];

    protected function casts(): array
    {
        return ['submitted_date' => 'date'];
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function members(): HasMany
    {
        return $this->hasMany(GroupCapstoneMember::class);
    }
}
