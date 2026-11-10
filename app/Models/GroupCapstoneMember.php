<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupCapstoneMember extends Model
{
    protected $fillable = [
        'group_capstone_submission_id',
        'student_id',
        'student_masterlist_record_id',
        'is_leader',
    ];

    protected function casts(): array
    {
        return ['is_leader' => 'boolean'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function masterlistRecord(): BelongsTo
    {
        return $this->belongsTo(StudentMasterlistRecord::class, 'student_masterlist_record_id');
    }
}
