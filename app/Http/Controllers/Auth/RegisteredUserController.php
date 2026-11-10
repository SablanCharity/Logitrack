<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StudentMasterlistRecord;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $instructors = User::where('role', 'instructor')
            ->where('is_approved', true)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('auth.register', compact('instructors'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_number' => ['required', 'string', 'max:50'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'program' => ['required', 'string', 'in:BSCS,BSIT'],
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
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        $validated['student_number'] = trim($validated['student_number']);

        $masterlistRecord = StudentMasterlistRecord::query()
            ->where('student_number', $validated['student_number'])
            ->where('program', $validated['program'])
            ->first();

        if (! $masterlistRecord) {
            return back()->withInput()->withErrors([
                'student_number' => 'Your student number or program does not match the confirmed student masterlist.',
            ]);
        }

        if (User::where('student_number', $validated['student_number'])->exists()) {
            return back()->withInput()->withErrors([
                'student_number' => 'This student number already has a registered account.',
            ]);
        }

        $user = User::create([
            'student_number' => $validated['student_number'],
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'student',
            'program' => $validated['program'],
            'is_approved' => true,
            'is_active' => true,
        ]);

        $otp = random_int(100000, 999999);
        $user->forceFill([
            'registration_otp_hash' => Hash::make((string) $otp),
            'registration_otp_expires_at' => now()->addMinutes(10),
            'registration_otp_attempts' => 0,
        ])->save();

        try {
            Mail::raw(
                "Your LogiTrack verification code is {$otp}. It expires in 10 minutes.",
                fn ($message) => $message->to($user->email)->subject('LogiTrack email verification code')
            );
        } catch (TransportExceptionInterface $exception) {
            $user->delete();

            Log::error('Registration OTP email could not be sent.', [
                'email' => $validated['email'],
                'exception' => $exception,
            ]);

            return back()->withInput()->withErrors([
                'email' => 'We could not send the verification code to this email address. Please check the email configuration or try again later.',
            ]);
        }

        return redirect()->route('registration.otp')->with('registration_user_id', $user->id);
    }

    public function showOtp(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('registration_user_id')) {
            return redirect()->route('register');
        }

        return view('auth.registration-otp');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $user = User::find($request->session()->get('registration_user_id'));

        if (! $user || $user->email_verified_at) {
            return redirect()->route('register');
        }

        $validated = $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        if ($user->registration_otp_attempts >= 5) {
            return back()->withErrors(['otp' => 'Too many incorrect attempts. Please register again.']);
        }

        if (! $user->registration_otp_expires_at || $user->registration_otp_expires_at->isPast()) {
            return back()->withErrors(['otp' => 'This verification code has expired. Please register again.']);
        }

        if (! Hash::check($validated['otp'], $user->registration_otp_hash)) {
            $user->increment('registration_otp_attempts');

            return back()->withErrors(['otp' => 'The verification code is incorrect.']);
        }

        $user->forceFill([
            'is_active' => true,
            'email_verified_at' => now(),
            'registration_otp_hash' => null,
            'registration_otp_expires_at' => null,
            'registration_otp_attempts' => 0,
        ])->save();

        $request->session()->forget('registration_user_id');

        Auth::login($user);

        return redirect()->route('dashboard');
    }
}
