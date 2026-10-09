<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassroomMember extends Model
{
    protected $fillable = [
        'classroom_id',
        'user_id',
        'status',
    ];

    /**
     * The classroom this membership belongs to.
     */
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * The user/student who belongs to the classroom.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}