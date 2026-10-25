<?php

namespace App\Http\Controllers;

use App\Mail\StaffAccountCreated;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AdminAccountController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $accounts = User::query()
            ->whereIn('role', ['admin', 'instructor', 'ojt'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('role', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('role')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('admin.accounts.index', compact('accounts', 'search'));
    }

    public function students(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $students = User::query()
            ->where('role', 'student')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('program', 'like', '%'.$search.'%')
                        ->orWhere('year_section', 'like', '%'.$search.'%');
                });
            })
            ->with('classroomMemberships.classroom.instructor')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('admin.accounts.students', compact('students', 'search'));
    }

    public function create(): View
    {
        return view('admin.accounts.create');
    }

    public function edit(User $user): View
    {
        abort_unless(in_array($user->role, ['admin', 'instructor', 'ojt'], true), 404);

        return view('admin.accounts.edit', compact('user'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:instructor,ojt'],
            'program' => ['nullable', 'string', 'max:255'],
        ]);

        if (! $this->emailDomainCanReceiveMail($validated['email'])) {
            return back()->withInput()->withErrors([
                'email' => 'This email domain cannot receive mail. Please check the Gmail address and try again.',
            ]);
        }

        $temporaryPassword = Str::random(24);

        try {
            DB::transaction(function () use ($validated, $temporaryPassword): void {
                User::create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'password' => Hash::make($temporaryPassword),
                    'role' => $validated['role'],
                    'program' => $validated['program'] ?? null,
                    'is_approved' => true,
                    'must_change_password' => $validated['role'] === 'instructor',
                ]);

                Mail::to($validated['email'])->send(new StaffAccountCreated(
                    $validated['first_name'],
                    $validated['email'],
                    $temporaryPassword,
                    $validated['role'],
                ));
            });
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withInput()->withErrors([
                'email' => 'The account was not created because the credentials email could not be sent. Check the address and mail settings, then try again.',
            ]);
        }

        return redirect()
            ->route('admin.accounts.index')
            ->with('success', ucfirst($validated['role']).' account created and credentials emailed successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['admin', 'instructor', 'ojt'], true), 404);

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'lowercase', 'max:255', 'unique:users,email,'.$user->id],
            'program' => ['nullable', 'string', 'max:255'],
        ]);

        $emailChanged = $validated['email'] !== $user->email;

        if ($emailChanged && ! $this->emailDomainCanReceiveMail($validated['email'])) {
            return back()->withInput()->withErrors([
                'email' => 'This email domain cannot receive mail. Please check the Gmail address and try again.',
            ]);
        }

        $temporaryPassword = Str::random(24);

        try {
            DB::transaction(function () use ($user, $validated, $emailChanged, $temporaryPassword): void {
                $user->update([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $validated['email'],
                    'program' => $validated['program'] ?? null,
                    ...($emailChanged ? [
                        'password' => Hash::make($temporaryPassword),
                        'must_change_password' => $user->role === 'instructor',
                    ] : []),
                ]);

                if ($emailChanged) {
                    Mail::to($validated['email'])->send(new StaffAccountCreated(
                        $validated['first_name'],
                        $validated['email'],
                        $temporaryPassword,
                        $user->role,
                    ));
                }
            });
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withInput()->withErrors([
                'email' => 'The account was not updated because the credentials email could not be sent.',
            ]);
        }

        return redirect()
            ->route('admin.accounts.index')
            ->with('success', $emailChanged
                ? 'Account updated and new credentials emailed successfully.'
                : 'Account details updated successfully.');
    }

    public function toggleActive(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 403, 'You cannot disable your own account.');

        $user->update(['is_active' => ! $user->is_active]);

        return back()->with('success', $user->is_active
            ? 'Account activated successfully.'
            : 'Account disabled successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 403, 'You cannot delete your own account.');

        $user->delete();

        return back()->with('success', 'Account deleted successfully.');
    }

    private function emailDomainCanReceiveMail(string $email): bool
    {
        $domain = substr(strrchr($email, '@'), 1);

        return $domain !== false
            && (checkdnsrr($domain, 'MX') || checkdnsrr($domain, 'A') || checkdnsrr($domain, 'AAAA'));
    }
}
