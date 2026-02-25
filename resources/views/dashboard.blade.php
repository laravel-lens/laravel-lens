<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Lens Accessibility Dashboard</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 dark:bg-slate-900 dark:text-slate-100 font-sans antialiased min-h-screen flex flex-col transition-colors duration-200" x-data="scanner()">

    <div class="flex-1 flex flex-col">
        <!-- Header -->
        <header class="bg-white dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700 sticky top-0 z-30 transition-colors duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-8 h-8 rounded bg-indigo-600 text-white font-bold text-lg">
                        L
                    </div>
                    <h1 class="text-xl font-semibold tracking-tight text-slate-900 dark:text-white">Laravel Lens</h1>
                </div>
                <div class="flex items-center gap-4">
                    <a href="https://github.com/webcrafts-studio/lens-for-laravel" target="_blank" class="text-sm font-medium text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition-colors hidden sm:block">Documentation</a>
                    <span class="inline-flex items-center rounded-md bg-slate-100 dark:bg-slate-700 px-2.5 py-0.5 text-xs font-medium text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600">
                        Local Environment
                    </span>
                    <!-- Theme Toggle -->
                    <button @click="toggleTheme" class="p-2 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white rounded-full hover:bg-slate-100 dark:hover:bg-slate-700 transition-colors" title="Toggle Dark/Light Mode">
                        <!-- Sun Icon (shows in dark mode) -->
                        <svg x-show="theme === 'dark'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <!-- Moon Icon (shows in light mode) -->
                        <svg x-show="theme === 'light'" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 py-10 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto space-y-8">
                
                <!-- Hero Section & Controls -->
                <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm p-8 sm:p-10 transition-colors duration-200">
                    <div class="max-w-2xl">
                        <h2 class="text-2xl font-bold text-slate-900 dark:text-white sm:text-3xl">Accessibility Auditor</h2>
                        <p class="mt-2 text-base text-slate-500 dark:text-slate-400">
                            Discover and fix accessibility issues in your Laravel application instantly. Enter a local URL to begin the WCAG compliance scan.
                        </p>
                    </div>
                    
                    <form @submit.prevent="performScan" class="mt-8 flex flex-col sm:flex-row gap-3">
                        <div class="relative flex-grow">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                <svg class="h-5 w-5 text-slate-400 dark:text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM6.75 9.25a.75.75 0 000 1.5h4.59l-2.1 1.95a.75.75 0 001.02 1.1l3.5-3.25a.75.75 0 000-1.1l-3.5-3.25a.75.75 0 10-1.02 1.1l2.1 1.95H6.75z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input 
                                type="url" 
                                x-model="url" 
                                required
                                class="block w-full rounded-lg border-0 py-3 pl-10 pr-4 text-slate-900 dark:text-white dark:bg-slate-900 ring-1 ring-inset ring-slate-300 dark:ring-slate-600 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 transition-colors duration-200" 
                                placeholder="http://localhost"
                            >
                        </div>
                        <button 
                            type="submit" 
                            :disabled="isLoading"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap"
                        >
                            <span x-show="!isLoading" class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Scan Now
                            </span>
                            <span x-show="isLoading" class="flex items-center gap-2" x-cloak>
                                <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Scanning...
                            </span>
                        </button>
                    </form>

                    <!-- Error Alert -->
                    <div x-show="error" x-cloak class="rounded-lg bg-red-50 dark:bg-red-900/30 p-4 border border-red-200 dark:border-red-800 mt-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-500 dark:text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800 dark:text-red-300">Scan Failed</h3>
                                <div class="mt-2 text-sm text-red-700 dark:text-red-400">
                                    <p x-text="error"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Area -->
                <div x-show="hasResults" x-cloak class="space-y-8">
                    
                    <!-- Summary Cards -->
                    <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div class="overflow-hidden rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-6 py-5 shadow-sm flex flex-col justify-between transition-colors duration-200">
                            <dt class="truncate text-sm font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                <div class="p-1.5 bg-slate-100 dark:bg-slate-700 rounded-md text-slate-600 dark:text-slate-300">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                Total Issues
                            </dt>
                            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white" x-text="totalIssues"></dd>
                        </div>
                        <div class="overflow-hidden rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-6 py-5 shadow-sm flex flex-col justify-between transition-colors duration-200">
                            <dt class="truncate text-sm font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                <div class="p-1.5 bg-red-50 dark:bg-red-900/30 rounded-md text-red-600 dark:text-red-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                </div>
                                Critical Issues
                            </dt>
                            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white" x-text="criticalIssues"></dd>
                        </div>
                        <div class="overflow-hidden rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 px-6 py-5 shadow-sm flex flex-col justify-between transition-colors duration-200">
                            <dt class="truncate text-sm font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2">
                                <div class="p-1.5 bg-blue-50 dark:bg-blue-900/30 rounded-md text-blue-600 dark:text-blue-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                Moderate / Minor
                            </dt>
                            <dd class="mt-2 text-3xl font-bold tracking-tight text-slate-900 dark:text-white" x-text="otherIssues"></dd>
                        </div>
                    </dl>

                    <!-- Issue List -->
                    <div class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-sm overflow-hidden transition-colors duration-200">
                        <div class="border-b border-slate-200 dark:border-slate-700 px-6 py-5">
                            <h3 class="text-base font-semibold leading-6 text-slate-900 dark:text-white">Detailed Findings</h3>
                        </div>
                        
                        <template x-if="issues.length === 0">
                            <div class="text-center py-12 px-6">
                                <svg class="mx-auto h-12 w-12 text-slate-300 dark:text-slate-600 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-sm font-medium text-slate-900 dark:text-white">No issues found</h3>
                                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Great job! Your page is highly accessible.</p>
                            </div>
                        </template>

                        <ul role="list" class="divide-y divide-slate-200 dark:divide-slate-700">
                            <template x-for="(issue, index) in issues" :key="index">
                                <li class="p-6 sm:p-8">
                                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                        <div class="flex-1 space-y-3">
                                            <div class="flex items-center gap-3">
                                                <span 
                                                    class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium uppercase tracking-wide"
                                                    :class="getBadgeColor(issue.impact)"
                                                >
                                                    <span x-html="getBadgeIcon(issue.impact)"></span>
                                                    <span x-text="issue.impact"></span>
                                                </span>
                                                <span class="text-sm font-mono text-slate-500 dark:text-slate-400" x-text="issue.id"></span>
                                            </div>
                                            <h4 class="text-base font-medium text-slate-900 dark:text-white" x-text="issue.description"></h4>
                                        </div>
                                        <a :href="issue.helpUrl" target="_blank" class="flex-shrink-0 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 dark:hover:text-indigo-300 transition-colors" title="Read more about this rule">
                                            Documentation
                                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5z" clip-rule="evenodd" />
                                                <path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 001.06.053L16.5 4.44v2.81a.75.75 0 001.5 0v-4.5a.75.75 0 00-.75-.75h-4.5a.75.75 0 000 1.5h2.553l-9.056 8.194a.75.75 0 00-.053 1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    </div>

                                    <div class="mt-4">
                                        <p class="text-xs font-medium text-slate-500 dark:text-slate-400 mb-2 uppercase tracking-wider">Failing Element</p>
                                        <div class="bg-slate-100 dark:bg-slate-950 rounded-md border border-slate-200 dark:border-slate-700 p-3 overflow-x-auto">
                                            <pre><code class="text-sm text-slate-800 dark:text-slate-300 font-mono whitespace-pre-wrap" x-text="issue.htmlSnippet"></code></pre>
                                        </div>
                                    </div>

                                    <div class="mt-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg p-4 border border-slate-200 dark:border-slate-700">
                                        <div>
                                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">File Location</p>
                                            <template x-if="issue.fileName">
                                                <div class="flex items-center gap-2 text-sm text-slate-900 dark:text-white font-medium">
                                                    <svg class="h-4 w-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                    <span class="font-mono bg-white dark:bg-slate-800 px-1.5 py-0.5 rounded border border-slate-200 dark:border-slate-600" x-text="issue.fileName + ':' + issue.lineNumber"></span>
                                                </div>
                                            </template>
                                            <template x-if="!issue.fileName">
                                                <div class="flex items-center gap-2 text-sm text-slate-400 dark:text-slate-500 italic">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    File locator pending...
                                                </div>
                                            </template>
                                        </div>
                                        <div class="sm:text-right">
                                            <p class="text-xs font-medium text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1.5">CSS Selector</p>
                                            <code class="text-xs text-slate-600 dark:text-slate-400 font-mono bg-white dark:bg-slate-800 px-2 py-1 rounded border border-slate-200 dark:border-slate-600 block" x-text="issue.selector"></code>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('scanner', () => ({
                url: '{{ url('/') }}',
                isLoading: false,
                hasResults: false,
                error: null,
                issues: [],
                theme: localStorage.getItem('theme') || 'light',
                
                init() {
                    // Initialize theme based on saved preference
                    document.documentElement.classList.toggle('dark', this.theme === 'dark');
                    
                    // Watch for changes and save to localStorage
                    this.$watch('theme', val => {
                        document.documentElement.classList.toggle('dark', val === 'dark');
                        localStorage.setItem('theme', val);
                    });
                },

                toggleTheme() {
                    this.theme = this.theme === 'light' ? 'dark' : 'light';
                },
                
                get totalIssues() { return this.issues.length; },
                get criticalIssues() { return this.issues.filter(i => i.impact === 'critical').length; },
                get otherIssues() { return this.issues.filter(i => i.impact !== 'critical').length; },

                async performScan() {
                    this.isLoading = true;
                    this.hasResults = false;
                    this.error = null;
                    this.issues = [];

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        const response = await fetch('{{ route('laravel-lens.scan') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({ url: this.url })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'An error occurred during scanning.');
                        }

                        this.issues = data.issues || [];
                        this.hasResults = true;
                    } catch (err) {
                        this.error = err.message;
                    } finally {
                        this.isLoading = false;
                    }
                },
                
                getBadgeColor(impact) {
                    switch(impact) {
                        case 'critical': return 'bg-red-100 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-400 dark:border-red-800/50';
                        case 'serious': 
                        case 'moderate': return 'bg-orange-100 text-orange-700 border-orange-200 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-800/50';
                        case 'minor': return 'bg-blue-100 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800/50';
                        default: return 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700';
                    }
                },

                getBadgeIcon(impact) {
                    switch(impact) {
                        case 'critical': return '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>';
                        case 'serious':
                        case 'moderate': return '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                        case 'minor': return '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                        default: return '<svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                    }
                }
            }));
        });
    </script>
</body>
</html>
