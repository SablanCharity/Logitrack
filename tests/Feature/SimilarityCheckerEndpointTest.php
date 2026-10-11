<?php

namespace Tests\Feature;

use App\Models\CapstoneProject;
use App\Models\Classroom;
use App\Models\GroupRoom;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;

use Tests\TestCase;

class SimilarityCheckerEndpointTest extends TestCase
{
    protected User $testUser;

    protected Classroom $testClassroom;

    protected GroupRoom $testGroupRoom;

    protected GroupRoom $secondaryGroupRoom;

    protected string $originalDefaultConnection;

    protected ?ConnectionResolverInterface $originalConnectionResolver = null;

    protected ?Dispatcher $originalEventDispatcher = null;

    protected function ensureEloquentIsConnected(): void
    {
        // Restore Laravel's facade application after other tests run.
        Facade::setFacadeApplication($this->app);
        Facade::clearResolvedInstance('db');

        // Explicitly restore the database manager and Eloquent state.
        config(['database.default' => 'mysql']);

        $databaseManager = $this->app->make('db');
        $databaseManager->setDefaultConnection('mysql');

        Model::setConnectionResolver($databaseManager);
        Model::setEventDispatcher($this->app->make('events'));
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalDefaultConnection = config(
            'database.default',
            'sqlite'
        );

        $this->originalConnectionResolver = Model::getConnectionResolver();
        $this->originalEventDispatcher = Model::getEventDispatcher();

        config([
            'database.default' => 'mysql',
            'database.connections.mysql.driver' => 'mysql',
            'database.connections.mysql.host' => '127.0.0.1',
            'database.connections.mysql.port' => 3306,
            'database.connections.mysql.database' => 'logitrack',
            'database.connections.mysql.username' => 'root',
            'database.connections.mysql.password' => '',
        ]);

        DB::purge('mysql');
        DB::setDefaultConnection('mysql');
        DB::reconnect('mysql');

        Model::setConnectionResolver($this->app['db']);
        Model::setEventDispatcher($this->app['events']);

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
        try {
            if (isset($this->testGroupRoom)) {
                DB::connection('mysql')
                    ->table('capstone_projects')
                    ->where('group_room_id', $this->testGroupRoom->id)
                    ->delete();

                $this->testGroupRoom->delete();
            }

            if (isset($this->secondaryGroupRoom)) {
                DB::connection('mysql')
                    ->table('capstone_projects')
                    ->where('group_room_id', $this->secondaryGroupRoom->id)
                    ->delete();

                $this->secondaryGroupRoom->delete();
            }

            if (isset($this->testClassroom)) {
                $this->testClassroom->delete();
            }

            if (isset($this->testUser)) {
                $this->testUser->delete();
            }
        } finally {
            DB::setDefaultConnection($this->originalDefaultConnection);
            DB::purge('mysql');

            Model::setConnectionResolver($this->originalConnectionResolver);
            Model::setEventDispatcher($this->originalEventDispatcher);

            parent::tearDown();
        }
    }

    public function test_similarity_checker_page_is_accessible(): void
    {
        $response = $this->get(route('similarity.index'));

        $response->assertSuccessful();
        $response->assertViewIs('similarity.index');
    }

    public function test_similarity_checker_accepts_valid_proposed_study(): void
    {
        $this->ensureEloquentIsConnected();

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
        $this->ensureEloquentIsConnected();

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
        $this->ensureEloquentIsConnected();

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