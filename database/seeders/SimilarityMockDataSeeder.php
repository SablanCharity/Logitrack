<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\GroupRoom;
use App\Models\GroupMember;
use App\Models\CapstoneProject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SimilarityMockDataSeeder extends Seeder
{
    public function run(): void
    {
        // Reuse the sample accounts created by DatabaseSeeder.
        $admin = User::where('email', 'admin@logitrack.test')
            ->firstOrFail();

        $instructor = User::where('email', 'instructor@test.com')
            ->firstOrFail();

        $classroom = Classroom::updateOrCreate(
            ['class_code' => 'SIM-TEST-2026'],
            [
                'instructor_id' => $instructor->id,
                'classroom_name' => 'Similarity Testing Class',
            ]
        );

        $studies = [
            [
                'email' => 'sim.student1@test.com',
                'first_name' => 'Alex',
                'last_name' => 'Reyes',
                'program' => 'BSIT',
                'title' => 'QR Code-Based Student Attendance Monitoring System',
                'abstract' => 'This study develops a web-based student attendance monitoring system using QR codes. Students scan a unique QR code to record attendance, while instructors view attendance records and generate reports. The system aims to reduce manual attendance recording, minimize errors, and improve classroom attendance monitoring.',
            ],
            [
                'email' => 'sim.student2@test.com',
                'first_name' => 'Jamie',
                'last_name' => 'Cruz',
                'program' => 'BSIT',
                'title' => 'Web-Based Classroom Attendance System Using QR Code Technology',
                'abstract' => 'This project proposes an online classroom attendance system that uses QR code scanning to record student attendance. Instructors can monitor attendance, review student records, and generate reports. The system is intended to replace paper-based attendance and provide a faster, more organized attendance process.',
            ],
            [
                'email' => 'sim.student3@test.com',
                'first_name' => 'Taylor',
                'last_name' => 'Garcia',
                'program' => 'BSCS',
                'title' => 'RFID-Based Student Attendance and Monitoring System',
                'abstract' => 'This study develops a student attendance monitoring application using RFID cards. Students tap their cards on a reader to record attendance. The application stores attendance logs and allows instructors to review student participation. It seeks to improve recording efficiency and reduce errors associated with manual attendance.',
            ],
            [
                'email' => 'sim.student4@test.com',
                'first_name' => 'Morgan',
                'last_name' => 'Santos',
                'program' => 'BSIT',
                'title' => 'Personal Finance and Expense Tracking Application for College Students',
                'abstract' => 'This study presents a personal finance application that helps college students record expenses, categorize spending, set budgets, and monitor their remaining allowance. The application generates spending summaries to help users understand their financial habits and improve money management.',
            ],
            [
                'email' => 'sim.student5@test.com',
                'first_name' => 'Casey',
                'last_name' => 'Mendoza',
                'program' => 'BSCS',
                'title' => 'Smart Plant Disease Detection Using Image Classification',
                'abstract' => 'This project explores image classification for identifying common plant diseases. Users submit images of plant leaves, and the system analyzes visual patterns to classify possible diseases. The goal is to help growers identify plant health problems earlier and support decisions about crop care.',
            ],
            [
                'email' => 'sim.student6@test.com',
                'first_name' => 'Riley',
                'last_name' => 'Bautista',
                'program' => 'BSIT',
                'title' => 'Online Appointment Scheduling System for a Community Health Center',
                'abstract' => 'This study develops an online appointment scheduling platform for a community health center. Patients can view available schedules and request appointments, while staff manage bookings and patient appointment records. The system aims to reduce waiting time and improve appointment organization.',
            ],
        ];

        foreach ($studies as $index => $study) {
            // Create a sample student account.
            $student = User::updateOrCreate(
                ['email' => $study['email']],
                [
                    'first_name' => $study['first_name'],
                    'last_name' => $study['last_name'],
                    'password' => Hash::make('Password123!'),
                    'role' => 'student',
                    'program' => $study['program'],
                    'year_section' => '4-1',
                    'is_approved' => true,
                ]
            );

            // Ensure the student belongs to the testing classroom.
            ClassroomMember::updateOrCreate(
                [
                    'classroom_id' => $classroom->id,
                    'user_id' => $student->id,
                ],
                ['status' => 'approved']
            );

            // Create a group room for each study.
            $groupRoom = GroupRoom::updateOrCreate(
                [
                    'classroom_id' => $classroom->id,
                    'group_name' => 'Similarity Test Group ' . ($index + 1),
                ],
                []
            );

            // Add the student as group leader.
            GroupMember::updateOrCreate(
                [
                    'group_room_id' => $groupRoom->id,
                    'user_id' => $student->id,
                ],
                ['is_leader' => true]
            );

            // Add the study to the approved repository.
            CapstoneProject::updateOrCreate(
                ['group_room_id' => $groupRoom->id],
                [
                    'submitted_by' => $student->id,
                    'verified_by' => $instructor->id,
                    'approved_by' => $admin->id,
                    'title' => $study['title'],
                    'abstract' => $study['abstract'],
                    'program' => $study['program'],
                    'year_section' => '4-1',
                    'publication_date' => now()
                        ->subYears(1)
                        ->addDays($index)
                        ->toDateString(),
                    'status' => 'admin_approved',
                    'submitted_at' => now()->subDays(10),
                    'verified_at' => now()->subDays(5),
                    'approved_at' => now()->subDays(2),
                    'return_reason' => null,
                ]
            );
        }
    }
}
