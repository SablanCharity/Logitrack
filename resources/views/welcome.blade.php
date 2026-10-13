<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'LogiTrack') }} · Capstone Repository</title>

        @vite(['resources/css/logitrack.css', 'resources/js/logitrack.js'])
        
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
                                <a href="{{ url('/dashboard') }}" class="lt-btn lt-btn--gold lt-btn--sm">
                                    Dashboard
                                </a>
                            @else
                                <a href="{{ route('login') }}" class="lt-btn lt-btn--outline-light lt-btn--sm">
                                    Log in
                                </a>

                                @if (Route::has('register'))
                                    <a href="{{ route('register') }}" class="lt-btn lt-btn--gold lt-btn--sm">
                                        Register
                                    </a>
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
                                    <a href="{{ url('/dashboard') }}" class="lt-btn lt-btn--gold lt-btn--lg">
                                        Go to Dashboard
                                    </a>
                                @else
                                    @if (Route::has('register'))
                                        <a href="{{ route('register') }}" class="lt-btn lt-btn--gold lt-btn--lg">
                                            Create your account
                                        </a>
                                    @endif

                                    <a href="{{ route('login') }}" class="lt-btn lt-btn--outline-light lt-btn--lg">
                                        Log in
                                    </a>
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
                    <p data-reveal>Ask your adviser for the class code, then create your account.</p>

                    <div data-reveal>
                        @auth
                            <a href="{{ url('/dashboard') }}" class="lt-btn lt-btn--gold lt-btn--lg">Go to Dashboard</a>
                        @else
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="lt-btn lt-btn--gold lt-btn--lg">Register</a>
                            @endif
                            <a href="{{ route('login') }}" class="lt-btn lt-btn--outline-light lt-btn--lg">Log in</a>
                        @endauth
                    </div>
                </section>
            @endif

        </main>

        <footer class="lt-footer">
            <div class="lt-footer__inner">
                <span class="lt-brand" style="font-size: 1.05rem;">
                    <span class="lt-brand__mark" style="width: 26px; height: 26px; font-size: .85rem;">L</span>
                    LogiTrack
                </span>
                <span>&copy; {{ date('Y') }} LogiTrack. Capstone repository &amp; decision support.</span>
            </div>
        </footer>

    </body>
</html>