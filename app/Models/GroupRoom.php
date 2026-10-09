<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GroupRoom extends Model
{
    protected $fillable = [
        'classroom_id',
        'group_name',
    ];

    /**
     * The classroom this group belongs to.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Students who belong to this group.
     */
    public function members(): HasMany
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * The capstone project submitted by this group.
     */
    public function capstoneProject(): HasOne
    {
        return $this->hasOne(CapstoneProject::class);
    }
}