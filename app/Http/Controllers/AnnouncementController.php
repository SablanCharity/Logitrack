<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Mail\AnnouncementPublished;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Throwable;

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

        $announcement = Announcement::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'message' => $validated['message'],
            'audience' => $validated['audience'],
            'is_published' => true,
            'published_at' => now(),
            'expires_at' => $validated['expires_at'] ?? null,
        ]);

        [$sentCount, $failedCount, $recipientCount] = $this->emailAnnouncement($announcement);

        if ($recipientCount === 0) {
            return redirect()
                ->route('admin.announcements.index')
                ->with('success', 'Announcement published successfully.')
                ->with('error', 'No active recipients were found for the selected audience.');
        }

        $redirect = redirect()
            ->route('admin.announcements.index')
            ->with('success', "Announcement published and emailed to {$sentCount} recipient(s).");

        if ($failedCount > 0) {
            $redirect->with('error', "{$failedCount} recipient email(s) could not be sent. Check the mail configuration and application logs.");
        }

        return $redirect;
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

    /**
     * Email the newly published announcement to active members of its audience.
     *
     * @return array{0:int, 1:int, 2:int} sent, failed, recipients
     */
    private function emailAnnouncement(Announcement $announcement): array
    {
        $recipients = User::query()
            ->where('is_active', true)
            ->whereNotNull('email');

        if ($announcement->audience === 'students') {
            $recipients->where('role', 'student');
        } elseif ($announcement->audience === 'instructors') {
            $recipients->where('role', 'instructor')->where('is_approved', true);
        } else {
            $recipients->where(function ($query): void {
                $query->where('role', 'student')
                    ->orWhere(function ($instructorQuery): void {
                        $instructorQuery->where('role', 'instructor')
                            ->where('is_approved', true);
                    });
            });
        }

        $sentCount = 0;
        $failedCount = 0;
        $recipientCount = 0;

        $recipients->select(['id', 'email'])->chunkById(100, function ($users) use (
            $announcement,
            &$sentCount,
            &$failedCount,
            &$recipientCount,
        ): void {
            foreach ($users as $user) {
                $recipientCount++;

                try {
                    Mail::to($user->email)->send(new AnnouncementPublished($announcement));
                    $sentCount++;
                } catch (Throwable $exception) {
                    $failedCount++;
                    Log::warning('Announcement email could not be sent.', [
                        'announcement_id' => $announcement->id,
                        'recipient_id' => $user->id,
                        'exception' => $exception::class,
                    ]);
                }
            }
        });

        return [$sentCount, $failedCount, $recipientCount];
    }
}
