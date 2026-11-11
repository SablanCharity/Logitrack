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
        $adviser = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
            'is_active' => true,
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
        $adviser = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
            'is_active' => true,
        ]);

        $this->actingAs($leader)
            ->get(route('student.submissions.create'))
            ->assertOk()
            ->assertDontSee('Each student may participate in only one submission.');

        $response = $this->actingAs($leader)->post(route('student.submissions.store'), [
            'title' => 'An Individual Capstone Study',
            'abstract' => 'An individual abstract for the selected author.',
            'adviser' => $adviser->id,
            'submitted_date' => '2026-11-11',
            'authors' => [$record->id, $memberRecord->id],
            'study' => UploadedFile::fake()->create('study.pdf', 10, 'application/pdf'),
            'manual' => UploadedFile::fake()->create('manual.pdf', 10, 'application/pdf'),
            'system' => UploadedFile::fake()->create('system.zip', 10, 'application/zip'),
            'mentee_form' => UploadedFile::fake()->create('mentee.pdf', 10, 'application/pdf'),
        ]);

        $response->assertRedirect(route('student.submissions.create'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('student_capstone_submissions', [
            'submitted_by' => $leader->id,
            'adviser' => $adviser->id,
        ]);
        $this->get(route('student.submissions.create'))
            ->assertOk()
            ->assertSee('Each student may participate in only one submission.')
            ->assertDontSee('action="'.route('student.submissions.store').'"', false)
            ->assertSee('Submitted');
        $this->assertCount(2, StudentCapstoneAuthor::query()->get());
    }

    public function test_submitter_can_edit_and_resubmit_a_returned_capstone(): void
    {
        Storage::fake('local');
        $student = User::factory()->create([
            'student_number' => '202626615',
            'program' => 'BSCS',
        ]);
        $studentRecord = StudentMasterlistRecord::create([
            'student_number' => '202626615',
            'name' => 'Returned Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $adviser = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
            'is_active' => true,
        ]);
        $submission = $this->createSubmissionWithAuthors($student, [$studentRecord]);
        $submission->update([
            'status' => 'returned',
            'return_reason' => 'Please clarify the research scope.',
        ]);

        $this->actingAs($student)
            ->get(route('student.submissions.create'))
            ->assertOk()
            ->assertSee('action="'.route('student.submissions.update', $submission).'"', false)
            ->assertSee('Save changes and resubmit')
            ->assertSee('Please clarify the research scope.');

        $response = $this->actingAs($student)
            ->put(route('student.submissions.update', $submission), [
                'title' => 'Updated Capstone Title',
                'abstract' => 'An updated abstract for the returned capstone.',
                'adviser' => $adviser->id,
                'submitted_date' => '2026-11-12',
            ]);

        $response->assertRedirect(route('student.submissions.create'));
        $response->assertSessionHas('success', 'Your changes were resubmitted for review.');
        $this->assertDatabaseHas('student_capstone_submissions', [
            'id' => $submission->id,
            'title' => 'Updated Capstone Title',
            'status' => 'submitted',
            'return_reason' => null,
            'study_path' => 'existing/study.pdf',
            'adviser' => $adviser->id,
        ]);
        $this->assertDatabaseCount('student_capstone_authors', 1);
    }

    public function test_resubmission_rejects_a_duplicate_title_and_abstract(): void
    {
        $student = User::factory()->create([
            'student_number' => '202626619',
            'program' => 'BSCS',
        ]);
        $otherStudent = User::factory()->create([
            'student_number' => '202626620',
            'program' => 'BSCS',
        ]);
        $studentRecord = StudentMasterlistRecord::create([
            'student_number' => '202626619',
            'name' => 'Returned Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $otherRecord = StudentMasterlistRecord::create([
            'student_number' => '202626620',
            'name' => 'Other Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $adviser = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
            'is_active' => true,
        ]);
        $existingSubmission = $this->createSubmissionWithAuthors($otherStudent, [$otherRecord]);
        $existingSubmission->update([
            'title' => 'Taken Capstone',
            'abstract' => 'An already-used abstract.',
            'content_hash' => hash('sha256', 'taken capstone|an already-used abstract.'),
        ]);
        $returnedSubmission = $this->createSubmissionWithAuthors($student, [$studentRecord]);
        $returnedSubmission->update(['status' => 'returned']);

        $this->actingAs($student)
            ->from(route('student.submissions.create'))
            ->put(route('student.submissions.update', $returnedSubmission), [
                'title' => ' TAKEN   CAPSTONE ',
                'abstract' => 'An ALREADY-USED abstract.',
                'adviser' => $adviser->id,
                'submitted_date' => '2026-11-12',
            ])
            ->assertRedirect(route('student.submissions.create'))
            ->assertSessionHasErrors('title');

        $this->assertDatabaseHas('student_capstone_submissions', [
            'id' => $returnedSubmission->id,
            'status' => 'returned',
            'title' => 'Existing Capstone Submission',
        ]);
    }

    public function test_only_the_original_submitter_can_edit_a_returned_capstone(): void
    {
        $submitter = User::factory()->create([
            'student_number' => '202626616',
            'program' => 'BSCS',
        ]);
        $otherStudent = User::factory()->create([
            'student_number' => '202626617',
            'program' => 'BSCS',
        ]);
        $record = StudentMasterlistRecord::create([
            'student_number' => '202626616',
            'name' => 'Original Submitter',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $adviser = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
            'is_active' => true,
        ]);
        $submission = $this->createSubmissionWithAuthors($submitter, [$record]);
        $submission->update(['status' => 'returned']);

        $this->actingAs($otherStudent)
            ->put(route('student.submissions.update', $submission), [
                'title' => 'Unauthorized title',
                'abstract' => 'Unauthorized abstract.',
                'adviser' => $adviser->id,
                'submitted_date' => '2026-11-12',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('student_capstone_submissions', [
            'id' => $submission->id,
            'title' => 'Existing Capstone Submission',
            'status' => 'returned',
        ]);
    }

    public function test_a_non_returned_capstone_cannot_be_edited(): void
    {
        $student = User::factory()->create([
            'student_number' => '202626618',
            'program' => 'BSCS',
        ]);
        $record = StudentMasterlistRecord::create([
            'student_number' => '202626618',
            'name' => 'Submitted Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $adviser = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
            'is_active' => true,
        ]);
        $submission = $this->createSubmissionWithAuthors($student, [$record]);

        $this->actingAs($student)
            ->put(route('student.submissions.update', $submission), [
                'title' => 'Unauthorized title',
                'abstract' => 'Unauthorized abstract.',
                'adviser' => $adviser->id,
                'submitted_date' => '2026-11-12',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('student_capstone_submissions', [
            'id' => $submission->id,
            'title' => 'Existing Capstone Submission',
            'status' => 'submitted',
        ]);
    }

    public function test_submitting_student_and_selected_authors_cannot_submit_or_be_selected_again(): void
    {
        Storage::fake('local');
        $leader = User::factory()->create([
            'student_number' => '202626610',
            'program' => 'BSCS',
        ]);
        $member = User::factory()->create([
            'student_number' => '202626611',
            'program' => 'BSCS',
        ]);
        $otherStudent = User::factory()->create([
            'student_number' => '202626612',
            'program' => 'BSCS',
        ]);
        $leaderRecord = StudentMasterlistRecord::create([
            'student_number' => '202626610',
            'name' => 'Leader Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $memberRecord = StudentMasterlistRecord::create([
            'student_number' => '202626611',
            'name' => 'Member Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $otherStudentRecord = StudentMasterlistRecord::create([
            'student_number' => '202626612',
            'name' => 'Other Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $adviser = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
            'is_active' => true,
        ]);
        $this->createSubmissionWithAuthors($leader, [$leaderRecord, $memberRecord]);

        $this->actingAs($leader)
            ->get(route('student.submissions.create'))
            ->assertOk()
            ->assertSee('Each student may participate in only one submission.')
            ->assertDontSee('action="'.route('student.submissions.store').'"', false);

        $this->actingAs($leader)
            ->post(route('student.submissions.store'), $this->submissionPayload(
                $adviser,
                [$leaderRecord->id],
                'Leader Second Capstone',
                'A second capstone by a previously submitting student.',
            ))
            ->assertSessionHasErrors('authors');

        $this->actingAs($member)
            ->get(route('student.submissions.create'))
            ->assertOk()
            ->assertSee('Each student may participate in only one submission.')
            ->assertDontSee('action="'.route('student.submissions.store').'"', false);

        $this->actingAs($member)
            ->post(route('student.submissions.store'), $this->submissionPayload(
                $adviser,
                [$memberRecord->id],
                'Member Second Capstone',
                'A second capstone by a previously selected author.',
            ))
            ->assertSessionHasErrors('authors');

        $this->actingAs($otherStudent)
            ->get(route('student.submissions.create'))
            ->assertOk()
            ->assertDontSee('value="'.$leaderRecord->id.'"', false)
            ->assertDontSee('value="'.$memberRecord->id.'"', false);

        $this->actingAs($otherStudent)
            ->post(route('student.submissions.store'), $this->submissionPayload(
                $adviser,
                [$memberRecord->id, $otherStudentRecord->id],
                'Another Capstone',
                'A separate capstone with a previously selected author.',
            ))
            ->assertSessionHasErrors('authors');

        $this->assertDatabaseCount('student_capstone_submissions', 1);
        $this->assertDatabaseCount('student_capstone_authors', 2);
    }

    public function test_duplicate_normalized_title_and_abstract_are_rejected(): void
    {
        Storage::fake('local');
        $firstStudent = User::factory()->create([
            'student_number' => '202626613',
            'program' => 'BSCS',
        ]);
        $secondStudent = User::factory()->create([
            'student_number' => '202626614',
            'program' => 'BSCS',
        ]);
        $firstRecord = StudentMasterlistRecord::create([
            'student_number' => '202626613',
            'name' => 'First Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $secondRecord = StudentMasterlistRecord::create([
            'student_number' => '202626614',
            'name' => 'Second Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $adviser = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
            'is_active' => true,
        ]);

        $this->actingAs($firstStudent)
            ->post(route('student.submissions.store'), $this->submissionPayload(
                $adviser,
                [$firstRecord->id],
                'A Unique Capstone',
                'A unique abstract for this capstone.',
            ))
            ->assertRedirect(route('student.submissions.create'));

        $this->actingAs($secondStudent)
            ->from(route('student.submissions.create'))
            ->post(route('student.submissions.store'), $this->submissionPayload(
                $adviser,
                [$secondRecord->id],
                '  a unique   capstone ',
                'A UNIQUE abstract   for this capstone.',
            ))
            ->assertRedirect(route('student.submissions.create'))
            ->assertSessionHasErrors('title');

        $this->assertDatabaseCount('student_capstone_submissions', 1);
    }

    public function test_individual_submission_rejects_more_than_three_authors(): void
    {
        Storage::fake('local');
        $student = User::factory()->create([
            'student_number' => '202626600',
            'program' => 'BSCS',
        ]);
        $adviser = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
            'is_active' => true,
        ]);
        $authors = collect([
            ['202626601', 'First Student'],
            ['202626602', 'Second Student'],
            ['202626603', 'Third Student'],
            ['202626604', 'Fourth Student'],
        ])->map(fn (array $author): int => StudentMasterlistRecord::create([
            'student_number' => $author[0],
            'name' => $author[1],
            'program' => 'BSCS',
            'status' => 'listed',
        ])->id)->all();

        $response = $this->actingAs($student)->post(route('student.submissions.store'), [
            'title' => 'A Capstone With Too Many Authors',
            'abstract' => 'An abstract for three authors.',
            'adviser' => $adviser->id,
            'submitted_date' => '2026-11-11',
            'authors' => $authors,
            'study' => UploadedFile::fake()->create('study.pdf', 10, 'application/pdf'),
            'manual' => UploadedFile::fake()->create('manual.pdf', 10, 'application/pdf'),
            'system' => UploadedFile::fake()->create('system.zip', 10, 'application/zip'),
            'mentee_form' => UploadedFile::fake()->create('mentee.pdf', 10, 'application/pdf'),
        ]);

        $response->assertSessionHasErrors([
            'authors' => 'The authors field must not have more than 3 items.',
        ]);
        $this->assertDatabaseCount('student_capstone_submissions', 0);
        $this->assertDatabaseCount('student_capstone_authors', 0);
    }

    public function test_submitting_student_is_added_as_an_author_when_not_selected(): void
    {
        Storage::fake('local');
        $student = User::factory()->create([
            'student_number' => '202626604',
            'program' => 'BSCS',
        ]);
        $studentRecord = StudentMasterlistRecord::create([
            'student_number' => '202626604',
            'name' => 'Submitting Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $otherRecord = StudentMasterlistRecord::create([
            'student_number' => '202626605',
            'name' => 'Selected Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $adviser = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
            'is_active' => true,
        ]);

        $this->actingAs($student)->post(route('student.submissions.store'), [
            'title' => 'Automatic Submitting Author Study',
            'abstract' => 'The submitting student is always an author.',
            'adviser' => $adviser->id,
            'submitted_date' => '2026-11-11',
            'authors' => [$otherRecord->id],
            'study' => UploadedFile::fake()->create('study.pdf', 10, 'application/pdf'),
            'manual' => UploadedFile::fake()->create('manual.pdf', 10, 'application/pdf'),
            'system' => UploadedFile::fake()->create('system.zip', 10, 'application/zip'),
            'mentee_form' => UploadedFile::fake()->create('mentee.pdf', 10, 'application/pdf'),
        ])->assertRedirect(route('student.submissions.create'));

        $this->assertDatabaseHas('student_capstone_authors', [
            'student_masterlist_record_id' => $studentRecord->id,
            'user_id' => $student->id,
            'is_leader' => true,
        ]);
        $this->assertDatabaseHas('student_capstone_authors', [
            'student_masterlist_record_id' => $otherRecord->id,
        ]);
    }

    /**
     * @param  array<int, StudentMasterlistRecord>  $authors
     */
    private function createSubmissionWithAuthors(User $submitter, array $authors): StudentCapstoneSubmission
    {
        $submission = StudentCapstoneSubmission::create([
            'submitted_by' => $submitter->id,
            'title' => 'Existing Capstone Submission',
            'abstract' => 'An existing capstone abstract.',
            'submitted_date' => '2026-11-11',
            'study_path' => 'existing/study.pdf',
            'manual_path' => 'existing/manual.pdf',
            'system_path' => 'existing/system.zip',
            'mentee_form_path' => 'existing/mentee.pdf',
            'status' => 'submitted',
        ]);

        foreach ($authors as $author) {
            $user = User::query()->where('student_number', $author->student_number)->first();

            StudentCapstoneAuthor::create([
                'student_capstone_submission_id' => $submission->id,
                'student_masterlist_record_id' => $author->id,
                'user_id' => $user?->id,
                'is_leader' => $user?->is($submitter) ?? false,
            ]);
        }

        return $submission;
    }

    /**
     * @param  array<int, int>  $authors
     * @return array<string, mixed>
     */
    private function submissionPayload(User $adviser, array $authors, string $title, string $abstract): array
    {
        return [
            'title' => $title,
            'abstract' => $abstract,
            'adviser' => $adviser->id,
            'submitted_date' => '2026-11-11',
            'authors' => $authors,
            'study' => UploadedFile::fake()->create('study.pdf', 10, 'application/pdf'),
            'manual' => UploadedFile::fake()->create('manual.pdf', 10, 'application/pdf'),
            'system' => UploadedFile::fake()->create('system.zip', 10, 'application/zip'),
            'mentee_form' => UploadedFile::fake()->create('mentee.pdf', 10, 'application/pdf'),
        ];
    }
}
