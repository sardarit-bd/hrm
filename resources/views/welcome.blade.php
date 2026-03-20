<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'HRMS') }} - Modern HR Management</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Styles / Scripts -->
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    <!-- Fallback tailwind setup for fresh installs without build/dev process yet -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    @endif
</head>

<body class="antialiased bg-slate-50 text-slate-900 font-sans selection:bg-brand-500 selection:text-white">
    <!-- Navbar -->
    <header class="absolute inset-x-0 top-0 z-50">
        <nav class="flex items-center justify-between p-6 lg:px-8 max-w-7xl mx-auto" aria-label="Global">
            <div class="flex lg:flex-1">
                <a href="/" class="-m-1.5 p-1.5 flex items-center gap-2">
                    <span class="sr-only">HRMS</span>
                    <div class="bg-brand-600 p-2 rounded-lg text-white">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                    </div>
                    <span class="font-bold text-xl tracking-tight text-slate-900">HRMS<span class="text-brand-600">Pro</span></span>
                </a>
            </div>
            <div class="hidden lg:flex lg:gap-x-12">
                <a href="#features" class="text-sm font-semibold leading-6 text-slate-700 hover:text-brand-600 transition">Features</a>
                <a href="#solutions" class="text-sm font-semibold leading-6 text-slate-700 hover:text-brand-600 transition">Solutions</a>
                <a href="#pricing" class="text-sm font-semibold leading-6 text-slate-700 hover:text-brand-600 transition">Pricing</a>
            </div>
            <div class="hidden lg:flex lg:flex-1 lg:justify-end lg:gap-x-4 items-center">
                @if (Route::has('login'))
                @auth
                <a href="{{ url('/dashboard') }}" class="text-sm font-semibold leading-6 text-slate-900 hover:text-brand-600 transition">Go to Dashboard <span aria-hidden="true">&rarr;</span></a>
                @else
                <a href="{{ route('login') }}" class="text-sm font-semibold leading-6 text-slate-700 hover:text-brand-600 transition">Log in</a>
                @if (Route::has('register'))
                <a href="{{ route('register') }}" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 transition">Sign up</a>
                @endif
                @endauth
                @endif
            </div>
        </nav>
    </header>

    <main>
        <!-- Hero section -->
        <div class="relative isolate pt-14 overflow-hidden">
            <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80" aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#93c5fd] to-[#3b82f6] opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem]"></div>
            </div>

            <div class="py-24 sm:py-32 lg:pb-40">
                <div class="mx-auto max-w-7xl px-6 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <div class="mb-8 flex justify-center">
                            <span class="relative rounded-full px-4 py-1.5 text-sm leading-6 text-brand-700 bg-brand-50 ring-1 ring-inset ring-brand-600/20">
                                Announcing our next generation HR platform. <a href="#" class="font-semibold text-brand-600"><span class="absolute inset-0" aria-hidden="true"></span>Read more <span aria-hidden="true">&rarr;</span></a>
                            </span>
                        </div>
                        <h1 class="text-4xl font-extrabold tracking-tight text-slate-900 sm:text-6xl mb-6">
                            Elevate your workforce with intelligent HR
                        </h1>
                        <p class="mt-6 text-lg leading-8 text-slate-600 max-w-2xl mx-auto">
                            Empower your HR team with a comprehensive platform for payroll, time tracking, employee records, and performance reviews. Focus on people, not paperwork.
                        </p>
                        <div class="mt-10 flex items-center justify-center gap-x-6">
                            @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="rounded-lg bg-brand-600 px-6 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 transition">Get started for free</a>
                            @endif
                            <a href="#features" class="text-sm font-semibold leading-6 text-slate-900 hover:text-brand-600 transition flex items-center gap-2">
                                <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.91 11.672a.375.375 0 010 .656l-5.603 3.113a.375.375 0 01-.557-.328V8.887c0-.286.307-.466.557-.327l5.603 3.112z" />
                                </svg>
                                See how it works
                            </a>
                        </div>
                    </div>

                    <!-- Abstract Dashboard Illustration -->
                    <div class="mt-16 flow-root sm:mt-24">
                        <div class="-m-2 rounded-2xl bg-slate-900/5 p-2 ring-1 ring-inset ring-slate-900/10 lg:-m-4 lg:rounded-3xl lg:p-4">
                            <div class="bg-white rounded-xl shadow-2xl ring-1 ring-slate-900/10 overflow-hidden flex flex-col md:flex-row" style="min-height: 500px;">
                                <!-- Sidebar -->
                                <div class="w-full md:w-64 bg-slate-50 border-r border-slate-100 p-6 hidden md:flex flex-col gap-6">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="h-8 w-8 bg-brand-600 rounded-lg"></div>
                                        <div class="h-4 w-24 bg-slate-200 rounded"></div>
                                    </div>
                                    <div class="space-y-3">
                                        <div class="h-8 w-full bg-brand-50 rounded-md border border-brand-100 flex items-center px-3 gap-3">
                                            <div class="h-4 w-4 bg-brand-400 rounded-sm"></div>
                                            <div class="h-3 w-20 bg-brand-600 rounded"></div>
                                        </div>
                                        <div class="h-8 w-full bg-transparent flex items-center px-3 gap-3">
                                            <div class="h-4 w-4 bg-slate-300 rounded-sm"></div>
                                            <div class="h-3 w-16 bg-slate-400 rounded"></div>
                                        </div>
                                        <div class="h-8 w-full bg-transparent flex items-center px-3 gap-3">
                                            <div class="h-4 w-4 bg-slate-300 rounded-sm"></div>
                                            <div class="h-3 w-24 bg-slate-400 rounded"></div>
                                        </div>
                                        <div class="h-8 w-full bg-transparent flex items-center px-3 gap-3">
                                            <div class="h-4 w-4 bg-slate-300 rounded-sm"></div>
                                            <div class="h-3 w-16 bg-slate-400 rounded"></div>
                                        </div>
                                    </div>
                                    <div class="mt-auto pt-6 border-t border-slate-200 flex items-center gap-3">
                                        <div class="h-10 w-10 bg-slate-300 rounded-full"></div>
                                        <div class="space-y-2 flex-1">
                                            <div class="h-3 w-full bg-slate-400 rounded"></div>
                                            <div class="h-2 w-2/3 bg-slate-300 rounded"></div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Main Content Area -->
                                <div class="flex-1 p-6 md:p-10 bg-white">
                                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
                                        <div>
                                            <div class="h-6 w-48 bg-slate-800 rounded mb-2"></div>
                                            <div class="h-4 w-64 bg-slate-400 rounded"></div>
                                        </div>
                                        <div class="h-10 w-32 bg-brand-600 rounded-lg"></div>
                                    </div>

                                    <!-- Stats Row -->
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                                        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                                            <div class="flex justify-between items-start mb-4">
                                                <div class="h-10 w-10 bg-brand-100 rounded-lg flex items-center justify-center">
                                                    <div class="h-5 w-5 bg-brand-600 rounded-sm"></div>
                                                </div>
                                                <div class="h-6 w-12 bg-green-100 rounded-full"></div>
                                            </div>
                                            <div class="h-4 w-20 bg-slate-400 rounded mb-2"></div>
                                            <div class="h-8 w-16 bg-slate-800 rounded"></div>
                                        </div>
                                        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm">
                                            <div class="flex justify-between items-start mb-4">
                                                <div class="h-10 w-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                                    <div class="h-5 w-5 bg-amber-600 rounded-sm"></div>
                                                </div>
                                                <div class="h-6 w-12 bg-green-100 rounded-full"></div>
                                            </div>
                                            <div class="h-4 w-24 bg-slate-400 rounded mb-2"></div>
                                            <div class="h-8 w-20 bg-slate-800 rounded"></div>
                                        </div>
                                        <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-sm hidden sm:block">
                                            <div class="flex justify-between items-start mb-4">
                                                <div class="h-10 w-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                                    <div class="h-5 w-5 bg-emerald-600 rounded-sm"></div>
                                                </div>
                                            </div>
                                            <div class="h-4 w-24 bg-slate-400 rounded mb-2"></div>
                                            <div class="h-8 w-24 bg-slate-800 rounded"></div>
                                        </div>
                                    </div>

                                    <!-- Table Area -->
                                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                                        <div class="border-b border-slate-200 p-4 flex justify-between items-center bg-slate-50">
                                            <div class="h-5 w-32 bg-slate-700 rounded"></div>
                                            <div class="h-8 w-24 bg-white border border-slate-300 rounded-md"></div>
                                        </div>
                                        <div class="p-0">
                                            <!-- Row 1 -->
                                            <div class="flex items-center justify-between p-4 border-b border-slate-100 hover:bg-slate-50">
                                                <div class="flex items-center gap-4">
                                                    <div class="h-10 w-10 bg-slate-200 rounded-full"></div>
                                                    <div class="space-y-2">
                                                        <div class="h-4 w-32 bg-slate-700 rounded"></div>
                                                        <div class="h-3 w-24 bg-slate-400 rounded"></div>
                                                    </div>
                                                </div>
                                                <div class="h-6 w-20 bg-emerald-100 rounded-full hidden sm:block"></div>
                                                <div class="h-4 w-16 bg-slate-400 rounded"></div>
                                            </div>
                                            <!-- Row 2 -->
                                            <div class="flex items-center justify-between p-4 border-b border-slate-100 hover:bg-slate-50">
                                                <div class="flex items-center gap-4">
                                                    <div class="h-10 w-10 bg-slate-200 rounded-full"></div>
                                                    <div class="space-y-2">
                                                        <div class="h-4 w-28 bg-slate-700 rounded"></div>
                                                        <div class="h-3 w-32 bg-slate-400 rounded"></div>
                                                    </div>
                                                </div>
                                                <div class="h-6 w-20 bg-emerald-100 rounded-full hidden sm:block"></div>
                                                <div class="h-4 w-16 bg-slate-400 rounded"></div>
                                            </div>
                                            <!-- Row 3 -->
                                            <div class="flex items-center justify-between p-4 hover:bg-slate-50">
                                                <div class="flex items-center gap-4">
                                                    <div class="h-10 w-10 bg-slate-200 rounded-full"></div>
                                                    <div class="space-y-2">
                                                        <div class="h-4 w-36 bg-slate-700 rounded"></div>
                                                        <div class="h-3 w-20 bg-slate-400 rounded"></div>
                                                    </div>
                                                </div>

</html>