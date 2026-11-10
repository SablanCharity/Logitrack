<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\ClassroomMember;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

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
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],

            'last_name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
            ],

            'program' => [
                'required',
                'string',
                'in:BSIT,BSCS',
            ],

            'year_section' => [
                'required',
                'string',
                'max:255',
            ],

            'instructor_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],

            'class_code' => [
                'required',
                'string',
                'max:50',
                'exists:classrooms,class_code',
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        // Find the classroom using the student's class code.
        $classroom = Classroom::where('class_code', $request->class_code)
            ->where('instructor_id', $request->instructor_id)
            ->first();

        if (! $classroom) {
            return back()
                ->withInput()
                ->withErrors([
                    'class_code' => 'The selected instructor does not match the class code.',
                ]);
        }

        // Create the student and classroom membership together.
        $user = DB::transaction(function () use ($request, $classroom) {

            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),

                // Public registration creates a student account.
                'role' => 'student',

                'program' => $request->program,
                'year_section' => $request->year_section,

                // Account still needs approval.
                'is_approved' => false,
            ]);

            // Add the student to the classroom as pending.
            ClassroomMember::create([
                'classroom_id' => $classroom->id,
                'user_id' => $user->id,
                'status' => 'pending',
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
