<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    // Display all announcements for the administrator.
    public function index(): View
    {
        $announcements = Announcement::with('author')
            ->latest()
            ->paginate(10);

        return view('admin.announcements.index', compact('announcements'));
    }

    // Display the create announcement form.
    public function create(): View
    {
        return view('admin.announcements.create');
    }

    // Save a new announcement.
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'audience' => [
                'required',
                'in:all,students,instructors'
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now'
            ],
        ]);

        Announcement::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'audience' => $validated['audience'],
            'is_published' => true,
            'published_at' => now(),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement published successfully!');
    }

    // Display the edit form.
    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    // Update an existing announcement.
    public function update(
        Request $request,
        Announcement $announcement
    ): RedirectResponse {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:10000'],
            'audience' => [
                'required',
                'in:all,students,instructors'
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after:now'
            ],
        ]);

        $announcement->update($validated);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement updated successfully!');
    }

    // Delete an announcement.
    public function destroy(
        Announcement $announcement
    ): RedirectResponse {
        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement deleted successfully!');
    }
}
