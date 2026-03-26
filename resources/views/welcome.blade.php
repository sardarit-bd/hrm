<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'HRMS') }} | Premium Management Workspace</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            sans: ['Plus Jakarta Sans', 'sans-serif'],
                            display: ['Space Grotesk', 'sans-serif'],
                        },
                        colors: {
                            brand: {
                                50: '#eef4ff',
                                100: '#d9e8ff',
                                200: '#b8d2ff',
                                500: '#2f6df6',
                                600: '#1f5ee8',
                                700: '#174dbc',
                            }
                        },
                        boxShadow: {
                            soft: '0 10px 30px rgba(15, 23, 42, 0.06)',
                            panel: '0 16px 40px rgba(15, 23, 42, 0.08)',
                        }
                    }
                }
            }
        </script>
    @endif

    <style>
        :root {
            color-scheme: light;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            background-image:
                radial-gradient(900px 520px at -5% -5%, rgba(47, 109, 246, .10), transparent 60%),
                radial-gradient(900px 520px at 105% 0%, rgba(148, 190, 255, .24), transparent 62%),
                linear-gradient(180deg, rgba(255, 255, 255, .95), rgba(248, 250, 252, 1));
        }

        .font-display {
            font-family: 'Space Grotesk', sans-serif;
        }

        @keyframes float-soft {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        @keyframes fade-rise {
            0% { opacity: 0; transform: translateY(14px); }
            100% { opacity: 1; transform: translateY(0); }
        }

        @keyframes orb-shift {
            0%, 100% { transform: translate3d(0, 0, 0); }
            50% { transform: translate3d(0, -12px, 0); }
        }

        .float-soft { animation: float-soft 6.5s ease-in-out infinite; }
        .fade-rise { animation: fade-rise .65s ease-out both; }
        .ambient-orb { animation: orb-shift 8s ease-in-out infinite; }

        @media (prefers-reduced-motion: reduce) {
            .float-soft, .fade-rise, .ambient-orb {
                animation: none !important;
            }
        }
    </style>
</head>

<body class="text-slate-900 antialiased selection:bg-brand-500/20 selection:text-brand-700">
    @php
        $roleCards = [
            ['role' => 'Super Admin', 'desc' => 'Organization-wide control, role governance, and strategic operational visibility.'],
            ['role' => 'General Manager', 'desc' => 'Approvals, planning, and executive insight into workforce and delivery health.'],
            ['role' => 'Project Manager', 'desc' => 'Project execution, delivery timelines, workload balance, and release confidence.'],
            ['role' => 'Team Leader', 'desc' => 'Team-level planning, leave coordination, and day-to-day execution oversight.'],
            ['role' => 'Software Engineer', 'desc' => 'Focused flow for tasks, attendance, leave requests, and payroll visibility.'],
        ];

        $featureCards = [
            ['title' => 'Unified HR + Delivery Workspace', 'desc' => 'Handle payroll, leave, project tracking, notifications, and operations from one platform.'],
            ['title' => 'Multi-Step Approval Engine', 'desc' => 'Support real workflows like Employee -> PM -> GM with audit-ready progression.'],
            ['title' => 'Operational Command Center', 'desc' => 'Give leaders real-time insight into blockers, team bandwidth, and project status.'],
            ['title' => 'Secure Salary Management', 'desc' => 'Protect compensation data with strict role-based access and traceable activity.'],
            ['title' => 'Topic-Based Anonymous Feedback', 'desc' => 'Collect structured sentiment to improve leadership decisions and team culture.'],
            ['title' => 'Enterprise Permission Model', 'desc' => 'Scale safely with robust role and permission boundaries across modules.'],
        ];
    @endphp

    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] focus:rounded-xl focus:bg-slate-900 focus:px-4 focus:py-2 focus:text-sm focus:font-semibold focus:text-white">
        Skip to main content
    </a>

    <header class="sticky top-0 z-50 border-b border-slate-200/70 bg-white/80 backdrop-blur-xl">
        <div class="mx-auto flex h-16 w-full max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
            <a href="/" class="flex items-center gap-3">
                <div class="grid h-9 w-9 place-items-center rounded-xl bg-brand-600 text-sm font-bold text-white shadow-soft">HR</div>
                <div>
                    <p class="font-display text-sm font-bold tracking-tight">{{ config('app.name', 'HRMS') }}</p>
                    <p class="text-xs text-slate-500">Premium Management Platform</p>
                </div>
            </a>

            <nav class="hidden items-center gap-8 md:flex" aria-label="Main navigation">
                <a href="#product" class="text-sm font-medium text-slate-600 transition hover:text-slate-900 focus:outline-none focus-visible:text-slate-900">Product</a>
                <a href="#roles" class="text-sm font-medium text-slate-600 transition hover:text-slate-900 focus:outline-none focus-visible:text-slate-900">Roles</a>
                <a href="#features" class="text-sm font-medium text-slate-600 transition hover:text-slate-900 focus:outline-none focus-visible:text-slate-900">Features</a>
                <a href="#workflow" class="text-sm font-medium text-slate-600 transition hover:text-slate-900 focus:outline-none focus-visible:text-slate-900">Workflow</a>
            </nav>

            <div class="hidden items-center gap-3 md:flex">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-soft transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                            Open Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                            Log in
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-xl bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-soft transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                                Get Started
                            </a>
                        @endif
                    @endauth
                @endif
            </div>

            <details class="group relative md:hidden">
                <summary class="list-none rounded-xl border border-slate-300 bg-white p-2 text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500" aria-label="Toggle menu">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </summary>
                <div class="absolute right-0 mt-2 w-64 rounded-2xl border border-slate-200 bg-white p-3 shadow-panel">
                    <a href="#product" class="block rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50">Product</a>
                    <a href="#roles" class="block rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50">Roles</a>
                    <a href="#features" class="block rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50">Features</a>
                    <a href="#workflow" class="block rounded-lg px-3 py-2 text-sm text-slate-700 transition hover:bg-slate-50">Workflow</a>
                    <hr class="my-2 border-slate-200">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">Open Dashboard</a>
                        @else
                            <a href="{{ route('login') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">Log in</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="block rounded-lg px-3 py-2 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">Get Started</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </details>
        </div>
    </header>

    <main id="main-content">
        <section id="product" class="relative overflow-hidden">
            <div class="pointer-events-none absolute -left-16 top-20 h-48 w-48 rounded-full bg-brand-200/35 blur-3xl ambient-orb"></div>
            <div class="pointer-events-none absolute -right-10 top-28 h-56 w-56 rounded-full bg-brand-100/70 blur-3xl ambient-orb"></div>

            <div class="mx-auto grid w-full max-w-7xl grid-cols-1 gap-10 px-4 pb-16 pt-12 sm:px-6 lg:grid-cols-12 lg:px-8 lg:pt-20">
                <div class="fade-rise lg:col-span-7">
                    <span class="inline-flex items-center gap-2 rounded-full border border-brand-200 bg-white px-3 py-1 text-xs font-semibold text-brand-700 shadow-soft">
                        Built for fast-moving software organizations
                    </span>

                    <h1 class="font-display mt-6 text-4xl font-bold leading-tight tracking-tight text-slate-900 sm:text-5xl lg:text-6xl">
                        Run your company operations from one polished control center.
                    </h1>

                    <p class="mt-6 max-w-2xl text-base leading-8 text-slate-600 sm:text-lg">
                        A premium management platform where executives and teams align on payroll, leave, projects, feedback, and approvals with clarity and speed.
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-soft transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                                Start Free
                            </a>
                        @endif
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                                Sign In
                            </a>
                        @endif
                    </div>

                    <div class="mt-10 grid grid-cols-1 gap-4 sm:grid-cols-3">
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft">
                            <p class="font-display text-2xl font-bold text-slate-900">99.9%</p>
                            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500">Uptime confidence</p>
                        </article>
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft">
                            <p class="font-display text-2xl font-bold text-slate-900">5+</p>
                            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500">Role-based views</p>
                        </article>
                        <article class="rounded-2xl border border-slate-200 bg-white p-4 shadow-soft">
                            <p class="font-display text-2xl font-bold text-slate-900">Real-time</p>
                            <p class="mt-1 text-xs font-medium uppercase tracking-wide text-slate-500">Ops visibility</p>
                        </article>
                    </div>
                </div>

                <div class="fade-rise lg:col-span-5">
                    <div class="float-soft rounded-3xl border border-slate-200 bg-white p-4 shadow-panel sm:p-6">
                        <div class="mb-4 flex items-center justify-between">
                            <p class="font-display text-sm font-semibold text-slate-900">Operations Pulse</p>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">Live</span>
                        </div>

                        <div class="space-y-3">
                            <article class="rounded-2xl border border-slate-200 p-3.5">
                                <p class="text-sm font-semibold text-slate-800">Payroll Prepared</p>
                                <p class="mt-1 text-xs text-slate-500">37 salaries verified for current cycle</p>
                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100"><span class="block h-full w-11/12 rounded-full bg-brand-500"></span></div>
                            </article>
                            <article class="rounded-2xl border border-slate-200 p-3.5">
                                <p class="text-sm font-semibold text-slate-800">Leave Review Queue</p>
                                <p class="mt-1 text-xs text-slate-500">4 pending approvals across PM and GM</p>
                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100"><span class="block h-full w-3/4 rounded-full bg-brand-500"></span></div>
                            </article>
                            <article class="rounded-2xl border border-slate-200 p-3.5">
                                <p class="text-sm font-semibold text-slate-800">Project Milestones</p>
                                <p class="mt-1 text-xs text-slate-500">6 deliveries on track this sprint</p>
                                <div class="mt-2 h-1.5 w-full overflow-hidden rounded-full bg-slate-100"><span class="block h-full w-4/5 rounded-full bg-brand-500"></span></div>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="roles" class="border-y border-slate-200 bg-white/75 py-16">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Built around the way software teams actually work</h2>
                    <p class="mt-4 text-base leading-8 text-slate-600">Each persona sees the right data, actions, and approval controls without clutter or operational noise.</p>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-5">
                    @foreach ($roleCards as $card)
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft transition hover:-translate-y-0.5 hover:shadow-panel">
                            <h3 class="font-display text-base font-semibold text-slate-900">{{ $card['role'] }}</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-600">{{ $card['desc'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="features" class="py-16">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="max-w-3xl">
                    <h2 class="font-display text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">Core capabilities designed for scale</h2>
                    <p class="mt-4 text-base leading-8 text-slate-600">From compensation to project delivery and feedback loops, every workflow is optimized for reliability and clarity.</p>
                </div>

                <div class="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($featureCards as $index => $card)
                        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft transition hover:shadow-panel">
                            <div class="mb-3 inline-flex h-9 w-9 items-center justify-center rounded-xl bg-brand-50 text-sm font-bold text-brand-700">
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </div>
                            <h3 class="font-display text-lg font-semibold text-slate-900">{{ $card['title'] }}</h3>
                            <p class="mt-2 text-sm leading-7 text-slate-600">{{ $card['desc'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section id="workflow" class="pb-16">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-soft sm:p-8">
                    <div class="max-w-3xl">
                        <h2 class="font-display text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">A workflow your leadership can trust</h2>
                        <p class="mt-3 text-sm leading-7 text-slate-600 sm:text-base">Automate multi-level approvals while keeping decisions transparent for employees and managers.</p>
                    </div>

                    <div class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-3">
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Step 01</p>
                            <h3 class="mt-2 font-display text-lg font-semibold text-slate-900">Employee Submits</h3>
                            <p class="mt-2 text-sm text-slate-600">Leave request is created and routed automatically with all context attached.</p>
                        </article>
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Step 02</p>
                            <h3 class="mt-2 font-display text-lg font-semibold text-slate-900">PM Reviews</h3>
                            <p class="mt-2 text-sm text-slate-600">Project Manager approves or requests changes, then forwards to GM.</p>
                        </article>
                        <article class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Step 03</p>
                            <h3 class="mt-2 font-display text-lg font-semibold text-slate-900">GM Finalizes</h3>
                            <p class="mt-2 text-sm text-slate-600">Final decision is recorded and notifications are sent instantly to stakeholders.</p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="pb-20">
            <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="rounded-3xl border border-slate-200 bg-gradient-to-r from-white via-brand-50/60 to-white p-6 shadow-soft sm:p-8 lg:flex lg:items-center lg:justify-between lg:gap-8">
                    <div class="max-w-2xl">
                        <h3 class="font-display text-2xl font-bold tracking-tight text-slate-900">Ready to modernize your company operations?</h3>
                        <p class="mt-3 text-sm leading-7 text-slate-600">Bring leadership, delivery, and HR workflows into one premium workspace built for software organizations.</p>
                    </div>
                    <div class="mt-6 flex flex-wrap items-center gap-3 lg:mt-0 lg:justify-end">
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-xl bg-brand-600 px-5 py-3 text-sm font-semibold text-white shadow-soft transition hover:bg-brand-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                                Create Account
                            </a>
                        @endif
                        @if (Route::has('login'))
                            <a href="{{ route('login') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-brand-500 focus-visible:ring-offset-2">
                                Go to Login
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200 bg-white/75">
        <div class="mx-auto flex w-full max-w-7xl flex-col gap-2 px-4 py-6 text-xs text-slate-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>{{ config('app.name', 'HRMS') }} &copy; {{ now()->year }}</p>
            <p>Designed for high-performing software companies</p>
        </div>
    </footer>
</body>

</html>
