<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapstoneAudit extends Model
{
    protected $fillable = [
        'user_id',
        'capstone_project_id',
        'action',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function capstoneProject(): BelongsTo
    {
        return $this->belongsTo(CapstoneProject::class);
    }
}
