<?php

namespace Tests\Feature;

use App\Models\ArchivedCapstone;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArchivedCapstoneReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_archived_capstone_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $archivedCapstone = ArchivedCapstone::create([
            'title' => 'Archived Study',
            'authors' => ['Author One'],
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('ocr.review', $archivedCapstone));

        $response->assertOk()
            ->assertSee('Archived Study')
            ->assertViewHas('archivedCapstone', $archivedCapstone);
    }

    public function test_admin_update_redirects_to_archived_capstone_review(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $archivedCapstone = ArchivedCapstone::create([
            'title' => 'Archived Study',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->put(route('ocr.update', $archivedCapstone), [
                'title' => 'Corrected Study',
            ]);

        $response->assertRedirect(route('ocr.review', $archivedCapstone));
        $this->assertDatabaseHas('archived_capstones', [
            'id' => $archivedCapstone->id,
            'title' => 'Corrected Study',
            'status' => 'reviewed',
        ]);
    }

    public function test_admin_approval_marks_archived_capstone_as_repository_approved(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $archivedCapstone = ArchivedCapstone::create([
            'title' => 'Archived Study',
            'status' => 'reviewed',
        ]);

        $response = $this->actingAs($admin)
            ->patch(route('ocr.approve', $archivedCapstone));

        $response->assertRedirect(route('repository.index'));
        $this->assertDatabaseHas('archived_capstones', [
            'id' => $archivedCapstone->id,
            'status' => 'approved',
            'approved_by' => $admin->id,
        ]);
    }

    public function test_only_approved_archived_capstones_are_visible_in_repository(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $approvedCapstone = ArchivedCapstone::create([
            'title' => 'Approved Archived Study',
            'status' => 'approved',
        ]);
        $pendingCapstone = ArchivedCapstone::create([
            'title' => 'Pending Archived Study',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($student)
            ->get(route('repository.index'));

        $response->assertOk()
            ->assertSee('Approved Archived Study')
            ->assertDontSee('Pending Archived Study');

        $this->actingAs($student)
            ->get(route('repository.show', [
                'capstoneId' => $approvedCapstone->id,
                'type' => 'old',
            ]))
            ->assertOk();

        $this->actingAs($student)
            ->get(route('repository.show', [
                'capstoneId' => $pendingCapstone->id,
                'type' => 'old',
            ]))
            ->assertNotFound();
    }

    public function test_repository_search_does_not_bypass_archived_approval_filter(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        ArchivedCapstone::create([
            'title' => 'Unrelated Pending Title',
            'abstract' => 'Contains searchable OCR phrase.',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($student)
            ->get(route('repository.index', ['search' => 'searchable OCR phrase']));

        $response->assertOk()
            ->assertDontSee('Unrelated Pending Title');
        $this->assertSame(0, $response->viewData('totalCapstoneStudies'));
    }

    public function test_only_admin_can_edit_approved_archived_repository_metadata(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $student = User::factory()->create(['role' => 'student']);
        $archivedCapstone = ArchivedCapstone::create([
            'title' => 'Approved Archived Study',
            'abstract' => 'Original abstract',
            'authors' => ['Original Author'],
            'status' => 'approved',
        ]);

        $this->actingAs($admin)
            ->get(route('repository.show', [
                'capstoneId' => $archivedCapstone->id,
                'type' => 'old',
            ]))
            ->assertOk()
            ->assertSee('Edit Metadata');

        $this->actingAs($student)
            ->get(route('repository.show', [
                'capstoneId' => $archivedCapstone->id,
                'type' => 'old',
            ]))
            ->assertOk()
            ->assertDontSee('Edit Metadata');

        $this->actingAs($student)
            ->get(route('admin.repository.edit', [
                'capstoneId' => $archivedCapstone->id,
                'type' => 'old',
            ]))
            ->assertForbidden();

        $this->actingAs($admin)
            ->put(route('admin.repository.update', $archivedCapstone->id), [
                'type' => 'old',
                'title' => 'Corrected Archived Study',
                'abstract' => 'Corrected abstract',
                'authors' => 'Author One, Author Two',
            ])
            ->assertRedirect(route('repository.show', [
                'capstoneId' => $archivedCapstone->id,
                'type' => 'old',
            ]));

        $this->assertDatabaseHas('archived_capstones', [
            'id' => $archivedCapstone->id,
            'title' => 'Corrected Archived Study',
            'abstract' => 'Corrected abstract',
        ]);
    }
}
