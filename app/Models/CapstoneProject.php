<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CapstoneProject extends Model
{
   protected $fillable = [
        'group_room_id',
        'submitted_by',
        'verified_by',
        'approved_by',
        'title',
        'abstract',
        'program',
        'year_section',
        'publication_date',
        'status',
        'submitted_at',
        'verified_at',
        'approved_at',
        'return_reason',
    ];

    protected $casts = [
        'publication_date' => 'date',
        'submitted_at' => 'datetime',
        'verified_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    /**
     * The group that submitted this capstone.
     */
    public function groupRoom(): BelongsTo
    {
        return $this->belongsTo(GroupRoom::class);
    }

    public function submitter()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifier()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}