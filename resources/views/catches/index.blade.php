<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Catches') }}</h2>
    </x-slot>

    <div class="py-8 max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-4 gap-3">
            <div>
                @if(auth()->user()->isExpert() || auth()->user()->isAdmin())
                <form method="get" class="flex items-center gap-2">
                    <input type="text" name="fisher" value="{{ request('fisher') }}" placeholder="Filter by fisher name" class="rounded-md border-gray-300 text-sm" />
                    <button class="px-3 py-1 bg-indigo-600 text-white text-xs rounded">Filter</button>
                    @if(request('fisher'))
                        <a href="{{ route('catches.index') }}" class="text-xs text-gray-500 underline">Reset</a>
                    @endif
                </form>
                @endif
            </div>
            <div class="flex justify-end">
                <a href="{{ route('catches.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white tracking-widest hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">{{ __('Record Catch') }}</a>
            </div>
        </div>
        <div class="bg-white overflow-hidden shadow-sm rounded-md">
            <div class="p-4">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b bg-gray-50">
                            <tr class="text-left text-gray-600">
                                <th class="py-2 pr-4">Date/Time</th>
                                @if(auth()->user()->isExpert() || auth()->user()->isAdmin())
                                    <th class="py-2 pr-4">Fisher</th>
                                @endif
                                <th class="py-2 pr-4">Species</th>
                                <th class="py-2 pr-4">Qty (kg)</th>
                                <th class="py-2 pr-4">Count</th>
                                <th class="py-2 pr-4">Location</th>
                                <th class="py-2 pr-4">Gear</th>
                                <!-- Weather column removed -->
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($catches as $c)
                            <tr class="border-b last:border-b-0">
                                <td class="py-2 pr-4 whitespace-nowrap">
                                    <a href="{{ route('catches.feedback.index', $c) }}" class="text-indigo-600 hover:underline">
                                        {{ $c->caught_at->format('Y-m-d H:i') }}
                                    </a>
                                </td>
                                @if(auth()->user()->isExpert() || auth()->user()->isAdmin())
                                    <td class="py-2 pr-4 text-xs text-gray-700">{{ optional($c->user)->name ?? '—' }}</td>
                                @endif
                                <td class="py-2 pr-4">{{ optional($c->species)->common_name ?? '—' }}</td>
                                <td class="py-2 pr-4">{{ $c->quantity }}</td>
                                <td class="py-2 pr-4">{{ $c->count }}</td>
                                <td class="py-2 pr-4">{{ $c->location ?: '—' }}</td>
                                <td class="py-2 pr-4">{{ $c->gear_type ?: '—' }}
                                    @if(auth()->user()->isExpert() || auth()->user()->isAdmin())
                                        <div class="text-[10px] text-gray-500">Feedback: {{ $c->feedbacks_count ?? $c->feedbacks()->count() }}</div>
                                    @endif
                                </td>
                                <!-- Weather cell removed -->
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-gray-500">{{ __('No catches recorded yet.') }}</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">{{ $catches->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
