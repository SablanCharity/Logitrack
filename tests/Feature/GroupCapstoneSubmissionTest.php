<?php

namespace Tests\Feature;

use App\Models\GroupCapstoneMember;
use App\Models\StudentCapstoneAuthor;
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

    public function test_individual_submission_saves_selected_authors(): void
    {
        Storage::fake('local');
        $leader = User::factory()->create([
            'student_number' => '202626598',
            'program' => 'BSCS',
        ]);
        $record = StudentMasterlistRecord::create([
            'student_number' => '202626598',
            'name' => 'Individual Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);

        $response = $this->actingAs($leader)->post(route('student.submissions.store'), [
            'title' => 'An Individual Capstone Study',
            'abstract' => 'An individual abstract for the selected author.',
            'submitted_date' => '2026-11-11',
            'authors' => [$record->id],
            'study' => UploadedFile::fake()->create('study.pdf', 10, 'application/pdf'),
            'manual' => UploadedFile::fake()->create('manual.pdf', 10, 'application/pdf'),
            'system' => UploadedFile::fake()->create('system.zip', 10, 'application/zip'),
            'mentee_form' => UploadedFile::fake()->create('mentee.pdf', 10, 'application/pdf'),
        ]);

        $response->assertRedirect(route('student.submissions.create'));
        $this->assertCount(1, StudentCapstoneAuthor::query()->get());
    }
}
