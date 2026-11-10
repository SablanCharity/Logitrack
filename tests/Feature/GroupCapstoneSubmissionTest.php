<?php

namespace Tests\Feature;

use App\Models\GroupCapstoneMember;
use App\Models\StudentCapstoneAuthor;
use App\Models\StudentCapstoneSubmission;
use App\Models\StudentMasterlistRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GroupCapstoneSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_selected_masterlist_authors_are_saved_as_submission_members(): void
    {
        Storage::fake('local');
        $leader = User::factory()->create([
            'student_number' => '202626597',
            'program' => 'BSIT',
        ]);
        $leaderRecord = StudentMasterlistRecord::create([
            'student_number' => '202626597',
            'name' => 'Leader Student',
            'program' => 'BSIT',
            'status' => 'listed',
        ]);
        $memberRecord = StudentMasterlistRecord::create([
            'student_number' => '202689235',
            'name' => 'Member Student',
            'program' => 'BSIT',
            'status' => 'listed',
        ]);

        $response = $this->actingAs($leader)->post(route('student.group-submissions.store'), [
            'program' => 'BSIT',
            'title' => 'A Distinct Capstone Study',
            'abstract' => 'A distinct abstract for the selected authors.',
            'adviser' => 'Mrs. Adviser',
            'submitted_date' => '2026-11-11',
            'authors' => [$leaderRecord->id, $memberRecord->id],
            'study' => UploadedFile::fake()->create('study.pdf', 10, 'application/pdf'),
            'system' => UploadedFile::fake()->create('system.zip', 10, 'application/zip'),
            'document' => UploadedFile::fake()->create('document.docx', 10, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            'mentee_form' => UploadedFile::fake()->create('mentee.pdf', 10, 'application/pdf'),
        ]);

        $response->assertRedirect(route('student.group-submissions.create'));
        $this->assertCount(2, GroupCapstoneMember::query()->get());
        $this->assertDatabaseHas('group_capstone_members', [
            'student_masterlist_record_id' => $memberRecord->id,
            'student_id' => null,
        ]);
    }

    public function test_individual_submission_saves_authors_and_shows_latest_status_after_submit(): void
    {
        Storage::fake('local');
        $leader = User::factory()->create([
            'student_number' => '202626598',
            'program' => 'BSCS',
        ]);
        StudentCapstoneSubmission::create([
            'submitted_by' => $leader->id,
            'title' => 'A Previously Submitted Capstone',
            'abstract' => 'A previous abstract.',
            'content_hash' => hash('sha256', 'previous submission'),
            'submitted_date' => '2026-11-10',
            'study_path' => 'previous/study.pdf',
            'manual_path' => 'previous/manual.pdf',
            'system_path' => 'previous/system.zip',
            'mentee_form_path' => 'previous/mentee.pdf',
            'status' => 'submitted',
        ]);
        $record = StudentMasterlistRecord::create([
            'student_number' => '202626598',
            'name' => 'Individual Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $memberRecord = StudentMasterlistRecord::create([
            'student_number' => '202626599',
            'name' => 'Second Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);

        $this->actingAs($leader)
            ->get(route('student.submissions.create'))
            ->assertOk()
            ->assertDontSee('Your latest submission is currently');

        $response = $this->actingAs($leader)->post(route('student.submissions.store'), [
            'title' => 'An Individual Capstone Study',
            'abstract' => 'An individual abstract for the selected author.',
            'submitted_date' => '2026-11-11',
            'authors' => [$record->id, $memberRecord->id],
            'study' => UploadedFile::fake()->create('study.pdf', 10, 'application/pdf'),
            'manual' => UploadedFile::fake()->create('manual.pdf', 10, 'application/pdf'),
            'system' => UploadedFile::fake()->create('system.zip', 10, 'application/zip'),
            'mentee_form' => UploadedFile::fake()->create('mentee.pdf', 10, 'application/pdf'),
        ]);

        $response->assertRedirect(route('student.submissions.create'));
        $response->assertSessionHas('success');
        $this->get(route('student.submissions.create'))
            ->assertOk()
            ->assertSee('Your latest submission is currently')
            ->assertSee('Submitted');
        $this->assertCount(2, StudentCapstoneAuthor::query()->get());
    }

    public function test_individual_submission_rejects_more_than_two_authors(): void
    {
        Storage::fake('local');
        $student = User::factory()->create([
            'student_number' => '202626600',
            'program' => 'BSCS',
        ]);
        $authors = collect([
            ['202626601', 'First Student'],
            ['202626602', 'Second Student'],
            ['202626603', 'Third Student'],
        ])->map(fn (array $author): int => StudentMasterlistRecord::create([
            'student_number' => $author[0],
            'name' => $author[1],
            'program' => 'BSCS',
            'status' => 'listed',
        ])->id)->all();

        $response = $this->actingAs($student)->post(route('student.submissions.store'), [
            'title' => 'A Capstone With Too Many Authors',
            'abstract' => 'An abstract for three authors.',
            'submitted_date' => '2026-11-11',
            'authors' => $authors,
            'study' => UploadedFile::fake()->create('study.pdf', 10, 'application/pdf'),
            'manual' => UploadedFile::fake()->create('manual.pdf', 10, 'application/pdf'),
            'system' => UploadedFile::fake()->create('system.zip', 10, 'application/zip'),
            'mentee_form' => UploadedFile::fake()->create('mentee.pdf', 10, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors([
            'authors' => 'The authors field must not have more than 2 items.',
        ]);
        $this->assertDatabaseCount('student_capstone_submissions', 0);
        $this->assertDatabaseCount('student_capstone_authors', 0);
    }
}
