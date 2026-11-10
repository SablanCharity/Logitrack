<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentCapstoneAuthor extends Model
{
    protected $fillable = [
        'student_capstone_submission_id',
        'student_masterlist_record_id',
        'user_id',
        'is_leader',
    ];

    protected function casts(): array
    {
        return ['is_leader' => 'boolean'];
    }

    public function masterlistRecord(): BelongsTo
    {
        return $this->belongsTo(StudentMasterlistRecord::class, 'student_masterlist_record_id');
    }
}
