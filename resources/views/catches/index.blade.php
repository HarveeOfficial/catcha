<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Catches') }}</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3">
            <div class="flex flex-col sm:flex-row gap-3 flex-wrap">
                @if (auth()->user()->isExpert() || auth()->user()->isAdmin() || auth()->user()->isMao())
                    <form method="get" class="flex items-center gap-2 flex-wrap">
                        <!-- Species Filter -->
                        <select name="species_id" class="rounded-md border-gray-300 text-sm">
                            <option value="">All Species</option>
                            @foreach ($species as $sp)
                                <option value="{{ $sp->id }}" @selected(request('species_id') == $sp->id)>
                                    {{ $sp->common_name }}
                                </option>
                            @endforeach
                        </select>
                        
                        <!-- Date Range Filter -->
                        <div class="flex items-center gap-2">
                            <input 
                                type="date" 
                                name="date_from" 
                                class="rounded-md border-gray-300 text-sm" 
                                placeholder="From"
                                value="{{ request('date_from') }}"
                            >
                            <span class="text-gray-500 text-sm">to</span>
                            <input 
                                type="date" 
                                name="date_to" 
                                class="rounded-md border-gray-300 text-sm" 
                                placeholder="To"
                                value="{{ request('date_to') }}"
                            >
                        </div>
                        
                        <button class="px-3 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700 transition">Filter</button>
                        @if (request('species_id') || request('date_from') || request('date_to'))
                            <a href="{{ route('catches.index') }}" class="text-xs text-gray-500 underline hover:text-gray-700">Reset</a>
                        @endif
                    </form>
                @endif
            </div>
            <div class="flex justify-end">
                @if (!auth()->user()->isMao() && !auth()->user()->isAdmin())
                    <a href="{{ route('catches.create') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">{{ __('Record Catch') }}</a>
                @endif
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm rounded-md">
            <div class="p-4">
                <!-- Desktop table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b bg-gray-50">
                            <tr class="text-left text-gray-600">
                                @if (auth()->user()->isExpert() || auth()->user()->isAdmin())
                                    <th class="py-2 pr-4">Fisher</th>
                                @endif
                                <th class="py-2 pr-4">Species</th>
                                <th class="py-2 pr-4">Qty (kg)</th>
                                <th class="py-2 pr-4">Count</th>
                                <th class="py-2 pr-4">Location</th>
                                <th class="py-2 pr-4">Gear</th>
                                <th class="py-2 pr-4">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($catches as $c)
                                <tr class="border-b last:border-b-0">
                                    @if (auth()->user()->isExpert() || auth()->user()->isAdmin())
                                        <td class="py-2 pr-4 text-xs text-gray-700">
                                            {{ optional($c->user)->name ?? '—' }}</td>
                                    @endif
                                    <td class="py-2 pr-4"><a href="{{ route('catches.show', $c) }}"
                                            class="text-indigo-600 hover:underline">
                                            {{ optional($c->species)->common_name ?? '—' }}</a>
                                    </td>
                                    <td class="py-2 pr-4">{{ $c->quantity }}</td>
                                    <td class="py-2 pr-4">{{ $c->count }}</td>
                                    <td class="py-2 pr-4">{{ $c->location ?: '—' }}</td>
                                    <td class="py-2 pr-4">{{ $c->gearType?->name ?: '—' }}</td>
                                    <td class="py-2 pr-4 whitespace-nowrap">
                                        {{ $c->caught_at->format('Y-m-d') }}
                                        @if (auth()->user()->isExpert() || auth()->user()->isAdmin())
                                            <div class="text-[10px] text-gray-500">Feedback:
                                                {{ $c->feedbacks_count ?? $c->feedbacks()->count() }}</div>
                                        @endif
                                    </td>
                                    <!-- Weather cell removed -->
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-500">
                                        {{ __('No catches recorded yet.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- Mobile cards -->
                <div class="md:hidden divide-y divide-gray-200">
                    @forelse($catches as $c)
                        <a href="{{ route('catches.show', $c) }}" class="block py-3 group">
                            <div class="flex items-center justify-between">
                                <div class="text-sm font-medium text-slate-800 group-hover:text-indigo-600">
                                    {{ $c->caught_at->format('Y-m-d') }}</div>
                                <div class="text-[10px] uppercase tracking-wide text-slate-500">
                                    {{ optional($c->species)->common_name ?? '—' }}</div>
                            </div>
                            <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                                <span><span class="font-medium">Qty:</span> {{ $c->quantity }}</span>
                                <span><span class="font-medium">Count:</span> {{ $c->count }}</span>
                                <span><span class="font-medium">Loc:</span> {{ $c->location ?: '—' }}</span>
                                <span><span class="font-medium">Gear:</span> {{ $c->gearType?->name ?: '—' }}</span>
                                @if (auth()->user()->isExpert() || auth()->user()->isAdmin())
                                    <span><span class="font-medium">Feedback:</span>
                                        {{ $c->feedbacks_count ?? $c->feedbacks()->count() }}</span>
                                @endif
                                <span><a href="{{ route('catches.feedback.index', $c) }}"
                                        class="underline text-indigo-600">Feedback</a></span>
                            </div>
                            @if (auth()->user()->isExpert() || auth()->user()->isAdmin())
                                <div class="mt-1 text-[11px] text-slate-500">Fisher:
                                    {{ optional($c->user)->name ?? '—' }}</div>
                            @endif
                        </a>
                    @empty
                        <p class="py-6 text-center text-sm text-gray-500">{{ __('No catches recorded yet.') }}</p>
                    @endforelse
                </div>
                <div class="mt-6 flex items-center justify-between px-4">
                    <div class="text-sm text-gray-600">
                        Showing {{ $catches->firstItem() ?? 0 }} to {{ $catches->lastItem() ?? 0 }} of {{ $catches->total() }} results
                    </div>
                    <div id="paginationContainer">
                        {{ $catches->links('pagination::tailwind-custom') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
    // AJAX pagination
    document.addEventListener('DOMContentLoaded', function() {
        const paginationContainer = document.getElementById('paginationContainer');
        if (paginationContainer) {
            paginationContainer.addEventListener('click', function(e) {
                if (e.target.tagName === 'A' && e.target.href) {
                    e.preventDefault();
                    const url = new URL(e.target.href);
                    loadPage(url);
                }
            });
        }
    });

    function loadPage(url) {
        const params = new URLSearchParams(url.search);
        const queryString = url.search ? url.search : '';
        
        fetch(window.location.pathname + queryString, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(response => response.text())
        .then(html => {
            // Parse the response to extract table/cards and pagination
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Update desktop table
            const newTable = doc.querySelector('table');
            const oldTable = document.querySelector('table');
            if (newTable && oldTable) {
                oldTable.querySelector('tbody').innerHTML = newTable.querySelector('tbody').innerHTML;
            }
            
            // Update mobile cards
            const newCards = doc.querySelector('.md\\:hidden.divide-y');
            const oldCards = document.querySelector('.md\\:hidden.divide-y');
            if (newCards && oldCards) {
                oldCards.innerHTML = newCards.innerHTML;
            }
            
            // Update pagination
            const newPagination = doc.getElementById('paginationContainer');
            const oldPagination = document.getElementById('paginationContainer');
            if (newPagination && oldPagination) {
                oldPagination.innerHTML = newPagination.innerHTML;
            }
            
            // Scroll to top of table
            document.querySelector('.bg-white.overflow-hidden').scrollIntoView({ behavior: 'smooth' });
            
            // Re-attach pagination listeners
            attachPaginationListeners();
        })
        .catch(err => console.error('Error loading page:', err));
    }

    function attachPaginationListeners() {
        const paginationContainer = document.getElementById('paginationContainer');
        if (paginationContainer) {
            const links = paginationContainer.querySelectorAll('a');
            links.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (this.href) {
                        loadPage(new URL(this.href));
                    }
                });
            });
        }
    }
</script>

<style>
    /* Custom pagination styles */
    .pagination {
        @apply flex gap-1 flex-wrap;
    }
    
    .pagination a,
    .pagination span {
        @apply px-3 py-2 text-sm font-medium transition-colors;
    }
    
    .pagination a {
        @apply text-gray-700 hover:text-gray-900 hover:bg-gray-100 rounded cursor-pointer;
    }
    
    .pagination span {
        @apply text-gray-500;
    }
    
    .pagination .active span {
        @apply text-white bg-indigo-600 rounded;
    }
    
    .pagination .disabled span {
        @apply text-gray-400 cursor-not-allowed;
    }
</style>
</x-app-layout>
