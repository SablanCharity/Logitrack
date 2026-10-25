<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\GroupMember;
use App\Models\GroupRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CapstoneSubmissionWindowTest extends TestCase
{
    use RefreshDatabase;

    public function test_students_cannot_submit_when_admin_has_closed_submissions(): void
    {
        [$student, $groupRoom] = $this->createStudentGroup();

        $this->actingAs($student)
            ->get(route('student.capstone.create', $groupRoom))
            ->assertRedirect(route('student.group-room.show', $groupRoom))
            ->assertSessionHas('error');

        $this->actingAs($student)
            ->post(route('student.capstone.store', $groupRoom), [
                'title' => 'Closed Study',
                'abstract' => 'This study cannot be submitted while closed.',
                'publication_date' => '2026-10-25',
            ])
            ->assertSessionHas('error');
    }

    public function test_admin_can_open_submissions_and_students_can_submit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        [$student, $groupRoom] = $this->createStudentGroup();

        $this->actingAs($admin)
            ->get(route('admin.capstones'))
            ->assertOk()
            ->assertSee('Open Capstone Submissions');

        $this->actingAs($admin)
            ->patch(route('admin.capstones.submission-window'))
            ->assertRedirect()
            ->assertSessionHas('success', 'Capstone submissions are now open.');

        $this->actingAs($student)
            ->post(route('student.capstone.store', $groupRoom), [
                'title' => 'Open Study',
                'abstract' => 'This study can be submitted while open.',
                'publication_date' => '2026-10-25',
            ])
            ->assertRedirect(route('student.group-room.show', $groupRoom));
    }

    /**
     * @return array{0: User, 1: GroupRoom}
     */
    private function createStudentGroup(): array
    {
        $student = User::factory()->create([
            'role' => 'student',
            'is_approved' => true,
            'program' => 'BSCS',
            'year_section' => '4A',
        ]);
        $instructor = User::factory()->create(['role' => 'instructor']);
        $classroom = Classroom::create([
            'instructor_id' => $instructor->id,
            'classroom_name' => 'Computer Science',
            'class_code' => 'CS-'.fake()->unique()->numerify('###'),
        ]);
        ClassroomMember::create([
            'classroom_id' => $classroom->id,
            'user_id' => $student->id,
            'status' => 'approved',
        ]);
        $groupRoom = GroupRoom::create([
            'classroom_id' => $classroom->id,
            'group_name' => 'Research Group',
        ]);
        GroupMember::create([
            'group_room_id' => $groupRoom->id,
            'user_id' => $student->id,
            'is_leader' => true,
        ]);

        return [$student, $groupRoom];
    }
}
