<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Guidance Details</h2>
    </x-slot>
    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-md p-6 space-y-4">
            @if(session('status'))
                <div class="text-sm text-green-600">{{ session('status') }}</div>
            @endif
            <div class="flex justify-between items-start gap-4">
                <div>
                    <h1 class="text-2xl font-semibold mb-1">{{ $guidance->title }}</h1>
                    <p class="text-xs text-gray-500">Type: {{ $guidance->type }} | Status: {{ ucfirst($guidance->status) }} | Live: {{ $guidance->active ? 'Yes':'No' }}</p>
                    @if($guidance->status==='rejected' && $guidance->rejected_reason)
                        <p class="text-xs text-red-600">Rejected Reason: {{ $guidance->rejected_reason }}</p>
                    @endif
                    <p class="text-xs text-gray-500">Species: {{ optional($guidance->species)->common_name ?? 'All' }}</p>
                </div>
                @php($role = Auth::user()->role ?? null)
                <div class="flex gap-2 flex-wrap justify-end">
                    @if($role==='admin' || ($guidance->created_by===Auth::id() && $guidance->status!=='approved'))
                        <a href="{{ route('guidances.edit',$guidance) }}" class="px-3 py-1 text-xs bg-indigo-600 text-white rounded">Edit</a>
                    @endif
                    @if($role==='admin')
                        @if($guidance->status !== 'approved')
                            <form action="{{ route('guidances.approve',$guidance) }}" method="post" onsubmit="return confirm('Approve this guidance?');">
                                @csrf
                                <button class="px-3 py-1 text-xs bg-green-600 text-white rounded">Approve</button>
                            </form>
                        @endif
                        @if($guidance->status !== 'rejected')
                            <form action="{{ route('guidances.reject',$guidance) }}" method="post" onsubmit="return confirm('Reject this guidance?');">
                                @csrf
                                <input type="hidden" name="reason" value="Out of scope" />
                                <button class="px-3 py-1 text-xs bg-yellow-600 text-white rounded">Reject</button>
                            </form>
                        @endif
                    @endif
                    @if($role==='admin' || ($guidance->created_by===Auth::id() && $guidance->status!=='approved'))
                        <form action="{{ route('guidances.destroy',$guidance) }}" method="post" onsubmit="return confirm('Delete this guidance?');">
                            @csrf
                            @method('DELETE')
                            <button class="px-3 py-1 text-xs bg-red-600 text-white rounded">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
            <div class="prose max-w-none text-sm leading-relaxed whitespace-pre-wrap">{{ $guidance->content }}</div>
            <div class="text-xs text-gray-400">Effective: {{ $guidance->effective_from }} @if($guidance->effective_to)- {{ $guidance->effective_to }}@endif</div>
            <div>
                <a href="{{ route('guidances.index') }}" class="text-sm text-gray-600 hover:text-gray-800">&larr; Back</a>
            </div>
        </div>
    </div>
</x-app-layout>
