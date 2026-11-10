<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentMasterlistRecord extends Model
{
    protected $fillable = [
        'student_number',
        'name',
        'first_name',
        'last_name',
        'email',
        'program',
        'status',
        'source_file',
    ];
}
