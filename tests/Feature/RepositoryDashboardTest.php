<?php

namespace Tests\Feature;

use App\Models\CapstoneProject;
use App\Models\Classroom;
use App\Models\GroupRoom;
use App\Models\ArchivedCapstone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RepositoryDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_metrics_and_featured_studies(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $this->createStudy('Best Study', isBest: true);
        $this->createStudy('Most Viewed Study', viewsCount: 12);
        $this->createStudy('Unapproved Study', viewsCount: 50, status: 'submitted');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk()
            ->assertSee('LogiTrack Capstone Repository')
            ->assertSee('Total Number of Capstone Studies')
            ->assertSee('2')
            ->assertSee('Best Study')
            ->assertSee('Most Viewed Study')
            ->assertSee('12 views')
            ->assertDontSee('Unapproved Study');
    }

    public function test_ojt_can_access_dashboard_repository_and_capstone_reviewer_pages(): void
    {
        $ojt = User::factory()->create(['role' => 'ojt']);
        $study = $this->createStudy('Study Awaiting Review', status: 'instructor_verified');

        $this->actingAs($ojt)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Repository')
            ->assertSee('Capstone Review');

        $this->actingAs($ojt)
            ->get(route('repository.index'))
            ->assertOk();

        $this->actingAs($ojt)
            ->get(route('admin.capstones'))
            ->assertOk()
            ->assertSee('Capstone Approval');

        $this->actingAs($ojt)
            ->get(route('admin.capstone.review', ['capstoneId' => $study->id]))
            ->assertOk()
            ->assertSee('Admin Review')
            ->assertDontSee('Approve for Repository')
            ->assertDontSee('Return for Correction');
    }

    public function test_dashboard_search_hides_overview_and_shows_five_results_per_page(): void
    {
        $user = User::factory()->create(['role' => 'student']);

        foreach (range(1, 7) as $number) {
            $this->createStudy("Searchable Study {$number}");
        }

        $response = $this->actingAs($user)
            ->get(route('dashboard', ['search' => 'Searchable']));

        $response->assertOk()
            ->assertSee('Search Results')
            ->assertDontSee('Best Capstone Study')
            ->assertDontSee('Total Number of Capstone Studies')
            ->assertDontSee('Most Viewed Capstone Study')
            ->assertDontSee('Featured study 1 of 2')
            ->assertSee('page=2');

        $this->assertSame(5, $response->viewData('capstones')->perPage());
        $this->assertCount(5, $response->viewData('capstones')->items());

        $secondPage = $this->get(route('dashboard', ['search' => 'Searchable', 'page' => 2]));

        $secondPage->assertOk();
        $this->assertSame(2, $secondPage->viewData('capstones')->currentPage());
        $this->assertCount(2, $secondPage->viewData('capstones')->items());
    }

    public function test_admin_can_set_one_approved_study_as_best(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $previousBest = $this->createStudy('Previous Best', isBest: true);
        $newBest = $this->createStudy('New Best');

        $response = $this->actingAs($admin)
            ->from(route('repository.index'))
            ->patch(route('admin.capstone.set-best', ['capstoneId' => $newBest->id]));

        $response->assertRedirect(route('repository.index'))
            ->assertSessionHas('success', 'Best capstone study updated.');
        $this->assertDatabaseHas('capstone_projects', [
            'id' => $previousBest->id,
            'is_best' => false,
        ]);
        $this->assertDatabaseHas('capstone_projects', [
            'id' => $newBest->id,
            'is_best' => true,
        ]);
    }

    public function test_admin_can_set_an_archived_study_as_best_and_dashboard_detects_most_viewed_study(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $bestStudy = ArchivedCapstone::create([
            'title' => 'Best Archived Study',
            'status' => 'approved',
        ]);
        ArchivedCapstone::create([
            'title' => 'Most Viewed Archived Study',
            'status' => 'approved',
            'views_count' => 15,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.capstone.set-best', [
                'capstoneId' => $bestStudy->id,
                'type' => 'old',
            ]))
            ->assertRedirect(route('repository.index'))
            ->assertSessionHas('success', 'Best capstone study updated.');

        $this->assertDatabaseHas('archived_capstones', [
            'id' => $bestStudy->id,
            'is_best' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Best Archived Study')
            ->assertSee('Most Viewed Archived Study')
            ->assertSee('15 views');
    }

    public function test_non_admin_cannot_set_best_study(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $study = $this->createStudy('Approved Study');

        $this->actingAs($student)
            ->patch(route('admin.capstone.set-best', ['capstoneId' => $study->id]))
            ->assertForbidden();
    }

    public function test_ojt_cannot_approve_capstones(): void
    {
        $ojt = User::factory()->create(['role' => 'ojt']);
        $study = $this->createStudy('Study Awaiting Review', status: 'instructor_verified');

        $this->actingAs($ojt)
            ->patch(route('admin.capstone.approve', ['capstoneId' => $study->id]))
            ->assertForbidden();
    }

    public function test_opening_approved_study_increments_its_view_count(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $study = $this->createStudy('Study to View');

        $response = $this->actingAs($user)->get(route('repository.show', $study));

        $response->assertOk()
            ->assertViewHas('backUrl', route('repository.index'));
        $this->assertDatabaseHas('capstone_projects', [
            'id' => $study->id,
            'views_count' => 1,
        ]);
    }

    public function test_opening_an_approved_archived_study_increments_its_view_count(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $study = ArchivedCapstone::create([
            'title' => 'Archived Study to View',
            'status' => 'approved',
        ]);

        $this->actingAs($user)
            ->get(route('repository.show', [
                'capstoneId' => $study->id,
                'type' => 'old',
            ]))
            ->assertOk();

        $this->assertDatabaseHas('archived_capstones', [
            'id' => $study->id,
            'views_count' => 1,
        ]);
    }

    public function test_study_detail_returns_to_dashboard_search_results(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $study = $this->createStudy('Search Result Study');

        $response = $this->actingAs($user)->get(route('repository.show', [
            'capstoneId' => $study->id,
            'return_to' => 'dashboard',
            'search' => 'Search Result',
            'year' => now()->year,
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertViewHas('backUrl', route('dashboard', [
                'search' => 'Search Result',
                'year' => now()->year,
                'page' => 2,
            ]));
    }

    public function test_instructor_study_detail_returns_to_instructor_repository_search_results(): void
    {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
        ]);
        $study = $this->createStudy('Instructor Search Result Study');

        $response = $this->actingAs($instructor)->get(route('instructor.repository.show', [
            'capstoneId' => $study->id,
            'type' => 'modern',
            'return_to' => 'instructor_repository',
            'search' => 'Instructor Search',
            'year' => now()->year,
            'page' => 2,
        ]));

        $response->assertOk()
            ->assertViewHas('backUrl', route('instructor.repository.index', [
                'search' => 'Instructor Search',
                'year' => now()->year,
                'page' => 2,
            ]));
    }

    private function createStudy(
        string $title,
        bool $isBest = false,
        int $viewsCount = 0,
        string $status = 'admin_approved'
    ): CapstoneProject {
        $instructor = User::factory()->create([
            'role' => 'instructor',
            'is_approved' => true,
        ]);
        $classroom = Classroom::create([
            'instructor_id' => $instructor->id,
            'classroom_name' => 'Capstone Class',
            'class_code' => fake()->unique()->lexify('??????'),
        ]);
        $groupRoom = GroupRoom::create([
            'classroom_id' => $classroom->id,
            'group_name' => 'Study Group',
        ]);

        return CapstoneProject::create([
            'group_room_id' => $groupRoom->id,
            'title' => $title,
            'abstract' => 'An approved capstone study abstract.',
            'program' => 'BSIT',
            'status' => $status,
            'approved_at' => now(),
            'publication_date' => now()->toDateString(),
            'is_best' => $isBest,
            'views_count' => $viewsCount,
            'verified_by' => $status === 'instructor_verified' ? $instructor->id : null,
            'verified_at' => $status === 'instructor_verified' ? now() : null,
        ]);
    }
}
