<?php

namespace Tests\Feature;

use App\Models\StudentCapstoneAuthor;
use App\Models\StudentCapstoneSubmission;
use App\Models\StudentMasterlistRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSubmissionMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_submission_monitoring_lists_student_submissions_and_authors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submitter = User::factory()->create([
            'first_name' => 'Alex',
            'last_name' => 'Submitter',
            'student_number' => '202600101',
        ]);
        $author = StudentMasterlistRecord::create([
            'student_number' => '202600101',
            'name' => 'Alex Submitter',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $submission = StudentCapstoneSubmission::create([
            'submitted_by' => $submitter->id,
            'title' => 'Student Monitoring Capstone',
            'abstract' => 'A study submitted for admin monitoring.',
            'content_hash' => hash('sha256', 'student monitoring capstone'),
            'submitted_date' => '2026-11-10',
            'study_path' => 'student/study.pdf',
            'manual_path' => 'student/manual.pdf',
            'system_path' => 'student/system.zip',
            'mentee_form_path' => 'student/mentee.pdf',
            'status' => 'submitted',
        ]);
        StudentCapstoneAuthor::create([
            'student_capstone_submission_id' => $submission->id,
            'student_masterlist_record_id' => $author->id,
            'user_id' => $submitter->id,
            'is_leader' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.group-submissions.index'))
            ->assertOk()
            ->assertViewHas('counts', fn (array $counts): bool => $counts['submitted'] === 1)
            ->assertDontSee('Submissions displayed')
            ->assertSee('Student submission requests')
            ->assertSee('Student Monitoring Capstone')
            ->assertSee('Alex Submitter')
            ->assertSee('202600101')
            ->assertSee('Nov 10, 2026')
            ->assertSee('Students submitted')
            ->assertDontSee('Group submissions')
            ->assertSee(route('admin.student-submissions.show', $submission), false);
    }

    public function test_admin_capstone_review_shows_submitter_date_and_all_authors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submitter = User::factory()->create([
            'first_name' => 'Jordan',
            'last_name' => 'Leader',
            'student_number' => '202600201',
        ]);
        $firstAuthor = StudentMasterlistRecord::create([
            'student_number' => '202600201',
            'name' => 'Jordan Leader',
            'program' => 'BSIT',
            'status' => 'listed',
        ]);
        $secondAuthor = StudentMasterlistRecord::create([
            'student_number' => '202600202',
            'name' => 'Casey Coauthor',
            'program' => 'BSIT',
            'status' => 'listed',
        ]);
        $submission = StudentCapstoneSubmission::create([
            'submitted_by' => $submitter->id,
            'title' => 'Capstone Review Details',
            'abstract' => 'The review page shows the submitter and authors.',
            'content_hash' => hash('sha256', 'capstone review details'),
            'submitted_date' => '2026-11-09',
            'study_path' => 'review/study.pdf',
            'manual_path' => 'review/manual.pdf',
            'system_path' => 'review/system.zip',
            'mentee_form_path' => 'review/mentee.pdf',
            'status' => 'submitted',
        ]);
        StudentCapstoneAuthor::create([
            'student_capstone_submission_id' => $submission->id,
            'student_masterlist_record_id' => $firstAuthor->id,
            'user_id' => $submitter->id,
            'is_leader' => true,
        ]);
        StudentCapstoneAuthor::create([
            'student_capstone_submission_id' => $submission->id,
            'student_masterlist_record_id' => $secondAuthor->id,
            'is_leader' => false,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.student-submissions.show', $submission))
            ->assertOk()
            ->assertSee('Jordan Leader')
            ->assertSee('202600201')
            ->assertSee('November 09, 2026')
            ->assertSee('Authors')
            ->assertSee('Casey Coauthor')
            ->assertSee('202600202')
            ->assertSee('Submitted by');
    }

    public function test_admin_can_add_student_submission_to_repository(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $submitter = User::factory()->create([
            'program' => 'BSCS',
            'student_number' => '202600301',
        ]);
        $submission = StudentCapstoneSubmission::create([
            'submitted_by' => $submitter->id,
            'title' => 'Repository Ready Capstone',
            'abstract' => 'A completed study ready for repository publication.',
            'content_hash' => hash('sha256', 'repository ready capstone'),
            'submitted_date' => '2026-11-11',
            'study_path' => 'repository/study.pdf',
            'manual_path' => 'repository/manual.pdf',
            'system_path' => 'repository/system.zip',
            'mentee_form_path' => 'repository/mentee.pdf',
            'status' => 'submitted',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.student-submissions.add-to-repository', $submission))
            ->assertRedirect(route('repository.show', [
                'capstoneId' => $submission->id,
                'type' => 'student',
            ]));

        $this->assertDatabaseHas('student_capstone_submissions', [
            'id' => $submission->id,
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get(route('repository.index'))
            ->assertOk()
            ->assertSee('Repository Ready Capstone');
    }
}
