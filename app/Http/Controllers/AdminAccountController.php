<?php

namespace App\Http\Controllers;

use App\Imports\InstructorImport;
use App\Mail\StaffAccountCreated;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Maatwebsite\Excel\Exceptions\NoTypeDetectedException;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class AdminAccountController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim($request->string('q')->toString());
        $accounts = User::query()
            ->whereIn('role', ['admin', 'instructor'])
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

    public function edit(User $user): View
    {
        abort_unless(in_array($user->role, ['admin', 'instructor'], true), 404);

        return view('admin.accounts.edit', compact('user'));
    }

    public function import(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt,xls,xlsx', 'max:10240'],
        ]);

        $import = new InstructorImport;
        try {
            Excel::import($import, $validated['file']);
        } catch (NoTypeDetectedException $exception) {
            report($exception);

            return back()->withErrors([
                'file' => 'The uploaded file format could not be read. Use a CSV, XLS, or XLSX file.',
            ]);
        }
        $rows = $this->normalizeInstructorRows($import->rows ?? collect());

        try {
            DB::transaction(function () use ($rows): void {
                foreach ($rows as $row) {
                    $temporaryPassword = Str::random(24);
                    User::create([
                        'first_name' => $row['first_name'],
                        'last_name' => $row['last_name'],
                        'email' => $row['email'],
                        'password' => Hash::make($temporaryPassword),
                        'role' => 'instructor',
                        'is_approved' => true,
                        'is_active' => true,
                        'must_change_password' => true,
                    ]);

                    Mail::to($row['email'])->send(new StaffAccountCreated(
                        $row['first_name'],
                        $row['email'],
                        $temporaryPassword,
                        'instructor',
                    ));
                }
            });
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withErrors([
                'file' => 'The import was not completed because an instructor credentials email could not be sent. Check the mail settings and try again.',
            ]);
        }

        return redirect()
            ->route('admin.accounts.index')
            ->with('success', $rows->count().' instructor account(s) imported and credentials emailed successfully.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_unless(in_array($user->role, ['admin', 'instructor'], true), 404);

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

    /**
     * @param  Collection<int, mixed>  $rows
     * @return Collection<int, array{first_name: string, last_name: string, email: string}>
     */
    private function normalizeInstructorRows(Collection $rows): Collection
    {
        $normalized = collect();
        $emails = [];

        foreach ($rows as $index => $row) {
            $row = collect($row)->mapWithKeys(fn (mixed $value, mixed $key): array => [
                Str::snake(trim((string) $key)) => is_string($value) ? trim($value) : $value,
            ]);
            $email = Str::lower((string) ($row->get('email') ?? ''));
            $name = trim((string) ($row->get('name') ?? ''));
            $firstName = trim((string) ($row->get('first_name') ?? ''));
            $lastName = trim((string) ($row->get('last_name') ?? ''));

            if ($firstName === '' && $lastName === '' && $name !== '') {
                $parts = preg_split('/\s+/', $name, 2);
                $firstName = $parts[0] ?? '';
                $lastName = $parts[1] ?? '';
            }

            if ($firstName === '' || $lastName === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw ValidationException::withMessages([
                    'file' => 'Row '.($index + 2).' must include a valid email and a name (name, or first_name and last_name).',
                ]);
            }

            if (isset($emails[$email]) || User::query()->where('email', $email)->exists()) {
                throw ValidationException::withMessages([
                    'file' => 'The email '.$email.' appears more than once or already belongs to an account.',
                ]);
            }

            if (! $this->emailDomainCanReceiveMail($email)) {
                throw ValidationException::withMessages([
                    'file' => 'The email domain for '.$email.' cannot receive mail.',
                ]);
            }

            $emails[$email] = true;
            $normalized->push([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
            ]);
        }

        if ($normalized->isEmpty()) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file does not contain any instructor records.',
            ]);
        }

        return $normalized;
    }
}
