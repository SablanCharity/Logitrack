<section>
    <header>
        <h2 class="lt-card__title">
            {{ __('Update Password') }}
        </h2>

        <p class="lt-card__desc">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="lt-form" data-submit-guard>
        @csrf
        @method('put')

        <div class="lt-field">
            <label for="update_password_current_password" class="lt-label">{{ __('Current Password') }}</label>
            <div class="lt-input-wrap">
                <input id="update_password_current_password" name="current_password" type="password" class="lt-input {{ $errors->updatePassword->has('current_password') ? 'is-invalid' : '' }}" autocomplete="current-password">
                <button type="button" class="lt-eye" data-toggle-password="#update_password_current_password" aria-pressed="false" aria-label="Show password"><svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button>
            </div>
            @foreach($errors->updatePassword->get('current_password') as $message)
                <p class="lt-field-error">{{ $message }}</p>
            @endforeach
        </div>

        <div class="lt-field">
            <label for="update_password_password" class="lt-label">{{ __('New Password') }}</label>
            <div class="lt-input-wrap">
                <input id="update_password_password" name="password" type="password" class="lt-input {{ $errors->updatePassword->has('password') ? 'is-invalid' : '' }}" autocomplete="new-password" data-strength="#update-strength">
                <button type="button" class="lt-eye" data-toggle-password="#update_password_password" aria-pressed="false" aria-label="Show password"><svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button>
            </div>
            <div class="lt-strength" id="update-strength" data-level="0">
                <div class="lt-strength__bars"><span class="lt-strength__bar"></span><span class="lt-strength__bar"></span><span class="lt-strength__bar"></span><span class="lt-strength__bar"></span></div>
                <span class="lt-strength__label">Use at least 8 characters</span>
            </div>
            @foreach($errors->updatePassword->get('password') as $message)
                <p class="lt-field-error">{{ $message }}</p>
            @endforeach
        </div>

        <div class="lt-field">
            <label for="update_password_password_confirmation" class="lt-label">{{ __('Confirm Password') }}</label>
            <div class="lt-input-wrap">
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="lt-input {{ $errors->updatePassword->has('password_confirmation') ? 'is-invalid' : '' }}" autocomplete="new-password" data-match="#update_password_password" data-match-msg="#update-match-msg">
                <button type="button" class="lt-eye" data-toggle-password="#update_password_password_confirmation" aria-pressed="false" aria-label="Show password"><svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button>
            </div>
            <span id="update-match-msg" class="lt-match" aria-live="polite"></span>
            @foreach($errors->updatePassword->get('password_confirmation') as $message)
                <p class="lt-field-error">{{ $message }}</p>
            @endforeach
        </div>

        <div class="lt-form__actions">
            <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Saving...">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p class="lt-saved" role="status" data-autohide="2000">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>