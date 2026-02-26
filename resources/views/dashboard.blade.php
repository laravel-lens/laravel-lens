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

    <div class="flex-1 flex flex-col selection:bg-[#FF2D20] selection:text-white dark:selection:bg-[#FF2D20] dark:selection:text-white relative">
        <!-- Header -->
        <header class="border-b border-black dark:border-neutral-700 bg-white dark:bg-black sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-14 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="flex items-center justify-center">
                        <svg class="w-8 h-8 text-[#FF2D20]" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path d="M23.642 5.43a.364.364 0 01.014.1v5.149c0 .135-.073.26-.189.326l-4.323 2.49v4.934a.378.378 0 01-.188.326L9.93 23.949a.316.316 0 01-.066.027c-.008.002-.016.008-.024.01a.348.348 0 01-.192 0c-.011-.002-.02-.008-.03-.012-.02-.008-.042-.014-.062-.025L.533 18.755a.376.376 0 01-.189-.326V2.974c0-.033.005-.066.014-.098.003-.012.01-.02.014-.032a.369.369 0 01.023-.058c.004-.013.015-.022.023-.033l.033-.045c.012-.01.025-.018.037-.027.014-.012.027-.024.041-.034H.53L5.043.05a.375.375 0 01.375 0L9.93 2.647h.002c.015.01.027.021.04.033l.038.027c.013.014.02.03.033.045.008.011.02.021.025.033.01.02.017.038.024.058.003.011.01.021.013.032.01.031.014.064.014.098v9.652l3.76-2.164V5.527c0-.033.004-.066.013-.098.003-.01.01-.02.013-.032a.487.487 0 01.024-.059c.007-.012.018-.02.025-.033.012-.015.021-.03.033-.043.012-.012.025-.02.037-.028.014-.01.026-.023.041-.032h.001l4.513-2.598a.375.375 0 01.375 0l4.513 2.598c.016.01.027.021.042.031.012.01.025.018.036.028.013.014.022.03.034.044.008.012.019.021.024.033.011.02.018.04.024.06.006.01.012.021.015.032zm-.74 5.032V6.179l-1.578.908-2.182 1.256v4.283zm-4.51 7.75v-4.287l-2.147 1.225-6.126 3.498v4.325zM1.093 3.624v14.588l8.273 4.761v-4.325l-4.322-2.445-.002-.003H5.04c-.014-.01-.025-.021-.04-.031-.011-.01-.024-.018-.035-.027l-.001-.002c-.013-.012-.021-.025-.031-.04-.01-.011-.021-.022-.028-.036h-.002c-.008-.014-.013-.031-.02-.047-.006-.016-.014-.027-.018-.043a.49.49 0 01-.008-.057c-.002-.014-.006-.027-.006-.041V5.789l-2.18-1.257zM5.23.81L1.47 2.974l3.76 2.164 3.758-2.164zm1.956 13.505l2.182-1.256V3.624l-1.58.91-2.182 1.255v9.435zm11.581-10.95l-3.76 2.163 3.76 2.163 3.759-2.164zm-.376 4.978L16.21 7.087 14.63 6.18v4.283l2.182 1.256 1.58.908zm-8.65 9.654l5.514-3.148 2.756-1.572-3.757-2.163-4.323 2.489-3.941 2.27z" fill="currentColor"/>
                        </svg>
                    </div>
                    <h1 class="text-lg font-mono font-bold tracking-widest uppercase">LARAVEL.LENS</h1>
                </div>
                <div class="flex items-center gap-6 font-mono text-sm">
                    <a href="https://github.com/laravel-lens/laravel-lens" target="_blank" class="hover:underline hidden sm:block uppercase tracking-wider">REPOSITORY</a>
                    <!-- Theme Toggle -->
                    <button @click="toggleTheme" class="p-1.5 border border-black dark:border-neutral-700 hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-none flex items-center justify-center">
                        <svg x-show="theme === 'dark'" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386l-1.591 1.591M21 12h-2.25m-.386 6.364l-1.591-1.591M12 18.75V21m-4.773-4.227l-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0z" />
                        </svg>
                        <svg x-show="theme === 'light'" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                        </svg>
                    </button>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto space-y-12">
                
                <!-- Hero Section & Controls -->
                <div class="relative mt-4">
                    <div class="bg-white dark:bg-black border border-black dark:border-neutral-700 p-8 sm:p-10 relative z-10">
                        <!-- Crosshairs -->
                        <span class="crosshair absolute top-0 left-0 -translate-x-1/2 -translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                        <span class="crosshair absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                        <span class="crosshair absolute bottom-0 left-0 -translate-x-1/2 translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                        <span class="crosshair absolute bottom-0 right-0 translate-x-1/2 translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                    
                        <div class="max-w-2xl relative z-10">
                            <h2 class="text-2xl font-mono font-bold uppercase tracking-widest border-b border-black dark:border-neutral-700 pb-4 mb-4">Target Designation</h2>
                            <p class="mt-2 text-base font-sans text-neutral-600 dark:text-neutral-400">
                                Enter target URL for WCAG compliance heuristics scan. System will output raw violation data.
                            </p>
                        </div>
                        
                        <form @submit.prevent="performScan" class="mt-8 flex flex-col sm:flex-row gap-0 border border-black dark:border-neutral-700 p-1 bg-neutral-50 dark:bg-neutral-900 relative z-10">
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
                        <div x-show="error" x-cloak class="bg-white dark:bg-black p-4 border-2 border-[#FF2D20] text-[#FF2D20] mt-6 border-dashed relative z-10">
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
                            <div class="bg-white dark:bg-black border border-black dark:border-neutral-700 px-6 py-5 flex flex-col justify-between h-full relative z-10">
                                <span class="crosshair absolute top-0 left-0 -translate-x-1/2 -translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                                <span class="crosshair absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                                <span class="crosshair absolute bottom-0 left-0 -translate-x-1/2 translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                                <span class="crosshair absolute bottom-0 right-0 translate-x-1/2 translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                                
                                <dt class="truncate text-xs font-mono font-bold uppercase tracking-widest border-b border-black dark:border-neutral-700 pb-2 mb-2 text-neutral-600 dark:text-neutral-400 relative z-10">
                                    Total Output
                                </dt>
                                <dd class="mt-2 text-4xl font-mono font-bold tracking-tight relative z-10" x-text="totalIssues"></dd>
                            </div>
                        </div>

                        <!-- Critical -->
                        <div class="relative">
                            <div class="bg-[#FF2D20] text-white border border-[#FF2D20] px-6 py-5 flex flex-col justify-between h-full relative z-10">
                                <span class="crosshair absolute top-0 left-0 -translate-x-1/2 -translate-y-1/2 bg-[#FF2D20] leading-none text-white font-mono text-lg z-20">+</span>
                                <span class="crosshair absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 bg-[#FF2D20] leading-none text-white font-mono text-lg z-20">+</span>
                                <span class="crosshair absolute bottom-0 left-0 -translate-x-1/2 translate-y-1/2 bg-[#FF2D20] leading-none text-white font-mono text-lg z-20">+</span>
                                <span class="crosshair absolute bottom-0 right-0 translate-x-1/2 translate-y-1/2 bg-[#FF2D20] leading-none text-white font-mono text-lg z-20">+</span>
                                
                                <dt class="truncate text-xs font-mono font-bold uppercase tracking-widest border-b border-white pb-2 mb-2 relative z-10">
                                    Critical Failures
                                </dt>
                                <dd class="mt-2 text-4xl font-mono font-bold tracking-tight relative z-10" x-text="criticalIssues"></dd>
                            </div>
                        </div>

                        <!-- Minor -->
                        <div class="relative">
                            <div class="bg-white dark:bg-black border border-black dark:border-neutral-700 px-6 py-5 flex flex-col justify-between h-full border-dashed relative z-10">
                                <span class="crosshair absolute top-0 left-0 -translate-x-1/2 -translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                                <span class="crosshair absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                                <span class="crosshair absolute bottom-0 left-0 -translate-x-1/2 translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                                <span class="crosshair absolute bottom-0 right-0 translate-x-1/2 translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                                
                                <dt class="truncate text-xs font-mono font-bold uppercase tracking-widest border-b border-black dark:border-neutral-700 border-dashed pb-2 mb-2 text-neutral-500 dark:text-neutral-400 relative z-10">
                                    Mod/Min Warnings
                                </dt>
                                <dd class="mt-2 text-4xl font-mono font-bold tracking-tight text-neutral-500 dark:text-neutral-400 relative z-10" x-text="otherIssues"></dd>
                            </div>
                        </div>
                    </dl>

                    <!-- Issue List -->
                    <div class="relative mt-8">
                        <div class="bg-white dark:bg-black border border-black dark:border-neutral-700 overflow-hidden relative z-10">
                            <span class="crosshair absolute top-0 left-0 -translate-x-1/2 -translate-y-1/2 bg-neutral-100 dark:bg-neutral-900 leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                            <span class="crosshair absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 bg-neutral-100 dark:bg-neutral-900 leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                            <span class="crosshair absolute bottom-0 left-0 -translate-x-1/2 translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                            <span class="crosshair absolute bottom-0 right-0 translate-x-1/2 translate-y-1/2 bg-white dark:bg-black leading-none text-neutral-400 font-mono text-lg z-20">+</span>
                            
                            <div class="border-b border-black dark:border-neutral-700 bg-neutral-100 dark:bg-neutral-900 px-6 py-4 flex items-center justify-between relative z-10">
                                <h3 class="text-sm font-mono font-bold uppercase tracking-widest">Diagnostic Logs</h3>
                                <span class="text-xs font-mono">COUNT: <span class="text-[#FF2D20] font-bold" x-text="issues.length"></span></span>
                            </div>
                            
                            <template x-if="issues.length === 0">
                                <div class="text-center py-16 px-6 font-mono relative z-10">
                                    <div class="text-2xl mb-2 font-bold">[ OK ]</div>
                                    <p class="text-sm text-neutral-500 uppercase tracking-widest">Zero violations detected. Status nominal.</p>
                                </div>
                            </template>

                            <ul role="list" class="divide-y divide-black dark:divide-neutral-700 relative z-10">
                                <template x-for="(issue, index) in issues" :key="index">
                                    <li class="p-6 sm:p-8">
                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                                            <div class="flex-1 space-y-3">
                                                <div class="flex flex-wrap items-center gap-3">
                                                    <span 
                                                        class="inline-flex items-center px-2 py-0.5 text-xs font-mono font-bold uppercase tracking-wider"
                                                        :class="getBadgeColor(issue.impact)"
                                                        x-text="`[` + issue.impact + `]`"
                                                    ></span>
                                                    <span class="text-sm font-mono font-bold tracking-widest text-neutral-600 dark:text-neutral-400" x-text="issue.id"></span>
                                                    <!-- AI Fix Button -->
                                                    <template x-if="issue.fileName">
                                                        <button 
                                                            @click="suggestFix(issue, index)"
                                                            :disabled="isFixing === index"
                                                            class="ml-auto sm:ml-0 px-3 py-1 border border-black dark:border-white text-xs font-mono font-bold uppercase tracking-widest hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-none disabled:opacity-50"
                                                        >
                                                            <span x-show="isFixing !== index">[ ⚡️ AI FIX (EXPERIMENTAL) ]</span>
                                                            <span x-show="isFixing === index">[ ⚙️ INITIALIZING NEURAL NET... ]</span>
                                                        </button>
                                                    </template>
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
        
        <!-- Fix Modal -->
        <div x-show="showFixModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" x-cloak>
            <div class="bg-white dark:bg-black border-2 border-black dark:border-white w-full max-w-3xl relative shadow-[8px_8px_0px_rgba(0,0,0,1)] dark:shadow-[8px_8px_0px_rgba(255,255,255,0.2)]">
                <!-- Modal Crosshairs -->
                <span class="crosshair absolute top-0 left-0 -translate-x-1/2 -translate-y-1/2 bg-white dark:bg-black leading-none text-black dark:text-white font-mono text-lg z-20">+</span>
                <span class="crosshair absolute top-0 right-0 translate-x-1/2 -translate-y-1/2 bg-white dark:bg-black leading-none text-black dark:text-white font-mono text-lg z-20">+</span>
                <span class="crosshair absolute bottom-0 left-0 -translate-x-1/2 translate-y-1/2 bg-white dark:bg-black leading-none text-black dark:text-white font-mono text-lg z-20">+</span>
                <span class="crosshair absolute bottom-0 right-0 translate-x-1/2 translate-y-1/2 bg-white dark:bg-black leading-none text-black dark:text-white font-mono text-lg z-20">+</span>

                <div class="border-b border-black dark:border-white px-6 py-4 flex items-center justify-between bg-neutral-100 dark:bg-neutral-900">
                    <h3 class="text-lg font-mono font-bold uppercase tracking-widest">[ AI_REMEDIATION_PROPOSAL ]</h3>
                    <button @click="showFixModal = false" class="text-black dark:text-white hover:text-[#FF2D20] font-mono font-bold text-xl leading-none">&times;</button>
                </div>
                
                <div class="p-6 space-y-6">
                    <div>
                        <p class="text-xs font-mono font-bold text-neutral-500 dark:text-neutral-400 mb-2 uppercase tracking-widest"><span class="text-red-500">[-]</span> ORIGINAL_CODE</p>
                        <div class="bg-red-50 dark:bg-red-950/30 border-l-4 border-red-500 p-4 font-mono text-sm overflow-x-auto border border-black dark:border-neutral-700">
                            <pre><code class="whitespace-pre-wrap text-black dark:text-neutral-200" x-text="currentFixSuggestion?.original_snippet"></code></pre>
                        </div>
                    </div>
                    
                    <div>
                        <p class="text-xs font-mono font-bold text-neutral-500 dark:text-neutral-400 mb-2 uppercase tracking-widest"><span class="text-green-500">[+]</span> PROPOSED_FIX</p>
                        <div class="bg-green-50 dark:bg-green-950/30 border-l-4 border-green-500 p-4 font-mono text-sm overflow-x-auto border border-black dark:border-neutral-700">
                            <pre><code class="whitespace-pre-wrap text-black dark:text-neutral-200" x-text="currentFixSuggestion?.fixed_snippet"></code></pre>
                        </div>
                    </div>
                </div>

                <div class="border-t border-black dark:border-white px-6 py-4 bg-neutral-50 dark:bg-neutral-900 flex justify-end gap-4">
                    <button @click="showFixModal = false" class="px-6 py-2 border border-black dark:border-white text-sm font-mono font-bold uppercase tracking-widest hover:bg-black hover:text-white dark:hover:bg-white dark:hover:text-black transition-none">
                        [ CANCEL ]
                    </button>
                    <button @click="applyFix" :disabled="isApplying" class="px-6 py-2 border border-black bg-black text-white dark:border-white dark:bg-white dark:text-black text-sm font-mono font-bold uppercase tracking-widest hover:bg-neutral-800 dark:hover:bg-neutral-200 transition-none disabled:opacity-50">
                        <span x-show="!isApplying">[ APPROVE & OVERWRITE FILE ]</span>
                        <span x-show="isApplying">[ WRITING... ]</span>
                    </button>
                </div>
            </div>
        </div>
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
                
                // AI Fix State
                isFixing: null,
                showFixModal: false,
                currentFixSuggestion: null,
                isApplying: false,
                
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
                
                async suggestFix(issue, index) {
                    this.isFixing = index;
                    this.error = null;
                    
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        const response = await fetch('{{ route('laravel-lens.fix.suggest') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({ 
                                file_path: issue.fileName,
                                line_number: issue.lineNumber,
                                issue_id: issue.id,
                                description: issue.description
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'An error occurred while suggesting fix.');
                        }

                        this.currentFixSuggestion = data.suggestion;
                        this.showFixModal = true;
                    } catch (err) {
                        alert(err.message);
                    } finally {
                        this.isFixing = null;
                    }
                },
                
                async applyFix() {
                    this.isApplying = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        
                        const response = await fetch('{{ route('laravel-lens.fix.apply') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({ 
                                file_path: this.currentFixSuggestion.file_path,
                                original_snippet: this.currentFixSuggestion.original_snippet,
                                fixed_snippet: this.currentFixSuggestion.fixed_snippet
                            })
                        });

                        const data = await response.json();

                        if (!response.ok) {
                            throw new Error(data.message || 'An error occurred while applying fix.');
                        }

                        alert('Fix applied successfully! You should rescan to verify.');
                        this.showFixModal = false;
                        this.currentFixSuggestion = null;
                    } catch (err) {
                        alert(err.message);
                    } finally {
                        this.isApplying = false;
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