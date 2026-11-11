<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\StudentRegistrationOtp;
use App\Models\StudentMasterlistRecord;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    private const PENDING_REGISTRATION = 'pending_student_registration';

    private const OTP_LIFETIME_MINUTES = 10;

    private const OTP_MAX_ATTEMPTS = 5;

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'student_number' => ['required', 'string', 'max:50', 'exists:student_masterlist_records,student_number', 'unique:users,student_number'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:'.User::class,
            ],

            'password' => [
                'required',
                Rules\Password::defaults(),
                'confirmed',
            ],
        ]);

        $masterlistRecord = StudentMasterlistRecord::where('student_number', $validated['student_number'])->firstOrFail();

        $pendingRegistration = [
            'student_number' => $validated['student_number'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'program' => $masterlistRecord->program,
        ];

        $request->session()->put(
            self::PENDING_REGISTRATION,
            $this->sendOtp($pendingRegistration),
        );

        return to_route('register.otp')->with('status', 'otp-sent');
    }

    public function showOtp(Request $request): View|RedirectResponse
    {
        $pendingRegistration = $this->validPendingRegistration($request);

        if ($pendingRegistration === null) {
            return to_route('register')->withErrors([
                'email' => 'Your registration code has expired. Please register again.',
            ]);
        }

        return view('auth.verify-registration-otp', [
            'email' => $pendingRegistration['email'],
        ]);
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $pendingRegistration = $this->validPendingRegistration($request);

        if ($pendingRegistration === null) {
            return to_route('register')->withErrors([
                'email' => 'Your registration code has expired. Please register again.',
            ]);
        }

        if ($pendingRegistration['attempts'] >= self::OTP_MAX_ATTEMPTS) {
            $request->session()->forget(self::PENDING_REGISTRATION);

            return to_route('register')->withErrors([
                'email' => 'Too many incorrect codes. Please register again.',
            ]);
        }

        if (! Hash::check($validated['otp'], $pendingRegistration['otp_hash'])) {
            $pendingRegistration['attempts']++;

            if ($pendingRegistration['attempts'] >= self::OTP_MAX_ATTEMPTS) {
                $request->session()->forget(self::PENDING_REGISTRATION);

                return to_route('register')->withErrors([
                    'email' => 'Too many incorrect codes. Please register again.',
                ]);
            }

            $request->session()->put(self::PENDING_REGISTRATION, $pendingRegistration);

            throw ValidationException::withMessages([
                'otp' => 'The code you entered is incorrect.',
            ]);
        }

        $studentMasterlistRecord = StudentMasterlistRecord::where('student_number', $pendingRegistration['student_number'])->first();

        if ($studentMasterlistRecord === null) {
            $request->session()->forget(self::PENDING_REGISTRATION);

            return to_route('register')->withErrors([
                'student_number' => 'This student number is no longer available for registration.',
            ]);
        }

        if (User::where('email', $pendingRegistration['email'])->exists()) {
            $request->session()->forget(self::PENDING_REGISTRATION);

            return to_route('register')->withErrors([
                'email' => 'An account with this email already exists. Please log in instead.',
            ]);
        }

        if (User::where('student_number', $pendingRegistration['student_number'])->exists()) {
            $request->session()->forget(self::PENDING_REGISTRATION);

            return to_route('register')->withErrors([
                'student_number' => 'This student number is already registered.',
            ]);
        }

        $user = User::create([
            'student_number' => $pendingRegistration['student_number'],
            'first_name' => $pendingRegistration['first_name'],
            'last_name' => $pendingRegistration['last_name'],
            'email' => $pendingRegistration['email'],
            'password' => $pendingRegistration['password'],
            'role' => 'student',
            'program' => $studentMasterlistRecord->program,
            'is_approved' => false,
        ]);
        $user->forceFill(['email_verified_at' => now()])->save();

        event(new Registered($user));
        Auth::login($user);
        $request->session()->forget(self::PENDING_REGISTRATION);

        return redirect(route('dashboard', absolute: false));
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        $pendingRegistration = $this->validPendingRegistration($request);

        if ($pendingRegistration === null) {
            return to_route('register')->withErrors([
                'email' => 'Your registration code has expired. Please register again.',
            ]);
        }

        $request->session()->put(
            self::PENDING_REGISTRATION,
            $this->sendOtp($pendingRegistration),
        );

        return to_route('register.otp')->with('status', 'otp-resent');
    }

    /**
     * @param  array<string, string>  $registration
     * @return array<string, int|string>
     */
    private function sendOtp(array $registration): array
    {
        $code = (string) random_int(100000, 999999);

        Mail::to($registration['email'])->send(new StudentRegistrationOtp($code));

        return [
            ...$registration,
            'otp_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::OTP_LIFETIME_MINUTES)->getTimestamp(),
            'attempts' => 0,
        ];
    }

    /**
     * @return array<string, int|string>|null
     */
    private function validPendingRegistration(Request $request): ?array
    {
        $pendingRegistration = $request->session()->get(self::PENDING_REGISTRATION);

        if (! is_array($pendingRegistration)
            || ! isset($pendingRegistration['email'], $pendingRegistration['otp_hash'], $pendingRegistration['expires_at'])
            || $pendingRegistration['expires_at'] <= now()->getTimestamp()) {
            $request->session()->forget(self::PENDING_REGISTRATION);

            return null;
        }

        return $pendingRegistration;
    }
}
