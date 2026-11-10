<?php

namespace Tests\Feature\Auth;

use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\User;
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
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
        ]);

        $classroom = Classroom::create([
            'instructor_id' => $instructor->id,
            'classroom_name' => 'Registration Test Class',
            'class_code' => 'REG-TEST-001',
        ]);

        $response = $this->post('/register', [
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'test@example.com',
            'program' => 'BSIT',
            'year_section' => '1-A',
            'instructor_id' => $instructor->id,
            'class_code' => $classroom->class_code,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();

        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'first_name' => 'Test',
            'last_name' => 'Student',
            'role' => 'student',
            'is_approved' => false,
        ]);

        $this->assertDatabaseHas('classroom_members', [
            'classroom_id' => $classroom->id,
            'user_id' => User::where('email', 'test@example.com')->value('id'),
            'status' => 'pending',
        ]);
    }
}