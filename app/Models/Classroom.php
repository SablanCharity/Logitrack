<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    protected $fillable = [
        'instructor_id',
        'classroom_name',
        'class_code',
    ];

    /**
     * The instructor who owns this classroom.
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Students who are members of this classroom.
     */
    public function members(): HasMany
    {
        return $this->hasMany(ClassroomMember::class);
    }

    /**
     * Group rooms inside this classroom.
     */
    public function groupRooms(): HasMany
    {
        return $this->hasMany(GroupRoom::class);
    }
}
