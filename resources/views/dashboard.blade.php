<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Laravel Lens - Technical Auditor</title>
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
        /* Prevent crosshairs from interfering with clicks */
        .crosshair { pointer-events: none; user-select: none; }
    </style>
</head>
<body class="bg-white text-black dark:bg-black dark:text-neutral-200 font-sans antialiased min-h-screen flex flex-col border-t-[4px] border-t-[#FF2D20]" x-data="scanner()">

    <div class="flex-1 flex flex-col selection:bg-[#FF2D20] selection:text-white dark:selection:bg-[#FF2D20] dark:selection:text-white">
        <!-- Header -->
        <header class="border-b border-black dark:border-neutral-700 bg-white dark:bg-black sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center">
                        <svg class="w-8 h-8 text-[#FF2D20]" viewBox="0 0 100 100" fill="currentColor">
                            <path d="M72.036 50.134c-2.316-2.61-4.707-4.996-6.845-7.14a408.831 408.831 0 0 0-8.98-8.838l-4.57 5.152s2.615 3.018 7.643 8.358c2.44 2.593 5.093 5.432 7.72 8.337L51 74.073l-13.882-15.65c3.08-3.328 6.22-6.666 9.088-9.566 4.908-4.966 8.016-8.03 8.016-8.03l-4.572-5.15a519.866 519.866 0 0 0-8.91 9.083c-2.298 2.38-4.836 5.05-7.316 7.828L18 35.253 49.333 0 100 57.17l-27.964 31.54V50.133Z"/>
                            <path d="M72.036 99.646 21.054 42.158 0 65.91l49.333 55.674 22.703-25.626v3.687Z"/>
                        </svg>
                    </div>
                    <h1 class="text-lg font-mono font-bold tracking-widest uppercase">SYS.LENS</h1>
                </div>
                <div class="flex items-center gap-6 font-mono text-sm">
                    <a href="https://github.com/laravel-lens/laravel-lens" target="_blank" class="hover:underline hidden sm:block uppercase tracking-wider">REPOSITORY</a>
                    <span class="px-2 py-1 border border-black dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-900 uppercase">
                        LOCAL_ENV
                    </span>
                    <!-- Theme Toggle -->
                    <button @click="toggleTheme" class="px-2 py-1 border border-black dark:border-neutral-700 hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-none uppercase">
                        <span x-show="theme === 'dark'" x-cloak>[ SUN ]</span>
                        <span x-show="theme === 'light'" x-cloak>[ MOON ]</span>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto space-y-12">
                
                <!-- Hero Section & Controls -->
                <div class="relative mt-4">
                    <!-- Crosshairs -->
                    <span class="crosshair absolute -top-3 -left-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                    <span class="crosshair absolute -top-3 -right-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                    <span class="crosshair absolute -bottom-3 -left-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                    <span class="crosshair absolute -bottom-3 -right-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                    
                    <div class="bg-white dark:bg-black border border-black dark:border-neutral-700 p-8 sm:p-10 relative z-10">
                        <div class="max-w-2xl">
                            <h2 class="text-2xl font-mono font-bold uppercase tracking-widest border-b border-black dark:border-neutral-700 pb-4 mb-4">Target Designation</h2>
                            <p class="mt-2 text-base font-sans text-neutral-600 dark:text-neutral-400">
                                Enter target URL for WCAG compliance heuristics scan. System will output raw violation data.
                            </p>
                        </div>
                        
                        <form @submit.prevent="performScan" class="mt-8 flex flex-col sm:flex-row gap-0 border border-black dark:border-neutral-700 p-1 bg-neutral-50 dark:bg-neutral-900">
                            <div class="relative flex-grow">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="font-mono text-[#FF2D20] font-bold">></span>
                                </div>
                                <input 
                                    type="url" 
                                    x-model="url" 
                                    required
                                    class="block w-full rounded-none border-0 py-3 pl-8 pr-4 text-black dark:text-white dark:bg-black ring-1 ring-inset ring-black dark:ring-neutral-700 placeholder:text-neutral-500 focus:ring-2 focus:ring-inset focus:ring-[#FF2D20] dark:focus:ring-[#FF2D20] sm:text-sm sm:leading-6 font-mono bg-white outline-none" 
                                    placeholder="http://localhost"
                                >
                            </div>
                            <button 
                                type="submit" 
                                :disabled="isLoading"
                                class="inline-flex items-center justify-center rounded-none bg-[#FF2D20] text-white px-8 py-3 text-sm font-mono font-bold uppercase tracking-widest hover:bg-black hover:text-white transition-none disabled:opacity-50 disabled:cursor-not-allowed whitespace-nowrap border-l sm:border-t-0 border-t border-[#FF2D20] hover:border-black sm:ml-1 mt-1 sm:mt-0"
                            >
                                <span x-show="!isLoading">EXECUTE</span>
                                <span x-show="isLoading" class="flex items-center gap-2" x-cloak>
                                    PROCESSING...
                                </span>
                            </button>
                        </form>

                        <!-- Error Alert -->
                        <div x-show="error" x-cloak class="bg-white dark:bg-black p-4 border-2 border-[#FF2D20] text-[#FF2D20] mt-6 border-dashed">
                            <div class="flex">
                                <div class="flex-shrink-0 font-mono font-bold mr-3">
                                    [ERR]
                                </div>
                                <div>
                                    <h3 class="text-sm font-mono font-bold uppercase tracking-wider">Exception Caught</h3>
                                    <div class="mt-1 text-sm font-mono">
                                        <p x-text="error"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Results Area -->
                <div x-show="hasResults" x-cloak class="space-y-12">
                    
                    <!-- Summary Cards -->
                    <dl class="grid grid-cols-1 gap-8 sm:gap-6 sm:grid-cols-3 mt-8">
                        <!-- Total -->
                        <div class="relative">
                            <span class="crosshair absolute -top-3 -left-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            <span class="crosshair absolute -top-3 -right-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            <span class="crosshair absolute -bottom-3 -left-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            <span class="crosshair absolute -bottom-3 -right-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            
                            <div class="bg-white dark:bg-black border border-black dark:border-neutral-700 px-6 py-5 flex flex-col justify-between h-full relative z-10">
                                <dt class="truncate text-xs font-mono font-bold uppercase tracking-widest border-b border-black dark:border-neutral-700 pb-2 mb-2 text-neutral-600 dark:text-neutral-400">
                                    Total Output
                                </dt>
                                <dd class="mt-2 text-4xl font-mono font-bold tracking-tight" x-text="totalIssues"></dd>
                            </div>
                        </div>

                        <!-- Critical -->
                        <div class="relative">
                            <span class="crosshair absolute -top-3 -left-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            <span class="crosshair absolute -top-3 -right-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            <span class="crosshair absolute -bottom-3 -left-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            <span class="crosshair absolute -bottom-3 -right-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            
                            <div class="bg-[#FF2D20] text-white border border-[#FF2D20] px-6 py-5 flex flex-col justify-between h-full relative z-10">
                                <dt class="truncate text-xs font-mono font-bold uppercase tracking-widest border-b border-white pb-2 mb-2">
                                    Critical Failures
                                </dt>
                                <dd class="mt-2 text-4xl font-mono font-bold tracking-tight" x-text="criticalIssues"></dd>
                            </div>
                        </div>

                        <!-- Minor -->
                        <div class="relative">
                            <span class="crosshair absolute -top-3 -left-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            <span class="crosshair absolute -top-3 -right-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            <span class="crosshair absolute -bottom-3 -left-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            <span class="crosshair absolute -bottom-3 -right-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                            
                            <div class="bg-white dark:bg-black border border-black dark:border-neutral-700 px-6 py-5 flex flex-col justify-between h-full border-dashed relative z-10">
                                <dt class="truncate text-xs font-mono font-bold uppercase tracking-widest border-b border-black dark:border-neutral-700 border-dashed pb-2 mb-2 text-neutral-500 dark:text-neutral-400">
                                    Mod/Min Warnings
                                </dt>
                                <dd class="mt-2 text-4xl font-mono font-bold tracking-tight text-neutral-500 dark:text-neutral-400" x-text="otherIssues"></dd>
                            </div>
                        </div>
                    </dl>

                    <!-- Issue List -->
                    <div class="relative mt-8">
                        <span class="crosshair absolute -top-3 -left-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                        <span class="crosshair absolute -top-3 -right-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                        <span class="crosshair absolute -bottom-3 -left-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                        <span class="crosshair absolute -bottom-3 -right-1.5 text-[#FF2D20] font-mono text-lg leading-none">+</span>
                        
                        <div class="bg-white dark:bg-black border border-black dark:border-neutral-700 overflow-hidden relative z-10">
                            <div class="border-b border-black dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-900 px-6 py-4 flex items-center justify-between">
                                <h3 class="text-sm font-mono font-bold uppercase tracking-widest">Diagnostic Logs</h3>
                                <span class="text-xs font-mono">COUNT: <span class="text-[#FF2D20] font-bold" x-text="issues.length"></span></span>
                            </div>
                            
                            <template x-if="issues.length === 0">
                                <div class="text-center py-16 px-6 font-mono">
                                    <div class="text-2xl mb-2 font-bold">[ OK ]</div>
                                    <p class="text-sm text-neutral-500 uppercase tracking-widest">Zero violations detected. Status nominal.</p>
                                </div>
                            </template>

                            <ul role="list" class="divide-y divide-black dark:divide-neutral-700">
                                <template x-for="(issue, index) in issues" :key="index">
                                    <li class="p-6 sm:p-8">
                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                            <div class="flex-1 space-y-3">
                                                <div class="flex items-center gap-3">
                                                    <span 
                                                        class="inline-flex items-center px-2 py-0.5 text-xs font-mono font-bold uppercase tracking-wider"
                                                        :class="getBadgeColor(issue.impact)"
                                                        x-text="`[` + issue.impact + `]`"
                                                    ></span>
                                                    <span class="text-sm font-mono font-bold tracking-widest text-neutral-600 dark:text-neutral-400" x-text="issue.id"></span>
                                                </div>
                                                <h4 class="text-base font-sans font-medium" x-text="issue.description"></h4>
                                            </div>
                                            <a :href="issue.helpUrl" target="_blank" class="flex-shrink-0 inline-flex items-center gap-1.5 text-sm font-mono font-bold border-b border-black dark:border-white hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-none uppercase py-0.5 px-1">
                                                VIEW_DOCS ->
                                            </a>
                                        </div>

                                        <div class="mt-6">
                                            <p class="text-xs font-mono font-bold text-neutral-500 dark:text-neutral-400 mb-2 uppercase tracking-widest"><span class="text-[#FF2D20]">>>></span> FAILING_NODE</p>
                                            <div class="bg-neutral-100 dark:bg-neutral-900 border-l-4 border-black dark:border-neutral-500 p-4 overflow-x-auto">
                                                <pre><code class="text-sm font-mono whitespace-pre-wrap" x-text="issue.htmlSnippet"></code></pre>
                                            </div>
                                        </div>

                                        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4 border-t border-black dark:border-neutral-700 pt-6">
                                            <div>
                                                <p class="text-xs font-mono font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-2"><span class="text-[#FF2D20]">>>></span> SRC_LOC</p>
                                                <template x-if="issue.fileName">
                                                    <div class="flex items-center gap-2 text-sm font-mono bg-white dark:bg-black border border-black dark:border-neutral-700 px-3 py-2 w-max">
                                                        <span x-text="issue.fileName + ':' + issue.lineNumber"></span>
                                                    </div>
                                                </template>
                                                <template x-if="!issue.fileName">
                                                    <div class="flex items-center gap-2 text-sm font-mono text-[#FF2D20] border border-[#FF2D20] border-dashed px-3 py-2 w-max uppercase bg-[#FF2D20]/10">
                                                        [ PENDING_LOCATOR ]
                                                    </div>
                                                </template>
                                            </div>
                                            <div class="sm:text-right">
                                                <p class="text-xs font-mono font-bold text-neutral-500 dark:text-neutral-400 uppercase tracking-widest mb-2 block sm:inline-block"><span class="text-[#FF2D20] sm:hidden">>>></span> CSS_SELECTOR</p>
                                                <div class="text-sm font-mono bg-white dark:bg-black border border-black dark:border-neutral-700 px-3 py-2 overflow-x-auto break-all sm:ml-auto w-fit max-w-full">
                                                    <span x-text="issue.selector"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                </template>
                            </ul>
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
                theme: localStorage.getItem('theme') || 'light',
                
                init() {
                    document.documentElement.classList.toggle('dark', this.theme === 'dark');
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
                        case 'critical': return 'bg-[#FF2D20] text-white border border-[#FF2D20]';
                        case 'serious': 
                        case 'moderate': return 'bg-white text-black dark:bg-black dark:text-white border border-black dark:border-white';
                        case 'minor': return 'bg-white text-neutral-500 dark:bg-black dark:text-neutral-400 border border-dashed border-neutral-500 dark:border-neutral-600';
                        default: return 'bg-white text-black dark:bg-black dark:text-white border border-black dark:border-white';
                    }
                }
            }));
        });
    </script>
</body>
</html>