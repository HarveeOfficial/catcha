<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Guidance Feed</h2>
    </x-slot>
    <div class="py-6 max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="flex justify-between items-center mb-4">
            <div class="text-sm text-gray-500">Total: {{ $guidances->total() }}</div>
            @php($role = Auth::user()->role ?? null)
            @if(in_array($role,['admin','expert','fisher']))
                <a href="{{ route('guidances.create') }}" class="inline-flex items-center px-3 py-2 bg-indigo-600 text-white text-xs font-semibold rounded shadow hover:bg-indigo-500">Add Guidance</a>
            @endif
        </div>

        @forelse($guidances as $g)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-5 overflow-hidden">
                <div class="p-4 space-y-3">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <a href="{{ route('guidances.show',$g) }}" class="block font-semibold text-base text-gray-800 hover:text-indigo-600">{{ $g->title }}</a>
                            <div class="mt-1 flex flex-wrap gap-2 text-[10px] uppercase tracking-wide text-gray-500">
                                <span class="px-1.5 py-0.5 bg-gray-100 rounded">{{ str_replace('_',' ', $g->type) }}</span>
                                <span class="px-1.5 py-0.5 rounded bg-gray-100">{{ ucfirst($g->status) }}</span>
                                <span class="px-1.5 py-0.5 bg-{{ $g->active ? 'green':'red' }}-100 text-{{ $g->active ? 'green':'red' }}-700 rounded">{{ $g->active ? 'Live':'Hidden' }}</span>
                                <span class="px-1.5 py-0.5 bg-gray-100 rounded">{{ optional($g->species)->common_name ?? 'All Species' }}</span>
                                @if($g->effective_from)
                                    <span class="px-1.5 py-0.5 bg-gray-100 rounded">Eff: {{ $g->effective_from }}@if($g->effective_to) → {{ $g->effective_to }}@endif</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-xs text-gray-400 whitespace-nowrap">{{ $g->created_at?->diffForHumans() }}</div>
                    </div>

                    <div class="text-sm text-gray-700 leading-relaxed">
                        {{ \Illuminate\Support\Str::limit(strip_tags($g->content), 300) }}
                    </div>

                    <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                        <div class="flex gap-4 text-xs text-gray-500">
                            <a href="{{ route('guidances.show',$g) }}" class="hover:text-indigo-600 font-medium">View</a>
                            @if($role==='admin' || ($g->created_by === Auth::id() && $g->status!=='approved'))
                                <a href="{{ route('guidances.edit',$g) }}" class="hover:text-indigo-600">Edit</a>
                            @endif
                        </div>
                        @if($role==='admin' || ($g->created_by === Auth::id() && $g->status!=='approved'))
                            <form action="{{ route('guidances.destroy',$g) }}" method="post" onsubmit="return confirm('Delete this guidance?')" class="m-0">
                                @csrf @method('DELETE')
                                <button class="text-xs text-red-600 hover:underline">Delete</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-md border border-dashed border-gray-300 p-8 text-center text-gray-500">No guidance entries yet.</div>
        @endforelse

        <div class="mt-6">{{ $guidances->links() }}</div>
    </div>
</x-app-layout>
