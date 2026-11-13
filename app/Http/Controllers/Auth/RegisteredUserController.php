<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\StudentRegistrationOtp;
use App\Models\StudentMasterlistRecord;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
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
     * Check whether a name looks like a genuine personal name.
     */
    private function isValidName(string $value): bool
    {
        $value = trim(preg_replace('/\s+/u', ' ', $value));

        // Letters only, allowing spaces, apostrophes and hyphens.
        if (!preg_match("/\A\p{L}+(?:[ '-]\p{L}+)*\z/u", $value)) {
            return false;
        }

        $compact = preg_replace("/[ '-]/u", '', $value);

        $lower = function_exists('mb_strtolower')
            ? mb_strtolower($compact, 'UTF-8')
            : strtolower($compact);

        $length = preg_match_all('/\p{L}/u', $lower);
        $uniqueLetters = count(array_unique(
            preg_split('//u', $lower, -1, PREG_SPLIT_NO_EMPTY)
        ));

        // Reject excessive repeated letters.
        if (preg_match('/(\p{L})\1{2,}/iu', $lower)) {
            return false;
        }

        // Reject strings with too few different letters.
        if ($length >= 7 && $uniqueLetters < 4) {
            return false;
        }

        if ($length >= 5 && $uniqueLetters < 3) {
            return false;
        }

        // Reject obvious keyboard-mashing patterns.
        if (preg_match(
            '/qwerty|asdfgh|zxcvbn|qazwsx|wsxedc|abcdef|ghijkl|poiuyt|lkjhgfd|mnbvcx|plmokn|oiuytr|asdfjkl/i',
            $lower
        )) {
            return false;
        }

        // Count vowels, including common accented vowels and y.
        preg_match_all('/[aeiouyáéíóúàèìòùâêîôûäëïöü]/iu', $lower, $matches);
        $vowelCount = count($matches[0]);

        if ($length >= 6) {
            $vowelRatio = $vowelCount / $length;

            if ($vowelRatio < 0.16 || $vowelRatio > 0.72) {
                return false;
            }
        }

        // Reject four or more consecutive consonants.
        if (preg_match('/[bcdfghjklmnpqrstvwxz]{4,}/i', $lower)) {
            return false;
        }

        // Reject obvious alternating sequences such as ababab.
        if (preg_match('/^(.)(.)\1\2(?:\1\2)+$/u', $lower)) {
            return false;
        }

        // Reject common keyboard sequences.
        if (preg_match(
            '/qwe|wer|ert|rty|tyu|yui|uio|asd|sdf|dfg|fgh|ghj|hjk|jkl|zxc|xcv|cvb|vbn/i',
            $lower
        )) {
            return false;
        }

        return true;
    }


    /**
     * Check student number and email availability for the registration form.
     * This endpoint is advisory; store() performs authoritative validation again.
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $data = $request->validate([
            'student_number' => ['sometimes', 'required', 'string', 'regex:/\A[0-9]{9}\z/'],
            'email' => ['sometimes', 'required', 'string', 'email', 'max:254'],
        ]);

        $result = [];

        if (isset($data['student_number'])) {
            $studentNumber = $data['student_number'];

            if (StudentMasterlistRecord::where('student_number', $studentNumber)->doesntExist()) {
                $result['student_number'] = 'not_in_masterlist';
            } elseif (User::where('student_number', $studentNumber)->exists()) {
                $result['student_number'] = 'taken';
            } else {
                $result['student_number'] = 'available';
            }
        }

        if (isset($data['email'])) {
            $email = mb_strtolower(trim($data['email']));
            $result['email'] = User::whereRaw('LOWER(email) = ?', [$email])->exists()
                ? 'taken'
                : 'available';
        }

        return response()->json($result);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Normalize values consistently before validation and uniqueness checks.
        $request->merge([
            'first_name' => preg_replace('/\\s+/u', ' ', trim((string) $request->input('first_name'))),
            'last_name' => preg_replace('/\\s+/u', ' ', trim((string) $request->input('last_name'))),
            'student_number' => trim((string) $request->input('student_number')),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'first_name' => [
                'required', 'string', 'min:2', 'max:60',
                function ($attribute, $value, $fail) {
                    if (! $this->isValidName($value)) {
                        $fail('Please enter a valid first name. Random letters, keyboard patterns, numbers and symbols are not allowed.');
                    }
                },
            ],
            'last_name' => [
                'required', 'string', 'min:2', 'max:60',
                function ($attribute, $value, $fail) {
                    if (! $this->isValidName($value)) {
                        $fail('Please enter a valid last name. Random letters, keyboard patterns, numbers and symbols are not allowed.');
                    }
                },
            ],
            'student_number' => [
                'required', 'string', 'regex:/\\A[0-9]{9}\\z/',
                'exists:student_masterlist_records,student_number',
                'unique:users,student_number',
                function ($attribute, $value, $fail) {
                    if (preg_match('/\\A([0-9])\\1{8}\\z/', $value)) {
                        $fail('The student number is not valid.');
                    }
                },
            ],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:254',
                'unique:users,email',
            ],

            'password' => [
                'required',
                Rules\Password::min(12)->mixedCase()->numbers()->symbols(),
                'confirmed',
            ],
        ], [
            'student_number.regex' => 'Student number must contain exactly 9 digits.',
            'student_number.exists' => 'Your student number is not registered in the student masterlist.',
            'student_number.unique' => 'An account with this student number already exists.',
            'email.email' => 'Please enter a valid email address.',
            'email.unique' => 'An account with this email address already exists.',
            'password.confirmed' => 'Your password confirmation does not match.',
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
