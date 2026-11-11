<?php

namespace Tests\Feature\Auth;

use App\Mail\StudentRegistrationOtp;
use App\Models\StudentMasterlistRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertOk()->assertSee('Confirm Password');
        $this->get('/')->assertOk()->assertSee('name="password_confirmation"', false);
    }

    public function test_registration_otp_email_contains_the_code_and_expiration(): void
    {
        $content = (new StudentRegistrationOtp('123456'))->render();

        $this->assertStringContainsString('123456', $content);
        $this->assertStringContainsString('expires in 10 minutes', $content);
    }

    public function test_new_users_can_register(): void
    {
        StudentMasterlistRecord::create([
            'student_number' => '202600001',
            'name' => 'Test Student',
            'program' => 'BSIT',
        ]);

        Mail::fake();

        $response = $this->post('/register', [
            'student_number' => '202600001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('register.otp'));
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
        $this->get(route('register.otp'))->assertOk()->assertSee('test@example.com');

        $otp = null;
        Mail::assertSent(StudentRegistrationOtp::class, function (StudentRegistrationOtp $mail) use (&$otp): bool {
            $otp = $mail->code;

            return $mail->hasTo('test@example.com');
        });
        $this->assertNotNull($otp);

        $response = $this->post(route('register.otp.store'), ['otp' => $otp]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'student_number' => '202600001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'role' => 'student',
            'is_approved' => false,
            'program' => 'BSIT',
        ]);
        $this->assertTrue(auth()->user()->hasVerifiedEmail());
    }

    public function test_registration_requires_matching_password_confirmation(): void
    {
        StudentMasterlistRecord::create([
            'student_number' => '202600002',
            'name' => 'Test Student',
            'program' => 'BSIT',
        ]);

        Mail::fake();

        $response = $this->from('/register')->post('/register', [
            'student_number' => '202600002',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertRedirect('/register')->assertSessionHasErrors('password');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
        Mail::assertNothingSent();
    }

    public function test_incorrect_otp_does_not_create_an_account(): void
    {
        StudentMasterlistRecord::create([
            'student_number' => '202600003',
            'name' => 'Test Student',
            'program' => 'BSIT',
        ]);

        Mail::fake();

        $this->post('/register', [
            'student_number' => '202600003',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register.otp'));

        $response = $this->from(route('register.otp'))->post(route('register.otp.store'), ['otp' => '000000']);

        $response->assertRedirect(route('register.otp'))->assertSessionHasErrors('otp');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_five_incorrect_otp_attempts_expire_the_registration(): void
    {
        StudentMasterlistRecord::create([
            'student_number' => '202600005',
            'name' => 'Test Student',
            'program' => 'BSIT',
        ]);

        Mail::fake();

        $this->post('/register', [
            'student_number' => '202600005',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register.otp'));

        foreach (range(1, 5) as $attempt) {
            $response = $this->from(route('register.otp'))->post(route('register.otp.store'), ['otp' => '000000']);
        }

        $response->assertRedirect(route('register'))->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_expired_otp_requires_registration_to_be_restarted(): void
    {
        StudentMasterlistRecord::create([
            'student_number' => '202600004',
            'name' => 'Test Student',
            'program' => 'BSIT',
        ]);

        Mail::fake();

        $this->post('/register', [
            'student_number' => '202600004',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('register.otp'));

        $this->travel(11)->minutes();

        $this->get(route('register.otp'))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    public function test_registration_requires_a_masterlist_student_number(): void
    {
        $response = $this->from('/register')->post('/register', [
            'student_number' => 'missing',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/register')->assertSessionHasErrors('student_number');
        $this->assertGuest();
    }
}
