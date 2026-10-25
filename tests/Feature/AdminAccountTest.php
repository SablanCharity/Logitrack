<?php

namespace Tests\Feature;

use App\Mail\StaffAccountCreated;
use App\Models\ArchivedCapstone;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_an_instructor_account(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.accounts.store'), [
            'first_name' => 'Jane',
            'last_name' => 'Instructor',
            'email' => 'jane.instructor@example.com',
            'role' => 'instructor',
            'program' => 'BSCS',
        ]);

        $response->assertRedirect(route('admin.accounts.index'))
            ->assertSessionHas('success', 'Instructor account created and credentials emailed successfully.');
        $this->assertDatabaseHas('users', [
            'email' => 'jane.instructor@example.com',
            'role' => 'instructor',
            'is_approved' => true,
            'must_change_password' => true,
        ]);
        Mail::assertSent(StaffAccountCreated::class, fn (StaffAccountCreated $mail): bool => $mail->email === 'jane.instructor@example.com'
            && $mail->role === 'instructor'
            && strlen($mail->temporaryPassword) === 24);
    }

    public function test_admin_can_create_an_ojt_account(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.accounts.store'), [
            'first_name' => 'John',
            'last_name' => 'OJT',
            'email' => 'john.ojt@example.com',
            'role' => 'ojt',
        ])->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'john.ojt@example.com',
            'role' => 'ojt',
            'is_approved' => true,
        ]);
    }

    public function test_admin_cannot_create_an_admin_account(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.accounts.create'))
            ->post(route('admin.accounts.store'), [
                'first_name' => 'New',
                'last_name' => 'Administrator',
                'email' => 'new.admin@example.com',
                'role' => 'admin',
            ])->assertRedirect(route('admin.accounts.create'))
            ->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'new.admin@example.com']);
        Mail::assertNothingSent();
    }

    public function test_admin_is_warned_when_email_domain_cannot_receive_mail(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.accounts.create'))
            ->post(route('admin.accounts.store'), [
                'first_name' => 'Invalid',
                'last_name' => 'Mailbox',
                'email' => 'invalid@definitely-no-such-domain.invalid',
                'role' => 'ojt',
            ])
            ->assertRedirect(route('admin.accounts.create'))
            ->assertSessionHasErrors('email');

        Mail::assertNothingSent();
        $this->assertDatabaseMissing('users', ['email' => 'invalid@definitely-no-such-domain.invalid']);
    }

    public function test_admin_can_edit_staff_details_and_new_email_receives_new_credentials(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create([
            'role' => 'instructor',
            'email' => 'old.instructor@example.com',
        ]);

        $this->actingAs($admin)
            ->put(route('admin.accounts.update', $staff), [
                'first_name' => 'Updated',
                'last_name' => 'Instructor',
                'email' => 'updated.instructor@example.com',
                'program' => 'BSCS',
            ])
            ->assertRedirect(route('admin.accounts.index'));

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'first_name' => 'Updated',
            'email' => 'updated.instructor@example.com',
        ]);
        Mail::assertSent(StaffAccountCreated::class, fn (StaffAccountCreated $mail): bool => $mail->email === 'updated.instructor@example.com');
    }

    public function test_admin_can_disable_and_delete_staff_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'instructor', 'is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.accounts.toggle-active', $staff))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $staff->id, 'is_active' => false]);

        $this->actingAs($admin)
            ->delete(route('admin.accounts.destroy', $staff))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
        ]);
        $this->assertNotNull($staff->fresh()->deleted_at);
    }

    public function test_admin_can_view_students_with_their_adviser_and_disable_them(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $student = User::factory()->create([
            'role' => 'student',
            'is_approved' => true,
            'is_active' => true,
            'program' => 'BSCS',
            'year_section' => '4A',
        ]);
        $classroom = Classroom::create([
            'instructor_id' => $instructor->id,
            'classroom_name' => 'Adviser Class',
            'class_code' => 'ADV-001',
        ]);
        ClassroomMember::create([
            'classroom_id' => $classroom->id,
            'user_id' => $student->id,
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.students.index'))
            ->assertOk()
            ->assertSee('name="q"', false)
            ->assertSee('material-symbols-outlined lt-input-icon', false)
            ->assertSee($student->email)
            ->assertSee($instructor->first_name.' '.$instructor->last_name);

        $this->actingAs($admin)
            ->get(route('admin.accounts.index'))
            ->assertOk()
            ->assertSee('name="q"', false)
            ->assertSee('Search staff accounts...');

        $this->actingAs($admin)
            ->patch(route('admin.accounts.toggle-active', $student))
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $student->id, 'is_active' => false]);
    }

    public function test_admin_account_search_only_renders_matching_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $matching = User::factory()->create([
            'role' => 'instructor',
            'first_name' => 'Searchable',
            'email' => 'searchable@example.com',
        ]);
        $other = User::factory()->create([
            'role' => 'ojt',
            'first_name' => 'Unrelated',
            'email' => 'unrelated@example.com',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.accounts.index', ['q' => 'searchable']))
            ->assertOk()
            ->assertSee($matching->email)
            ->assertDontSee($other->email);

        $this->actingAs($admin)
            ->get(route('admin.accounts.index', ['q' => 'missing-account']))
            ->assertOk()
            ->assertSee('No match found');
    }

    public function test_disabled_accounts_cannot_log_in(): void
    {
        $user = User::factory()->create([
            'role' => 'instructor',
            'is_active' => false,
            'password' => bcrypt('password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors([
            'email' => 'Your account is disabled. If you think this is a mistake, please contact the department admin.',
        ]);
    }

    public function test_new_instructor_is_sent_to_profile_once_to_change_password(): void
    {
        $user = User::factory()->create([
            'role' => 'instructor',
            'is_active' => true,
            'must_change_password' => true,
            'password' => bcrypt('password'),
        ]);

        $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('profile.edit'));

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'must_change_password' => false]);
    }

    public function test_non_admin_cannot_create_staff_accounts(): void
    {
        $ojt = User::factory()->create(['role' => 'ojt']);

        $this->actingAs($ojt)
            ->get(route('admin.accounts.create'))
            ->assertForbidden();
    }

    public function test_ojt_can_review_metadata_but_cannot_approve_archived_capstones(): void
    {
        $ojt = User::factory()->create(['role' => 'ojt']);
        $archivedCapstone = ArchivedCapstone::create([
            'title' => 'OCR Study',
            'status' => 'pending',
        ]);

        $this->actingAs($ojt)
            ->get(route('ocr.review', $archivedCapstone))
            ->assertOk()
            ->assertDontSee('Approve &amp; Push to Repository');

        $this->actingAs($ojt)
            ->patch(route('ocr.approve', $archivedCapstone))
            ->assertForbidden();
    }

    public function test_ojt_cannot_upload_or_change_an_approved_archived_capstone(): void
    {
        $ojt = User::factory()->create(['role' => 'ojt']);
        $archivedCapstone = ArchivedCapstone::create([
            'title' => 'Approved OCR Study',
            'status' => 'approved',
        ]);

        $this->actingAs($ojt)
            ->get(route('ocr.upload'))
            ->assertForbidden();

        $this->actingAs($ojt)
            ->put(route('ocr.update', $archivedCapstone), [
                'title' => 'Changed Study',
            ])
            ->assertForbidden();
    }
}
