<?php

namespace Tests\Feature;

use App\Models\GroupCapstoneMember;
use App\Models\GroupCapstoneSubmission;
use App\Models\StudentCapstoneAuthor;
use App\Models\StudentCapstoneSubmission;
use App\Models\StudentMasterlistRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class StudentMasterlistControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_masterlist_renders_after_missing_submission_tables_are_restored(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        StudentMasterlistRecord::create([
            'student_number' => '202600901',
            'name' => 'Masterlist Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);

        Schema::dropIfExists('group_capstone_members');
        Schema::dropIfExists('group_capstone_submissions');

        $migration = require database_path('migrations/2026_11_11_071308_restore_missing_group_capstone_tables.php');
        $migration->up();

        $this->assertTrue(Schema::hasTable('group_capstone_submissions'));
        $this->assertTrue(Schema::hasTable('group_capstone_members'));

        Schema::dropIfExists('student_capstone_authors');

        $authorsMigration = require database_path('migrations/2026_11_11_072831_repair_missing_student_capstone_authors_table.php');
        $authorsMigration->up();

        $this->assertTrue(Schema::hasTable('student_capstone_authors'));

        $this->actingAs($admin)
            ->get(route('admin.student-masterlist.index'))
            ->assertOk()
            ->assertSee('href="'.route('admin.accounts.index').'"', false)
            ->assertSee('href="'.route('admin.student-masterlist.index').'"', false)
            ->assertSee('202600901')
            ->assertSee('Masterlist Student');
    }

    public function test_masterlist_shows_returned_status_for_individual_and_group_authors(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $individualStudent = User::factory()->create([
            'student_number' => '202600902',
            'program' => 'BSCS',
        ]);
        $individualRecord = StudentMasterlistRecord::create([
            'student_number' => '202600902',
            'name' => 'Returned Individual',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);
        $individualSubmission = StudentCapstoneSubmission::create([
            'submitted_by' => $individualStudent->id,
            'title' => 'Returned Individual Capstone',
            'abstract' => 'An individual abstract.',
            'submitted_date' => '2026-11-11',
            'study_path' => 'individual/study.pdf',
            'manual_path' => 'individual/manual.pdf',
            'system_path' => 'individual/system.zip',
            'mentee_form_path' => 'individual/mentee.pdf',
            'status' => 'returned',
        ]);
        StudentCapstoneAuthor::create([
            'student_capstone_submission_id' => $individualSubmission->id,
            'student_masterlist_record_id' => $individualRecord->id,
            'user_id' => $individualStudent->id,
            'is_leader' => true,
        ]);

        $groupStudent = User::factory()->create([
            'student_number' => '202600903',
            'program' => 'BSIT',
        ]);
        $groupRecord = StudentMasterlistRecord::create([
            'student_number' => '202600903',
            'name' => 'Returned Group Member',
            'program' => 'BSIT',
            'status' => 'listed',
        ]);
        $groupSubmission = GroupCapstoneSubmission::create([
            'submitted_by' => $groupStudent->id,
            'program' => 'BSIT',
            'title' => 'Returned Group Capstone',
            'abstract' => 'A group abstract.',
            'adviser' => 'Group Adviser',
            'submitted_date' => '2026-11-11',
            'study_path' => 'group/study.pdf',
            'system_path' => 'group/system.zip',
            'document_path' => 'group/document.docx',
            'mentee_form_path' => 'group/mentee.pdf',
            'content_hash' => hash('sha256', 'returned-group-capstone'),
            'status' => 'returned',
        ]);
        GroupCapstoneMember::create([
            'group_capstone_submission_id' => $groupSubmission->id,
            'student_id' => $groupStudent->id,
            'student_masterlist_record_id' => $groupRecord->id,
            'is_leader' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.student-masterlist.index', ['search' => '202600902']))
            ->assertOk()
            ->assertSee('Returned Individual')
            ->assertSee('Returned Capstone')
            ->assertDontSee('Submitted Capstone');

        $this->get(route('admin.student-masterlist.index', ['search' => '202600903']))
            ->assertOk()
            ->assertSee('Returned Group Member')
            ->assertSee('Returned Capstone')
            ->assertDontSee('Submitted Capstone');
    }

    public function test_duplicate_masterlist_records_display_a_warning(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        StudentMasterlistRecord::create([
            'student_number' => '202600904',
            'name' => 'Existing Student',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.student-masterlist.upload'), [
            'program' => 'BSCS',
            'masterlist' => UploadedFile::fake()->createWithContent(
                'students.csv',
                "student_number,name\n202600904,Existing Student\n202600905,New Student\n202600905,New Student Duplicate\n",
            ),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('warning', '2 duplicate student records detected. Existing records were updated.');
        $this->assertDatabaseCount('student_masterlist_records', 2);

        $this->get(route('admin.student-masterlist.index'))
            ->assertOk()
            ->assertSee('2 duplicate student records detected. Existing records were updated.');
    }
}
