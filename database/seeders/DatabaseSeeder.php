<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ADMIN
        User::updateOrCreate(
            ['email' => 'admin@logitrack.test'],
            [
                'first_name' => 'System',
                'last_name' => 'Administrator',
                'password' => Hash::make('Password123!'),
                'role' => 'admin',
                'is_approved' => true,
            ]
        );

        // INSTRUCTOR
        $instructor = User::updateOrCreate(
            ['email' => 'instructor@test.com'],
            [
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'password' => Hash::make('Password123!'),
                'role' => 'instructor',
                'is_approved' => true,
            ]
        );

        // STUDENT
        $student = User::updateOrCreate(
            ['email' => 'student@test.com'],
            [
                'first_name' => 'Juan',
                'last_name' => 'Dela Cruz',
                'password' => Hash::make('Password123!'),
                'role' => 'student',
                'program' => 'BSIT',
                'year_section' => '4-1',
                'is_approved' => true,
            ]
        );

        // CLASSROOM
        $classroom = Classroom::updateOrCreate(
            ['class_code' => 'TEST-2026'],
            [
                'instructor_id' => $instructor->id,
                'classroom_name' => 'BSIT 4-1 Sample Class',
            ]
        );

        // STUDENT CLASSROOM MEMBERSHIP
        ClassroomMember::updateOrCreate(
            [
                'classroom_id' => $classroom->id,
                'user_id' => $student->id,
            ],
            [
                'status' => 'approved',
            ]
        );
    }
}
