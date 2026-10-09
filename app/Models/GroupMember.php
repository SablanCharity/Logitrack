<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMember extends Model
{
    protected $fillable = [
        'group_room_id',
        'user_id',
        'is_leader',
    ];

    protected $casts = [
        'is_leader' => 'boolean',
    ];

    /**
     * The group room this membership belongs to.
     */
    public function groupRoom(): BelongsTo
    {
        return $this->belongsTo(GroupRoom::class);
    }

    /**
     * The student who belongs to the group.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}