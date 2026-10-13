<section>
    <header>
        <h2 class="lt-card__title">
            {{ __('Delete Account') }}
        </h2>

        <p class="lt-card__desc">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button type="button" class="lt-btn lt-btn--danger" data-open-dialog="#confirm-user-deletion">
        {{ __('Delete Account') }}
    </button>

    <dialog
        id="confirm-user-deletion"
        class="lt-dialog lt-dialog--form"
        aria-labelledby="confirm-user-deletion-title"
        @if($errors->userDeletion->isNotEmpty()) data-autoopen @endif
    >
        <form method="post" action="{{ route('profile.destroy') }}" data-submit-guard>
            @csrf
            @method('delete')

            <div class="lt-dialog__body">
                <h2 id="confirm-user-deletion-title" class="lt-dialog__title">
                    {{ __('Are you sure you want to delete your account?') }}
                </h2>

                <p class="lt-dialog__text">
                    {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
                </p>

                <div class="lt-field">
                    <label for="password" class="lt-label lt-sr-only">{{ __('Password') }}</label>

                    <div class="lt-input-wrap">
                        <input
                            id="password"
                            name="password"
                            type="password"
                            class="lt-input {{ $errors->userDeletion->has('password') ? 'is-invalid' : '' }}"
                            placeholder="{{ __('Password') }}"
                            autofocus
                        >
                        <button type="button" class="lt-eye" data-toggle-password="#password" aria-pressed="false" aria-label="Show password"><svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg><svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg></button>
                    </div>

                    @foreach($errors->userDeletion->get('password') as $message)
                        <p class="lt-field-error">{{ $message }}</p>
                    @endforeach
                </div>
            </div>

            <div class="lt-dialog__foot">
                <button type="button" class="lt-btn lt-btn--ghost lt-btn--sm" data-close-dialog>
                    {{ __('Cancel') }}
                </button>

                <button type="submit" class="lt-btn lt-btn--danger lt-btn--sm" data-loading-text="Deleting...">
                    {{ __('Delete Account') }}
                </button>
            </div>
        </form>
    </dialog>
</section>