<?php

namespace Tests\Feature\Auth;

use App\Models\StudentMasterlistRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        Mail::fake();
        StudentMasterlistRecord::create([
            'student_number' => '2026-0001',
            'name' => 'Official Name',
            'program' => 'BSCS',
            'status' => 'listed',
        ]);

        $response = $this->post('/register', [
            'student_number' => '2026-0001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'program' => 'BSCS',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('registration.otp'));
        $this->assertGuest();

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'student_number' => '2026-0001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'role' => 'student',
            'is_active' => true,
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();
        $user->forceFill([
            'registration_otp_hash' => Hash::make('000000'),
            'registration_otp_expires_at' => now()->addMinutes(10),
        ])->save();
        $this->withSession(['registration_user_id' => $user->id])
            ->post('/register/verify-email', ['otp' => '000000']);

        $this->assertAuthenticatedAs($user->fresh());
    }

    public function test_landing_registration_form_can_start_otp_verification(): void
    {
        Mail::fake();
        StudentMasterlistRecord::create([
            'student_number' => '2026-0002',
            'name' => 'Landing Student',
            'program' => 'BSIT',
            'status' => 'listed',
        ]);

        $response = $this->post('/register', [
            '_form' => 'register',
            'student_number' => '2026-0002',
            'first_name' => 'Landing',
            'last_name' => 'Student',
            'program' => 'BSIT',
            'email' => 'landing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('registration.otp'));
        $response->assertSessionHas('registration_user_id');
    }
}
