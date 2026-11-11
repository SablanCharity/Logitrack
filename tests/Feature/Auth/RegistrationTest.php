<?php

namespace Tests\Feature\Auth;

use App\Models\StudentMasterlistRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        StudentMasterlistRecord::create([
            'student_number' => '202600001',
            'name' => 'Test Student',
            'program' => 'BSIT',
        ]);

        $response = $this->post('/register', [
            'student_number' => '202600001',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

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
    }

    public function test_registration_requires_a_masterlist_student_number(): void
    {
        $response = $this->from('/register')->post('/register', [
            'student_number' => 'missing',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/register')->assertSessionHasErrors('student_number');
        $this->assertGuest();
    }
}
