<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
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
            theme: {
                extend: {
                    colors: {
                        gray: {
                            850: '#1f2937',
                            900: '#111827',
                            950: '#030712',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-950 text-gray-100 font-sans antialiased min-h-screen flex flex-col selection:bg-indigo-500/30">

    <div x-data="scanner()">
        <!-- Header -->
        <header class="bg-gray-900/50 backdrop-blur-md border-b border-white/5 sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 text-white font-bold text-lg shadow-lg shadow-indigo-500/20">
                        L
                    </div>
                    <h1 class="text-xl font-semibold tracking-tight bg-clip-text text-transparent bg-gradient-to-r from-white to-gray-400">Laravel Lens</h1>
                </div>
                <div class="flex items-center gap-4">
                    <a href="https://github.com/webcrafts-studio/lens-for-laravel" target="_blank" class="text-sm font-medium text-gray-400 hover:text-white transition-colors">Documentation</a>
                    <span class="inline-flex items-center rounded-md bg-emerald-400/10 px-2 py-1 text-xs font-medium text-emerald-400 ring-1 ring-inset ring-emerald-400/20">
                        Local Environment
                    </span>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-4xl mx-auto space-y-12">
                
                <!-- Hero Section & Controls -->
                <div class="text-center space-y-6">
                    <h2 class="text-4xl font-extrabold tracking-tight sm:text-5xl">Accessibility Auditor</h2>
                    <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                        Discover and fix accessibility issues in your Laravel application instantly. Enter a local URL to begin the WCAG compliance scan.
                    </p>
                    
                    <form @submit.prevent="performScan" class="max-w-xl mx-auto mt-8 flex gap-3">
                        <div class="relative flex-grow">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <svg class="h-5 w-5 text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM6.75 9.25a.75.75 0 000 1.5h4.59l-2.1 1.95a.75.75 0 001.02 1.1l3.5-3.25a.75.75 0 000-1.1l-3.5-3.25a.75.75 0 10-1.02 1.1l2.1 1.95H6.75z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <input 
                                type="url" 
                                x-model="url" 
                                required
                                class="block w-full rounded-xl border-0 py-3.5 pl-11 pr-4 bg-gray-900 text-white ring-1 ring-inset ring-white/10 focus:ring-2 focus:ring-inset focus:ring-indigo-500 sm:text-sm sm:leading-6 placeholder:text-gray-500 transition-shadow" 
                                placeholder="http://localhost"
                            >
                        </div>
                        <button 
                            type="submit" 
                            :disabled="isLoading"
                            class="inline-flex items-center justify-center rounded-xl bg-indigo-500 px-6 py-3.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-500 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            <span x-show="!isLoading">Scan Now</span>
                            <span x-show="isLoading" class="flex items-center gap-2" x-cloak>
                                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Scanning...
                            </span>
                        </button>
                    </form>

                    <!-- Error Alert -->
                    <div x-show="error" x-cloak class="rounded-xl bg-red-500/10 p-4 border border-red-500/20 max-w-xl mx-auto mt-6 text-left">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-400">Scan Failed</h3>
                                <div class="mt-2 text-sm text-red-400/80">
                                    <p x-text="error"></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Area -->
                <div x-show="hasResults" x-cloak class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-500">
                    
                    <!-- Summary Cards -->
                    <dl class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                        <div class="overflow-hidden rounded-2xl bg-gray-900 border border-white/5 px-4 py-5 shadow sm:p-6 relative group">
                            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <dt class="truncate text-sm font-medium text-gray-400">Total Issues</dt>
                            <dd class="mt-2 text-3xl font-bold tracking-tight text-white" x-text="totalIssues"></dd>
                        </div>
                        <div class="overflow-hidden rounded-2xl bg-gray-900 border border-white/5 px-4 py-5 shadow sm:p-6 relative group">
                            <div class="absolute inset-0 bg-gradient-to-br from-red-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <dt class="truncate text-sm font-medium text-gray-400">Critical Issues</dt>
                            <dd class="mt-2 text-3xl font-bold tracking-tight text-red-400" x-text="criticalIssues"></dd>
                        </div>
                        <div class="overflow-hidden rounded-2xl bg-gray-900 border border-white/5 px-4 py-5 shadow sm:p-6 relative group">
                            <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                            <dt class="truncate text-sm font-medium text-gray-400">Moderate / Minor</dt>
                            <dd class="mt-2 text-3xl font-bold tracking-tight text-yellow-400" x-text="otherIssues"></dd>
                        </div>
                    </dl>

                    <!-- Issue List -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium leading-6 text-white border-b border-white/10 pb-4">Detailed Findings</h3>
                        
                        <template x-if="issues.length === 0">
                            <div class="text-center py-12 bg-gray-900/50 rounded-2xl border border-white/5 border-dashed">
                                <svg class="mx-auto h-12 w-12 text-emerald-400/50 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <h3 class="text-sm font-medium text-white">No issues found</h3>
                                <p class="mt-1 text-sm text-gray-400">Great job! Your page is highly accessible.</p>
                            </div>
                        </template>

                        <div class="space-y-4">
                            <template x-for="(issue, index) in issues" :key="index">
                                <div class="bg-gray-900 rounded-2xl border border-white/5 shadow-sm overflow-hidden hover:border-white/10 transition-colors">
                                    <div class="p-6 sm:p-8">
                                        <div class="flex items-start justify-between gap-4">
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-3 mb-2">
                                                    <span 
                                                        class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ring-1 ring-inset uppercase tracking-wider"
                                                        :class="getBadgeColor(issue.impact)"
                                                        x-text="issue.impact"
                                                    ></span>
                                                    <span class="text-sm font-mono text-gray-500" x-text="issue.id"></span>
                                                </div>
                                                <h4 class="text-base font-medium text-white" x-text="issue.description"></h4>
                                            </div>
                                            <a :href="issue.helpUrl" target="_blank" class="flex-shrink-0 text-gray-500 hover:text-indigo-400 transition-colors" title="Read more about this rule">
                                                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M4.25 5.5a.75.75 0 00-.75.75v8.5c0 .414.336.75.75.75h8.5a.75.75 0 00.75-.75v-4a.75.75 0 011.5 0v4A2.25 2.25 0 0112.75 17h-8.5A2.25 2.25 0 012 14.75v-8.5A2.25 2.25 0 014.25 4h5a.75.75 0 010 1.5h-5z" clip-rule="evenodd" />
                                                    <path fill-rule="evenodd" d="M6.194 12.753a.75.75 0 001.06.053L16.5 4.44v2.81a.75.75 0 001.5 0v-4.5a.75.75 0 00-.75-.75h-4.5a.75.75 0 000 1.5h2.553l-9.056 8.194a.75.75 0 00-.053 1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </a>
                                        </div>

                                        <div class="mt-6">
                                            <p class="text-xs font-medium text-gray-400 mb-2 uppercase tracking-wider">Failing Element</p>
                                            <div class="bg-gray-950 rounded-lg p-4 border border-white/5 overflow-x-auto">
                                                <pre><code class="text-sm text-gray-300 font-mono whitespace-pre-wrap" x-text="issue.htmlSnippet"></code></pre>
                                            </div>
                                        </div>

                                        <div class="mt-6 border-t border-white/5 pt-6 flex items-center justify-between">
                                            <div>
                                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">Location</p>
                                                <!-- Placeholder for FileLocator result -->
                                                <template x-if="issue.fileName">
                                                    <div class="flex items-center gap-2">
                                                        <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                        </svg>
                                                        <span class="text-sm text-gray-300 font-mono" x-text="issue.fileName + ':' + issue.lineNumber"></span>
                                                    </div>
                                                </template>
                                                <template x-if="!issue.fileName">
                                                    <span class="text-sm text-gray-500 italic">File locator pending...</span>
                                                </template>
                                            </div>
                                            <div class="text-right">
                                                <p class="text-xs font-medium text-gray-400 uppercase tracking-wider mb-1">CSS Selector</p>
                                                <code class="text-xs text-gray-500 font-mono bg-gray-950 px-2 py-1 rounded" x-text="issue.selector"></code>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </template>
                        </div>
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
                        case 'critical': return 'bg-red-500/10 text-red-400 ring-red-500/20';
                        case 'serious': return 'bg-orange-500/10 text-orange-400 ring-orange-500/20';
                        case 'moderate': return 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20';
                        case 'minor': return 'bg-blue-500/10 text-blue-400 ring-blue-500/20';
                        default: return 'bg-gray-500/10 text-gray-400 ring-gray-500/20';
                    }
                }
            }));
        });
    </script>
</body>
</html>
