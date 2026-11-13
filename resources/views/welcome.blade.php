<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'LogiTrack') }} · Capstone Repository</title>

        @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
        
        <style>
            /* Registration dialog layout: keep every field aligned and prevent
               long values, validation messages, or autofill from breaking the modal. */
            #register-dialog {
                width: min(94vw, 720px);
                max-width: 720px;
                max-height: 92vh;
                overflow: auto;
                box-sizing: border-box;
            }

            #register-dialog .lt-dialog__body--register {
                width: 100%;
                box-sizing: border-box;
                padding: clamp(1.25rem, 3vw, 2rem);
            }

            #register-dialog .lt-register-dialog__form {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem 1.1rem;
                width: 100%;
                min-width: 0;
            }

            #register-dialog .lt-field,
            #register-dialog .lt-register-password-row,
            #register-dialog .lt-register-dialog__actions {
                min-width: 0;
                width: 100%;
                box-sizing: border-box;
            }

            #register-dialog .lt-field {
                display: flex;
                flex-direction: column;
                gap: .4rem;
            }

            #register-dialog .lt-label {
                display: block;
                margin: 0;
                line-height: 1.35;
            }

            #register-dialog .lt-input {
                display: block;
                width: 100%;
                max-width: 100%;
                min-width: 0;
                min-height: 46px;
                box-sizing: border-box;
                padding: .7rem .85rem;
                line-height: 1.4;
                font: inherit;
                border-radius: 10px;
                outline-offset: 2px;
            }

            #register-dialog .lt-input:focus {
                outline: 2px solid currentColor;
                outline-offset: 1px;
            }

            #register-dialog .lt-register-dialog__field--full,
            #register-dialog .lt-register-password-row,
            #register-dialog .lt-register-dialog__actions {
                grid-column: 1 / -1;
            }

            .lt-register-password-row {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem 1.1rem;
            }

            .lt-live-status {
                display: none;
                min-height: 0;
                margin-top: .1rem;
                color: #dc2626;
                font-size: .75rem;
                line-height: 1.4;
                overflow-wrap: anywhere;
            }

            .lt-live-status:empty {
                display: none;
            }

            #register-dialog .lt-field-error {
                margin: .15rem 0 0;
                overflow-wrap: anywhere;
            }

            #register-dialog .lt-register-dialog__actions {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 1rem;
                flex-wrap: wrap;
                padding-top: .25rem;
            }

            #register-dialog .lt-register-dialog__actions button {
                min-width: 130px;
            }

            @media (max-width: 600px) {
                #register-dialog .lt-register-dialog__form {
                    grid-template-columns: minmax(0, 1fr);
                }

                #register-dialog .lt-register-dialog__field--full,
                #register-dialog .lt-register-password-row,
                #register-dialog .lt-register-dialog__actions {
                    grid-column: 1;
                }

                .lt-register-password-row {
                    grid-template-columns: minmax(0, 1fr);
                }

                #register-dialog .lt-register-dialog__actions {
                    align-items: stretch;
                    flex-direction: column-reverse;
                }

                #register-dialog .lt-register-dialog__actions button {
                    width: 100%;
                }
            }


            /* Registration form structure follows the grouped layout in the reference. */
            #register-dialog .lt-register-dialog__form {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                align-items: start;
            }

            #register-dialog .lt-register-dialog__field--full,
            #register-dialog .lt-register-dialog__name-fields,
            #register-dialog .lt-register-dialog__actions {
                grid-column: 1 / -1;
            }

            #register-dialog .lt-register-dialog__name-fields {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 1rem 1.1rem;
                min-width: 0;
            }

            #register-dialog .lt-input-wrap {
                position: relative;
                width: 100%;
                min-width: 0;
            }

            #register-dialog .lt-input-wrap .lt-input {
                padding-right: 3rem;
            }

            #register-dialog .lt-live-status {
                display: none;
            }

            #register-dialog .lt-live-status:not(:empty) {
                display: block;
            }

            @media (max-width: 600px) {
                #register-dialog .lt-register-dialog__form,
                #register-dialog .lt-register-dialog__name-fields {
                    grid-template-columns: minmax(0, 1fr);
                }

                #register-dialog .lt-register-dialog__field--full,
                #register-dialog .lt-register-dialog__name-fields,
                #register-dialog .lt-register-dialog__actions {
                    grid-column: 1;
                }
            }

        </style>
        
    </head>
    <body class="lt-body">

        {{-- ===== Navigation ===== --}}
        <header class="lt-nav lt-nav--landing">
            <div class="lt-nav__inner">
                <a href="#top" class="lt-brand">
                    <span class="lt-brand__mark">L</span>
                    LogiTrack
                </a>

                <button type="button" class="lt-nav__toggle" data-nav-toggle="#nav-menu" aria-label="Toggle menu" aria-expanded="false">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                </button>

                <div class="lt-nav__menu" id="nav-menu">
                    <nav class="lt-nav__links" aria-label="Sections">
                        <a href="#features" class="lt-nav__link">Features</a>
                        <a href="#how-it-works" class="lt-nav__link">How it works</a>
                        <a href="#roles" class="lt-nav__link">Who it's for</a>
                    </nav>

                    @if (Route::has('login'))
                        <div class="lt-nav__actions">
                            @auth
                                <button type="button" class="lt-btn lt-btn--gold lt-btn--sm" data-open-dialog="#login-dialog">
                                    Log in
                                </button>
                            @else
                                <button type="button" class="lt-btn lt-btn--outline-light lt-btn--sm" data-open-dialog="#login-dialog">
                                    Log in
                                </button>
                                @if (Route::has('register'))
                                    <button type="button" class="lt-btn lt-btn--gold lt-btn--sm" data-open-dialog="#register-dialog">
                                        Register
                                    </button>
                                @endif
                            @endauth
                        </div>
                    @endif
                </div>
            </div>
        </header>

        <main id="top">

            {{-- ===== Hero ===== --}}
            <section class="lt-hero">
                <div class="lt-hero__inner">

                    <div>
                        <span class="lt-hero__tag">Capstone Repository &amp; Decision Support</span>

                        <h1>Every capstone, <span>one searchable home.</span></h1>

                        <p class="lt-hero__lead">
                            LogiTrack keeps approved capstone projects in one repository, helps you find
                            related existing studies, and explains which parts of a proposal to review
                            before you commit to a topic.
                        </p>

                        <div class="lt-hero__cta">
                            @if (Route::has('login'))
                                @auth
                                    <button type="button" class="lt-btn lt-btn--gold lt-btn--lg" data-open-dialog="#login-dialog">
                                        Log in
                                    </button>
                                @else
                                    @if (Route::has('register'))
                                        <button type="button" class="lt-btn lt-btn--gold lt-btn--lg" data-open-dialog="#register-dialog">
                                            Create your account
                                        </button>
                                    @endif
                                    <button type="button" class="lt-btn lt-btn--outline-light lt-btn--lg" data-open-dialog="#login-dialog">
                                        Log in
                                    </button>
                                @endauth
                            @endif
                        </div>
                    </div>

                    {{-- Decorative illustration only (sample data, not real results) --}}
                    <div class="lt-demo" aria-hidden="true">
                        <div class="lt-demo__top">
                            <span class="lt-demo__title">Similarity Analysis</span>
                            <span class="lt-demo__pill">Illustration</span>
                        </div>
                        <p class="lt-demo__q">Proposed: Smart Campus Navigation System</p>

                        <div class="lt-demo__row">
                            <div class="lt-demo__row-head"><span>Mobile Campus Navigation Application</span><span class="lt-demo__score">84%</span></div>
                            <div class="lt-bar"><div class="lt-bar__fill" style="--w: 84%"></div></div>
                        </div>
                        <div class="lt-demo__row">
                            <div class="lt-demo__row-head"><span>Smart University Locator</span><span class="lt-demo__score">71%</span></div>
                            <div class="lt-bar"><div class="lt-bar__fill" style="--w: 71%"></div></div>
                        </div>
                        <div class="lt-demo__row">
                            <div class="lt-demo__row-head"><span>Campus Assistance System</span><span class="lt-demo__score">58%</span></div>
                            <div class="lt-bar"><div class="lt-bar__fill lt-bar__fill--gold" style="--w: 58%"></div></div>
                        </div>
                        <p class="lt-demo__foot">Related studies are ranked, with the review areas explained.</p>
                    </div>

                </div>
            </section>

            {{-- ===== Features ===== --}}
            <section class="lt-section" id="features">
                <div class="lt-section__inner">
                    <div class="lt-section__head" data-reveal>
                        <span class="lt-eyebrow">Features</span>
                        <h2>Built around the capstone workflow</h2>
                        <p>From the first submission to the approved repository entry, each step has a clear owner.</p>
                    </div>

                    <div class="lt-features">
                        <article class="lt-feature" data-reveal>
                            <div class="lt-feature__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                            </div>
                            <h3>Searchable repository</h3>
                            <p>Browse and search approved capstones by title, abstract, program and year to support your RRL.</p>
                        </article>

                        <article class="lt-feature" data-reveal data-reveal-delay="80">
                            <div class="lt-feature__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="8" height="16" rx="1.5"/><rect x="13" y="4" width="8" height="16" rx="1.5"/></svg>
                            </div>
                            <h3>Similarity checker</h3>
                            <p>Compare a proposed title or introduction against existing studies and see the closest matches.</p>
                        </article>

                        <article class="lt-feature" data-reveal data-reveal-delay="160">
                            <div class="lt-feature__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3 8-8"/><path d="M20 12v6a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h9"/></svg>
                            </div>
                            <h3>Decision support</h3>
                            <p>Get explainable suggestions on what to review, such as scope, target users and features.</p>
                        </article>

                        <article class="lt-feature" data-reveal data-reveal-delay="240">
                            <div class="lt-feature__icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/><path d="M9 13h6M9 17h4"/></svg>
                            </div>
                            <h3>Archived capstones</h3>
                            <p>Older hardbound studies are scanned, read with OCR and checked by a person before they are approved.</p>
                        </article>
                    </div>
                </div>
            </section>

            {{-- ===== How it works ===== --}}
            <section class="lt-section lt-section--white" id="how-it-works">
                <div class="lt-section__inner">
                    <div class="lt-section__head" data-reveal>
                        <span class="lt-eyebrow">How it works</span>
                        <h2>From classroom to repository</h2>
                        <p>Nothing enters the repository automatically. Every submission is reviewed first.</p>
                    </div>

                    <div class="lt-flow">
                        <div class="lt-flow__step" data-reveal>
                            <h3>Join your class</h3>
                            <p>Register with your adviser's class code and wait for their approval.</p>
                        </div>
                        <div class="lt-flow__step" data-reveal data-reveal-delay="80">
                            <h3>Create your group</h3>
                            <p>Work solo or add up to 2 more members to your group room.</p>
                        </div>
                        <div class="lt-flow__step" data-reveal data-reveal-delay="160">
                            <h3>Submit your capstone</h3>
                            <p>Enter the title, abstract and publication date for review.</p>
                        </div>
                        <div class="lt-flow__step" data-reveal data-reveal-delay="240">
                            <h3>Adviser verifies</h3>
                            <p>Your adviser verifies the details or returns them with a reason.</p>
                        </div>
                        <div class="lt-flow__step" data-reveal data-reveal-delay="320">
                            <h3>Admin approves</h3>
                            <p>Once approved, your study appears in the repository.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===== Roles ===== --}}
            <section class="lt-section" id="roles">
                <div class="lt-section__inner">
                    <div class="lt-section__head" data-reveal>
                        <span class="lt-eyebrow">Who it's for</span>
                        <h2>One system, four roles</h2>
                    </div>

                    <div class="lt-roles">
                        <div class="lt-role" data-reveal>
                            <h3>Students</h3>
                            <p>Join a classroom, form a group, submit a capstone, and check proposals against existing studies.</p>
                        </div>
                        <div class="lt-role" data-reveal data-reveal-delay="80">
                            <h3>Instructors / Advisers</h3>
                            <p>Create classrooms, approve students, and verify or return group submissions.</p>
                        </div>
                        <div class="lt-role" data-reveal data-reveal-delay="160">
                            <h3>OJT</h3>
                            <p>Help review and correct metadata, without final repository approval authority.</p>
                        </div>
                        <div class="lt-role" data-reveal data-reveal-delay="240">
                            <h3>Admin</h3>
                            <p>Manages accounts, reviews submissions, and gives the final approval for the repository.</p>
                        </div>
                    </div>
                </div>
            </section>

            {{-- ===== Call to action ===== --}}
            @if (Route::has('login'))
                <section class="lt-cta">
                    <h2 data-reveal>Ready to get started?</h2>
                    <p data-reveal>Ask your adviser for the class code, then log in to continue.</p>

                    <div data-reveal>
                        @auth
                            <a href="{{ url('/dashboard') }}" class="lt-btn lt-btn--gold lt-btn--lg">Go to Dashboard</a>
                        @else
                            @if (Route::has('register'))
                                <button type="button" class="lt-btn lt-btn--gold lt-btn--lg" data-open-dialog="#register-dialog">Register</button>
                            @endif
                            <button type="button" class="lt-btn lt-btn--outline-light lt-btn--lg" data-open-dialog="#login-dialog">Log in</button>
                        @endauth
                    </div>
                </section>
            @endif

        </main>

        @if (Route::has('register'))
            <dialog id="register-dialog" class="lt-dialog lt-dialog--register" aria-labelledby="register-dialog-title" @if (old('_form') === 'register') data-autoopen @endif>
                <div class="lt-dialog__body lt-dialog__body--register">
                    <button type="button" class="lt-search-filter__toggle lt-register-dialog__close" data-close-dialog aria-label="Close registration form">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>

                    <div class="lt-register-dialog__head">
                        <div>
                            <h2 id="register-dialog-title" class="lt-dialog__title">Create your account</h2>
                            <p class="lt-dialog__text">Join LogiTrack to access your capstone workspace.</p>
                        </div>
                    </div>

                    <form id="landing-register-form" method="POST" action="{{ route('register') }}" data-check-url="{{ route('registration.check') }}" class="lt-form lt-register-dialog__form">
                        @csrf
                        <input type="hidden" name="_form" value="register">

                        <div class="lt-field lt-register-dialog__field--full">
                            <label for="landing-student-number" class="lt-label">{{ __('Student Number') }}</label>
                            <input id="landing-student-number" class="lt-input {{ old('_form') === 'register' && $errors->has('student_number') ? 'is-invalid' : '' }}" type="text" name="student_number" value="{{ old('_form') === 'register' ? old('student_number') : '' }}" required inputmode="numeric" minlength="9" maxlength="9" pattern="[0-9]{9}" autocomplete="off" aria-describedby="student-number-status">
                            <small id="student-number-status" class="lt-live-status" aria-live="polite"></small>
                            @if (old('_form') === 'register')
                                @foreach($errors->get('student_number') as $message)
                                    <p class="lt-field-error">{{ $message }}</p>
                                @endforeach
                            @endif
                        </div>

                        <div class="lt-register-dialog__name-fields">
                            <div class="lt-field">
                                <label for="landing-first-name" class="lt-label">{{ __('First Name') }}</label>
                                <input id="landing-first-name" class="lt-input {{ old('_form') === 'register' && $errors->has('first_name') ? 'is-invalid' : '' }}" type="text" name="first_name" value="{{ old('_form') === 'register' ? old('first_name') : '' }}" required minlength="2" maxlength="60" autocomplete="given-name" aria-describedby="first-name-status">
                                <small id="first-name-status" class="lt-live-status" aria-live="polite"></small>
                                @if (old('_form') === 'register')
                                    @foreach($errors->get('first_name') as $message)
                                        <p class="lt-field-error">{{ $message }}</p>
                                    @endforeach
                                @endif
                            </div>

                            <div class="lt-field">
                                <label for="landing-last-name" class="lt-label">{{ __('Last Name') }}</label>
                                <input id="landing-last-name" class="lt-input {{ old('_form') === 'register' && $errors->has('last_name') ? 'is-invalid' : '' }}" type="text" name="last_name" value="{{ old('_form') === 'register' ? old('last_name') : '' }}" required minlength="2" maxlength="60" autocomplete="family-name" aria-describedby="last-name-status">
                                <small id="last-name-status" class="lt-live-status" aria-live="polite"></small>
                                @if (old('_form') === 'register')
                                    @foreach($errors->get('last_name') as $message)
                                        <p class="lt-field-error">{{ $message }}</p>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <div class="lt-field lt-register-dialog__field--full">
                            <label for="landing-email" class="lt-label">{{ __('School Email') }}</label>
                            <input id="landing-email" class="lt-input {{ old('_form') === 'register' && $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" value="{{ old('_form') === 'register' ? old('email') : '' }}" required maxlength="254" autocomplete="email" aria-describedby="email-status">
                            <span class="lt-hint">We will send a verification code to this email before creating your account.</span>
                            <small id="email-status" class="lt-live-status" aria-live="polite"></small>
                            @if (old('_form') === 'register')
                                @foreach($errors->get('email') as $message)
                                    <p class="lt-field-error">{{ $message }}</p>
                                @endforeach
                            @endif
                        </div>

                        <div class="lt-field">
                            <label for="landing-password" class="lt-label">{{ __('Create Password') }}</label>
                            <div class="lt-input-wrap">
                                <input id="landing-password" class="lt-input {{ old('_form') === 'register' && $errors->has('password') ? 'is-invalid' : '' }}" type="password" name="password" required minlength="12" autocomplete="new-password" aria-describedby="password-status">
                                <button type="button" class="lt-eye" data-toggle-password="#landing-password" aria-pressed="false" aria-label="Show password">
                                    <svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                                </button>
                            </div>
                            <small id="password-status" class="lt-live-status" aria-live="polite"></small>
                            @if (old('_form') === 'register')
                                @foreach($errors->get('password') as $message)
                                    <p class="lt-field-error">{{ $message }}</p>
                                @endforeach
                            @endif
                        </div>

                        <div class="lt-field">
                            <label for="landing-password-confirmation" class="lt-label">{{ __('Confirm Password') }}</label>
                            <div class="lt-input-wrap">
                                <input id="landing-password-confirmation" class="lt-input {{ old('_form') === 'register' && $errors->has('password_confirmation') ? 'is-invalid' : '' }}" type="password" name="password_confirmation" required minlength="12" autocomplete="new-password" aria-describedby="confirm-password-status">
                                <button type="button" class="lt-eye" data-toggle-password="#landing-password-confirmation" aria-pressed="false" aria-label="Show password">
                                    <svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                                </button>
                            </div>
                            <small id="confirm-password-status" class="lt-live-status" aria-live="polite"></small>
                            @if (old('_form') === 'register')
                                @foreach($errors->get('password_confirmation') as $message)
                                    <p class="lt-field-error">{{ $message }}</p>
                                @endforeach
                            @endif
                        </div>

                        <div class="lt-register-dialog__actions">
                            <a class="lt-link" href="{{ route('login') }}">{{ __('Already registered?') }}</a>
                            <button type="submit" class="lt-btn lt-btn--primary" data-loading-text="Sending code...">{{ __('Register') }}</button>
                        </div>
                    </form>
                </div>
            </dialog>
        @endif

        @if (Route::has('login'))
            <dialog id="login-dialog" class="lt-dialog lt-dialog--login" aria-labelledby="login-dialog-title" @if (old('_form') === 'login') data-autoopen @endif>
                <div class="lt-dialog__body lt-dialog__body--login">
                    <button type="button" class="lt-search-filter__toggle lt-login-dialog__close" data-close-dialog aria-label="Close login form">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>
                    </button>
                    <div class="lt-login-dialog__head">
                        <h2 id="login-dialog-title" class="lt-dialog__title">Welcome back</h2>
                        <p class="lt-dialog__text">Log in to your LogiTrack account.</p>
                    </div>

                    <form id="landing-login-form" method="POST" action="{{ route('login') }}" class="lt-form" data-submit-guard>
                        @csrf
                        <input type="hidden" name="_form" value="login">

                        <div class="lt-field">
                            <label for="landing-login-email" class="lt-label">{{ __('Email') }}</label>
                            <input id="landing-login-email" class="lt-input {{ $errors->has('email') ? 'is-invalid' : '' }}" type="email" name="email" value="{{ old('_form') === 'login' ? old('email') : '' }}" required autocomplete="username">
                            @if (old('_form') === 'login')
                                @foreach($errors->get('email') as $message)
                                    <p class="lt-field-error">{{ $message }}</p>
                                @endforeach
                            @endif
                        </div>

                        <div class="lt-field">
                            <label for="landing-login-password" class="lt-label">{{ __('Password') }}</label>
                            <div class="lt-input-wrap">
                                <input id="landing-login-password" class="lt-input {{ $errors->has('password') ? 'is-invalid' : '' }}" type="password" name="password" required autocomplete="current-password">
                                <button type="button" class="lt-eye" data-toggle-password="#landing-login-password" aria-pressed="false" aria-label="Show password">
                                    <svg class="lt-eye__on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>
                                    <svg class="lt-eye__off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.7 10.7 0 0 1 12 5c6.4 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17 17 0 0 0 2 12s3.6 7 10 7a10 10 0 0 0 4.4-1"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>
                                </button>
                            </div>
                            @if (old('_form') === 'login')
                                @foreach($errors->get('password') as $message)
                                    <p class="lt-field-error">{{ $message }}</p>
                                @endforeach
                            @endif
                        </div>

                        <div class="lt-auth__row">
                            <label for="landing-login-remember" class="lt-check">
                                <input id="landing-login-remember" type="checkbox" name="remember" value="1">
                                <span>{{ __('Remember me') }}</span>
                            </label>

                            @if (Route::has('password.request'))
                                <a class="lt-link" href="{{ route('password.request') }}">{{ __('Forgot your password?') }}</a>
                            @endif
                        </div>

                        <button type="submit" class="lt-btn lt-btn--primary lt-btn--lg" style="width:100%;" data-loading-text="Logging in...">
                            {{ __('Log in') }}
                        </button>
                    </form>

                    @if (Route::has('register'))
                        <p class="lt-auth__foot">
                            New here?
                            <button type="button" class="lt-link lt-login-dialog__register" data-close-dialog data-open-dialog="#register-dialog">{{ __('Create an account') }}</button>
                        </p>
                    @endif
                </div>
            </dialog>
        @endif

        <footer class="lt-footer">
            <div class="lt-footer__inner">
                <span class="lt-brand" style="font-size: 1.05rem;">
                    <span class="lt-brand__mark" style="width: 26px; height: 26px; font-size: .85rem;">L</span>
                    LogiTrack
                </span>
                <span>&copy; {{ date('Y') }} LogiTrack. Capstone repository &amp; decision support.</span>
            </div>
        </footer>

        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('landing-register-form');
            if (!form) return;

            const fields = {
                student: document.getElementById('landing-student-number'),
                first: document.getElementById('landing-first-name'),
                last: document.getElementById('landing-last-name'),
                email: document.getElementById('landing-email'),
                password: document.getElementById('landing-password'),
                confirmation: document.getElementById('landing-password-confirmation')
            };
            if (Object.values(fields).some(field => !field)) return;

            const checkUrl = form.dataset.checkUrl;
            let studentTimer, emailTimer;
            let studentRequest = 0, emailRequest = 0;
            let studentState = 'unchecked', emailState = 'unchecked';
            let submitting = false;

            // Show red warnings only. Successful checks remain visually quiet.
            function showStatus(id, message) {
                const el = document.getElementById(id);
                if (!el) return;
                el.textContent = message || '';
                el.style.display = message ? 'block' : 'none';
                el.style.color = '#dc2626';
            }

            function checkStudentFormat() {
                const value = fields.student.value;
                const exact = /^[0-9]{9}$/.test(value);
                const repeated = /^([0-9])\1{8}$/.test(value);
                const valid = exact && !repeated;
                const message = !value ? '' : exact
                    ? (repeated ? 'A student number cannot contain the same digit nine times.' : '')
                    : 'Student number must contain exactly 9 digits.';
                fields.student.setCustomValidity(message);
                showStatus('student-number-status', message);
                return valid;
            }

            async function checkStudentAvailability() {
                const value = fields.student.value;
                const requestId = ++studentRequest;
                if (!checkStudentFormat()) {
                    studentState = 'invalid';
                    return false;
                }
                try {
                    const query = new URLSearchParams({ student_number: value });
                    const response = await fetch(`${checkUrl}?${query}`, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    if (!response.ok) throw new Error('Student number availability check failed.');
                    const data = await response.json();
                    if (requestId !== studentRequest || fields.student.value !== value) return false;

                    let message = '';
                    if (data.student_number === 'taken') {
                        studentState = 'taken';
                        message = 'This student number already has a registered account.';
                    } else if (data.student_number === 'not_in_masterlist') {
                        studentState = 'not_in_masterlist';
                        message = 'This student number does not exist in the student masterlist.';
                    } else if (data.student_number === 'available') {
                        studentState = 'available';
                    } else {
                        throw new Error('Unexpected student number check response.');
                    }
                    fields.student.setCustomValidity(message);
                    showStatus('student-number-status', message);
                    return !message;
                } catch (error) {
                    console.error(error);
                    if (requestId === studentRequest) {
                        studentState = 'error';
                        const message = 'Unable to check this student number. Please try again.';
                        fields.student.setCustomValidity(message);
                        showStatus('student-number-status', message);
                    }
                    return false;
                }
            }

            fields.student.addEventListener('input', function () {
                this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9);
                studentState = 'unchecked';
                fields.student.setCustomValidity('');
                checkStudentFormat();
                clearTimeout(studentTimer);
                if (/^[0-9]{9}$/.test(this.value)) {
                    studentTimer = setTimeout(checkStudentAvailability, 450);
                }
            });

            // Name checks: permit letters, spaces, apostrophes and hyphens.
            const namePattern = /^\p{L}+(?:[ '-]\p{L}+)*$/u;
            const keyboardPattern = /qwerty|asdfgh|zxcvbn|qazwsx|wsxedc|abcdef|ghijkl|poiuyt|lkjhgfd|mnbvcx|plmokn|oiuytr|asdfjkl/i;
            const vowels = /[aeiouyáéíóúàèìòùâêîôûäëïöü]/giu;

            function cleanName(input) {
                input.value = input.value
                    .replace(/[^\p{L} '-]/gu, '')
                    .replace(/[ '-]{2,}/g, match => match[0])
                    .replace(/^[ '-]+/, '');
            }

            function randomLookingName(value) {
                const compact = value.replace(/[ '-]/g, '').toLocaleLowerCase();
                if (/(.)\1{2,}/u.test(compact) || keyboardPattern.test(compact)) return true;
                const chars = Array.from(compact);
                const unique = new Set(chars).size;
                if (chars.length >= 7 && unique < 4) return true;
                if (chars.length >= 5 && unique < 3) return true;
                const words = value.toLocaleLowerCase().split(/[ '-]+/).filter(Boolean);
                for (const word of words) {
                    const length = Array.from(word).length;
                    const vowelCount = (word.match(vowels) || []).length;
                    if (length >= 4 && vowelCount === 0) return true;
                    if (length >= 6 && (vowelCount / length < .16 || vowelCount / length > .72)) return true;
                    if (/[bcdfghjklmnpqrstvwxz]{4,}/i.test(word)) return true;
                    if (length >= 6 && /^(.)(.)\1\2(?:\1\2)+$/u.test(word)) return true;
                    if (length >= 8 && new Set(Array.from(word)).size <= 3) return true;
                }
                return /qwe|wer|ert|rty|tyu|yui|uio|asd|sdf|dfg|fgh|ghj|hjk|jkl|zxc|xcv|cvb|vbn/i.test(compact);
            }

            function checkName(input, statusId, label) {
                const value = input.value.trim();
                const valid = value.length >= 2 && value.length <= 60 &&
                    namePattern.test(value) && !randomLookingName(value);
                const message = value && !valid
                    ? `${label} looks like random or invalid text. Enter a valid name.` : '';
                input.setCustomValidity(message);
                showStatus(statusId, message);
                return valid;
            }

            [[fields.first, 'first-name-status', 'First name'],
             [fields.last, 'last-name-status', 'Last name']].forEach(([input, id, label]) => {
                input.addEventListener('input', function () {
                    cleanName(this);
                    checkName(this, id, label);
                });
                input.addEventListener('blur', function () {
                    this.value = this.value.trim().replace(/\s+/g, ' ');
                    checkName(this, id, label);
                });
            });

            function checkEmailFormat() {
                const input = fields.email;
                const value = input.value.trim().toLowerCase();
                const pattern = /^[a-z0-9.!#$%&'*+/=?^_`{|}~-]+@[a-z0-9](?:[a-z0-9-]*[a-z0-9])?(?:\.[a-z0-9](?:[a-z0-9-]*[a-z0-9])?)+$/i;
                const valid = value !== '' && value.length <= 254 && pattern.test(value);
                const message = value && !valid ? 'Enter a valid email address. Check the spelling and domain.' : '';
                input.setCustomValidity(message);
                showStatus('email-status', message);
                return valid;
            }

            async function checkEmailAvailability() {
                const value = fields.email.value.trim().toLowerCase();
                const requestId = ++emailRequest;
                if (!checkEmailFormat()) {
                    emailState = 'invalid';
                    return false;
                }
                try {
                    const query = new URLSearchParams({ email: value });
                    const response = await fetch(`${checkUrl}?${query}`, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin'
                    });
                    if (!response.ok) throw new Error('Email availability check failed.');
                    const data = await response.json();
                    if (requestId !== emailRequest || fields.email.value.trim().toLowerCase() !== value) return false;
                    if (data.email === 'taken') {
                        emailState = 'taken';
                        const message = 'This email address is already registered. Please use another email.';
                        fields.email.setCustomValidity(message);
                        showStatus('email-status', message);
                        return false;
                    }
                    if (data.email !== 'available') throw new Error('Unexpected email check response.');
                    emailState = 'available';
                    fields.email.setCustomValidity('');
                    showStatus('email-status', '');
                    return true;
                } catch (error) {
                    console.error(error);
                    if (requestId === emailRequest) {
                        emailState = 'error';
                        const message = 'Unable to check this email address. Please try again.';
                        fields.email.setCustomValidity(message);
                        showStatus('email-status', message);
                    }
                    return false;
                }
            }

            fields.email.addEventListener('input', function () {
                this.value = this.value.replace(/\s/g, '').toLowerCase();
                emailState = 'unchecked';
                this.setCustomValidity('');
                checkEmailFormat();
                clearTimeout(emailTimer);
                if (checkEmailFormat()) emailTimer = setTimeout(checkEmailAvailability, 500);
            });
            fields.email.addEventListener('blur', function () {
                this.value = this.value.trim().toLowerCase();
                if (checkEmailFormat()) checkEmailAvailability();
            });

            function checkPassword() {
                const value = fields.password.value;
                const valid = value.length >= 12 && /[A-Z]/.test(value) &&
                    /[a-z]/.test(value) && /[0-9]/.test(value) && /[^A-Za-z0-9]/.test(value);
                const message = value && !valid
                    ? 'Password must have at least 12 characters, uppercase, lowercase, a number and a symbol.' : '';
                fields.password.setCustomValidity(message);
                showStatus('password-status', message);
                checkConfirmation();
                return valid;
            }

            function checkConfirmation() {
                const value = fields.confirmation.value;
                const valid = value !== '' && value === fields.password.value;
                const message = value && !valid ? 'Passwords do not match.' : '';
                fields.confirmation.setCustomValidity(message);
                showStatus('confirm-password-status', message);
                return valid;
            }
            fields.password.addEventListener('input', checkPassword);
            fields.confirmation.addEventListener('input', checkConfirmation);

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                if (submitting) return;

                fields.email.value = fields.email.value.trim().toLowerCase();
                fields.first.value = fields.first.value.trim().replace(/\s+/g, ' ');
                fields.last.value = fields.last.value.trim().replace(/\s+/g, ' ');

                const localValid = [
                    checkStudentFormat(),
                    checkName(fields.first, 'first-name-status', 'First name'),
                    checkName(fields.last, 'last-name-status', 'Last name'),
                    checkEmailFormat(),
                    checkPassword(),
                    checkConfirmation()
                ].every(Boolean);

                if (!localValid) {
                    form.reportValidity();
                    return;
                }

                // Recheck both values immediately before submitting.
                const remoteValid = await Promise.all([
                    checkStudentAvailability(),
                    checkEmailAvailability()
                ]);
                if (!remoteValid.every(Boolean) || !form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                submitting = true;
                const button = form.querySelector('button[type="submit"]');
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Creating account...';
                }
                HTMLFormElement.prototype.submit.call(form);
            });
        });
        </script>
    </body>
</html>