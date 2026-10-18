<?php

namespace Tests\Feature;

use App\Models\CapstoneProject;
use App\Models\Classroom;
use App\Models\GroupRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimilarityCheckerResultStructureTest extends TestCase
{
    use RefreshDatabase;

    protected User $testUser;

    protected Classroom $testClassroom;

    protected GroupRoom $testGroupRoom;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testUser = User::create([
            'first_name' => 'Similarity',
            'last_name' => 'Result Test',
            'email' => 'similarity-result-' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'instructor',
            'program' => 'Information Technology',
            'year_section' => 'Test',
            'is_approved' => true,
        ]);

        $this->testClassroom = Classroom::create([
            'instructor_id' => $this->testUser->id,
            'classroom_name' => 'Similarity Result Test Classroom',
            'class_code' => 'SIM-RESULT-' . uniqid(),
        ]);

        $this->testGroupRoom = GroupRoom::create([
            'classroom_id' => $this->testClassroom->id,
            'group_name' => 'Similarity Result Test Group',
        ]);

        $this->actingAs($this->testUser);

    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_similarity_checker_returns_complete_similarity_result_structure(): void
    {
        $this->createApprovedStudy();

        $response = $this->post(
            route('similarity.check'),
            [
                'title' => 'Student Attendance Monitoring System',
                'abstract' =>
                    'This study proposes the development of a '
                    . 'student attendance monitoring system for '
                    . 'educational institutions. The system will '
                    . 'allow teachers to record, monitor, and manage '
                    . 'student attendance electronically. It will '
                    . 'provide attendance reports and help '
                    . 'instructors identify students who frequently '
                    . 'miss classes.',
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

        $results = $response->viewData('results');

        $this->assertNotEmpty($results);

        foreach ($results as $result) {
            $this->assertIsArray($result);

            $this->assertArrayHasKey('id', $result);
            $this->assertArrayHasKey('title', $result);
            $this->assertArrayHasKey('abstract', $result);
            $this->assertArrayHasKey('similarity', $result);

            $similarity = $result['similarity'];

            $this->assertIsArray($similarity);

            $this->assertArrayHasKey('lexical_similarity', $similarity);
            $this->assertArrayHasKey('lexical_percentage', $similarity);
            $this->assertArrayHasKey('semantic_similarity', $similarity);
            $this->assertArrayHasKey('semantic_percentage', $similarity);
            $this->assertArrayHasKey('hybrid_similarity', $similarity);
            $this->assertArrayHasKey('hybrid_percentage', $similarity);
            $this->assertArrayHasKey('shared_terms', $similarity);
            $this->assertIsArray($similarity['shared_terms']);

            $this->assertArrayHasKey('dss', $similarity);

            $this->assertIsArray($similarity['dss']);

            $this->assertArrayHasKey('level', $similarity['dss']);
            $this->assertArrayHasKey('title', $similarity['dss']);
            $this->assertArrayHasKey('message', $similarity['dss']);
            $this->assertArrayHasKey(
                'areas_to_review',
                $similarity['dss']
            );

            $this->assertIsArray(
                $similarity['dss']['areas_to_review']
            );

            $this->assertArrayHasKey(
                'recommendation',
                $similarity['dss']
            );

            $this->assertArrayHasKey(
                'semantic_available',
                $similarity
            );

            $this->assertIsBool(
                $similarity['semantic_available']
            );
        }
    }

    public function test_similarity_percentages_are_valid_ranges(): void
    {
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

        $results = $response->viewData('results');

        $this->assertNotEmpty($results);

        foreach ($results as $result) {
            $similarity = $result['similarity'];

            $this->assertGreaterThanOrEqual(
                0,
                $similarity['lexical_percentage']
            );

            $this->assertLessThanOrEqual(
                100,
                $similarity['lexical_percentage']
            );

            $this->assertGreaterThanOrEqual(
                0,
                $similarity['semantic_percentage']
            );

            $this->assertLessThanOrEqual(
                100,
                $similarity['semantic_percentage']
            );

            $this->assertGreaterThanOrEqual(
                0,
                $similarity['hybrid_percentage']
            );

            $this->assertLessThanOrEqual(
                100,
                $similarity['hybrid_percentage']
            );
        }
    }

    public function test_similarity_checker_returns_dss_review_areas(): void
    {
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

        $results = $response->viewData('results');

        $this->assertNotEmpty($results);

        $foundDssAreas = false;

        foreach ($results as $result) {
            $dss = $result['similarity']['dss'];

            if (! empty($dss['areas_to_review'])) {
                $foundDssAreas = true;

                foreach ($dss['areas_to_review'] as $area) {
                    $this->assertIsString($area);
                    $this->assertNotSame('', trim($area));
                }
            }
        }

        $this->assertTrue(
            $foundDssAreas,
            'At least one similarity result should provide DSS review areas.'
        );
    }

    public function test_similarity_checker_returns_expected_repository_study(): void
    {
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

        $results = $response->viewData('results');

        $this->assertNotEmpty($results);

        $matchingResult = collect($results)->first(
            fn (array $result): bool =>
                $result['title']
                === 'Digital Classroom Attendance Tracking System'
        );

        $this->assertNotNull($matchingResult);

        $this->assertArrayHasKey('similarity', $matchingResult);

        $similarity = $matchingResult['similarity'];

        $this->assertArrayHasKey('hybrid_percentage', $similarity);

        $this->assertGreaterThan(
            0,
            $similarity['hybrid_percentage']
        );

        $this->assertArrayHasKey('shared_terms', $similarity);

        $this->assertContains(
            'attendance',
            $similarity['shared_terms']
        );
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