<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\GroupMember;
use App\Models\GroupRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupRoomVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_only_groups_they_belong_to(): void
    {
        $student = User::factory()->create([
            'role' => 'student',
            'is_approved' => true,
        ]);
        $classroom = Classroom::create([
            'instructor_id' => User::factory()->create(['role' => 'instructor'])->id,
            'classroom_name' => 'Computer Science',
            'class_code' => 'CS-001',
        ]);
        ClassroomMember::create([
            'classroom_id' => $classroom->id,
            'user_id' => $student->id,
            'status' => 'approved',
        ]);
        $visibleGroup = GroupRoom::create([
            'classroom_id' => $classroom->id,
            'group_name' => 'Visible Group',
        ]);
        GroupMember::create([
            'group_room_id' => $visibleGroup->id,
            'user_id' => $student->id,
            'is_leader' => true,
        ]);
        GroupRoom::create([
            'classroom_id' => $classroom->id,
            'group_name' => 'Other Group',
        ]);

        $this->actingAs($student)
            ->get(route('student.group-rooms.index', $classroom))
            ->assertOk()
            ->assertSee('Visible Group')
            ->assertDontSee('Other Group')
            ->assertDontSee('Search group name...');
    }
}
