<section>
    <header>
        <h2 class="lt-card__title">
            {{ __('Profile Information') }}
        </h2>

        <p class="lt-card__desc">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="lt-form" data-submit-guard>
        @csrf
        @method('patch')

        <div class="lt-field">
            <label for="first_name" class="lt-label">{{ __('First Name') }}</label>
            <input id="first_name" name="first_name" type="text" class="lt-input {{ $errors->has('first_name') ? 'is-invalid' : '' }}" value="{{ old('first_name', $user->first_name) }}" required autofocus autocomplete="given-name">
            @foreach($errors->get('first_name') as $message)
                <p class="lt-field-error">{{ $message }}</p>
            @endforeach
        </div>

        <div class="lt-field">
            <label for="last_name" class="lt-label">{{ __('Last Name') }}</label>
            <input id="last_name" name="last_name" type="text" class="lt-input {{ $errors->has('last_name') ? 'is-invalid' : '' }}" value="{{ old('last_name', $user->last_name) }}" required autocomplete="family-name">
            @foreach($errors->get('last_name') as $message)
                <p class="lt-field-error">{{ $message }}</p>
            @endforeach
        </div>

        <div class="lt-field">
            <label for="email" class="lt-label">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" class="lt-input {{ $errors->has('email') ? 'is-invalid' : '' }}" value="{{ old('email', $user->email) }}" required autocomplete="username">
            @foreach($errors->get('email') as $message)
                <p class="lt-field-error">{{ $message }}</p>
            @endforeach

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="lt-alert lt-alert--warning" role="status" style="margin: 8px 0 0;">
                    <span class="lt-alert__icon">!</span>
                    <div>
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" type="submit" class="lt-link lt-link-btn">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </div>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <p class="lt-match is-ok">
                        ✓ {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            @endif
        </div>

        <div class="lt-form__actions">
            <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Saving...">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <p class="lt-saved" role="status" data-autohide="2000">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>