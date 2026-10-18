<?php

namespace Tests\Feature;

use App\Models\CapstoneProject;
use App\Models\Classroom;
use App\Models\GroupRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimilarityCheckerEndpointTest extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;

    protected Classroom $testClassroom;

    protected GroupRoom $testGroupRoom;

    protected GroupRoom $secondaryGroupRoom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUser = User::create([
            'first_name' => 'Similarity',
            'last_name' => 'HTTP Test',
            'email' => 'similarity-http-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'instructor',
            'program' => 'Information Technology',
            'year_section' => 'Test',
            'is_approved' => true,
        ]);

        $this->testClassroom = Classroom::create([
            'instructor_id' => $this->testUser->id,
            'classroom_name' => 'Similarity HTTP Test Classroom',
            'class_code' => 'SIM-HTTP-' . uniqid(),
        ]);

        $this->testGroupRoom = GroupRoom::create([
            'classroom_id' => $this->testClassroom->id,
            'group_name' => 'Similarity HTTP Approved Group',
        ]);

        $this->secondaryGroupRoom = GroupRoom::create([
            'classroom_id' => $this->testClassroom->id,
            'group_name' => 'Similarity HTTP Draft Group',
        ]);

        $this->actingAs($this->testUser);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_similarity_checker_page_is_accessible(): void
    {
        $response = $this->get(route('similarity.index'));

        $response->assertSuccessful();
        $response->assertViewIs('similarity.index');
    }

    public function test_similarity_checker_accepts_valid_proposed_study(): void
    {
        $studyBeforeRequest = CapstoneProject::query()
            ->where(
                'status',
                config('similarity.repository_status', 'admin_approved')
            )
            ->count();

        $this->createApprovedStudy();

        $response = $this->post(
            route('similarity.check'),
            [
                'title' => 'Student Attendance Monitoring System',
                'abstract' =>
                    'This study proposes a digital system for '
                    . 'monitoring student attendance and generating '
                    . 'attendance reports for teachers.',
            ]
        );

        $response->assertSuccessful();
        $response->assertViewIs('similarity.results');

        $response->assertViewHas(
            'proposedTitle',
            'Student Attendance Monitoring System'
        );

        $response->assertViewHas('proposedAbstract');
        $response->assertViewHas('results');

        $response->assertViewHas(
            'studyCount',
            $studyBeforeRequest + 1
        );

        $results = $response->viewData('results');

        $this->assertNotEmpty($results);

        $titles = array_column($results, 'title');

        $this->assertContains(
            'Digital Classroom Attendance Tracking System',
            $titles
        );
    }

    public function test_similarity_checker_uses_approved_repository_studies_only(): void
    {
        $studyBeforeRequest = CapstoneProject::query()
            ->where(
                'status',
                config('similarity.repository_status', 'admin_approved')
            )
            ->count();

        $this->createApprovedStudy();

        CapstoneProject::create([
            'group_room_id' => $this->secondaryGroupRoom->id,
            'submitted_by' => $this->testUser->id,
            'title' => 'Draft Attendance System',
            'abstract' =>
                'A draft system for managing student attendance.',
            'program' => 'Information Technology',
            'year_section' => 'Test',
            'status' => 'draft',
        ]);

        $response = $this->post(
            route('similarity.check'),
            [
                'title' => 'Student Attendance Monitoring System',
                'abstract' =>
                    'This study proposes a digital system for '
                    . 'monitoring student attendance and generating '
                    . 'attendance reports for teachers.',
            ]
        );

        $response->assertSuccessful();

        $response->assertViewHas(
            'studyCount',
            $studyBeforeRequest + 1
        );

        $results = $response->viewData('results');

        $this->assertNotEmpty($results);

        $titles = array_column($results, 'title');

        $this->assertContains(
            'Digital Classroom Attendance Tracking System',
            $titles
        );

        $this->assertNotContains(
            'Draft Attendance System',
            $titles
        );
    }

    public function test_similarity_checker_rejects_missing_title(): void
    {
        $response = $this->post(
            route('similarity.check'),
            [
                'abstract' =>
                    'This is a valid research abstract with enough text.',
            ]
        );

        $response->assertSessionHasErrors('title');
    }

    public function test_similarity_checker_rejects_short_abstract(): void
    {
        $response = $this->post(
            route('similarity.check'),
            [
                'title' => 'Test Study',
                'abstract' => 'Too short',
            ]
        );

        $response->assertSessionHasErrors('abstract');
    }

    protected function createApprovedStudy(): CapstoneProject
    {

        return CapstoneProject::create([
            'group_room_id' => $this->testGroupRoom->id,
            'submitted_by' => $this->testUser->id,
            'approved_by' => $this->testUser->id,
            'title' =>
                'Digital Classroom Attendance Tracking System',
            'abstract' =>
                'This project develops a digital attendance '
                . 'tracking system for schools that enables '
                . 'instructors to record student attendance '
                . 'electronically. The system maintains attendance '
                . 'records, generates reports, and helps teachers '
                . 'monitor students with frequent absences.',
            'program' => 'Information Technology',
            'year_section' => 'Test',
            'status' => 'admin_approved',
            'submitted_at' => now(),
            'approved_at' => now(),
        ]);
    }
}