<?php

namespace Tests\Feature;

use App\Models\CapstoneProject;
use App\Models\Classroom;
use App\Models\GroupRoom;
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

    public function test_non_admin_cannot_set_best_study(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $study = $this->createStudy('Approved Study');

        $this->actingAs($student)
            ->patch(route('admin.capstone.set-best', ['capstoneId' => $study->id]))
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
        ]);
    }
}
