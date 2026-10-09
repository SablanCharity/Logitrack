<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

```
    <!-- First Name -->
    <div>
        <x-input-label for="first_name" :value="__('First Name')" />

        <x-text-input
            id="first_name"
            class="block mt-1 w-full"
            type="text"
            name="first_name"
            :value="old('first_name')"
            required
            autofocus
            autocomplete="given-name"
        />

        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
    </div>

    <!-- Last Name -->
    <div class="mt-4">
        <x-input-label for="last_name" :value="__('Last Name')" />

        <x-text-input
            id="last_name"
            class="block mt-1 w-full"
            type="text"
            name="last_name"
            :value="old('last_name')"
            required
            autocomplete="family-name"
        />

        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
    </div>

    <!-- Email Address -->
    <div class="mt-4">
        <x-input-label for="email" :value="__('School Email')" />

        <x-text-input
            id="email"
            class="block mt-1 w-full"
            type="email"
            name="email"
            :value="old('email')"
            required
            autocomplete="username"
        />

        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    <!-- Program -->
    <div class="mt-4">
        <x-input-label for="program" :value="__('Program')" />

        <select
            id="program"
            name="program"
            class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
            required
        >
            <option value="">Select Program</option>
            <option value="BSIT" {{ old('program') === 'BSIT' ? 'selected' : '' }}>
                BSIT
            </option>
            <option value="BSCS" {{ old('program') === 'BSCS' ? 'selected' : '' }}>
                BSCS
            </option>
        </select>

        <x-input-error :messages="$errors->get('program')" class="mt-2" />
    </div>

    <!-- Year and Section -->
    <div class="mt-4">
        <x-input-label for="year_section" :value="__('Year & Section')" />

        <x-text-input
            id="year_section"
            class="block mt-1 w-full"
            type="text"
            name="year_section"
            :value="old('year_section')"
            placeholder="Example: 4-D"
            required
        />

        <x-input-error :messages="$errors->get('year_section')" class="mt-2" />
        </div>

    <!-- Instructor / Adviser -->
        <div>
        <x-input-label for="instructor_id" :value="__('Instructor / Adviser')" />

        <select
            id="instructor_id"
            name="instructor_id"
            required
        >
            <option value="">-- Select Instructor / Adviser --</option>

            @foreach($instructors as $instructor)
                <option
                    value="{{ $instructor->id }}"
                    {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}
                >
                    {{ $instructor->first_name }} {{ $instructor->last_name }}
                </option>
            @endforeach
        </select>

        <x-input-error
            :messages="$errors->get('instructor_id')"
            class="mt-2"
        />
    </div>

   

    <!-- Class Code -->
    <div class="mt-4">
        <x-input-label for="class_code" :value="__('Class Code')" />

        <x-text-input
            id="class_code"
            class="block mt-1 w-full"
            type="text"
            name="class_code"
            :value="old('class_code')"
            required
        />

        <x-input-error :messages="$errors->get('class_code')" class="mt-2" />
    </div>

    <!-- Password -->
    <div class="mt-4">
        <x-input-label for="password" :value="__('Password')" />

        <x-text-input
            id="password"
            class="block mt-1 w-full"
            type="password"
            name="password"
            required
            autocomplete="new-password"
        />

        <x-input-error :messages="$errors->get('password')" class="mt-2" />
    </div>

    <!-- Confirm Password -->
    <div class="mt-4">
        <x-input-label
            for="password_confirmation"
            :value="__('Confirm Password')"
        />

        <x-text-input
            id="password_confirmation"
            class="block mt-1 w-full"
            type="password"
            name="password_confirmation"
            required
            autocomplete="new-password"
        />

        <x-input-error
            :messages="$errors->get('password_confirmation')"
            class="mt-2"
        />
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-end mt-4">
        <a
            class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            href="{{ route('login') }}"
        >
            {{ __('Already registered?') }}
        </a>

        <x-primary-button class="ms-4">
            {{ __('Register') }}
        </x-primary-button>
    </div>
</form>
```

</x-guest-layout>
