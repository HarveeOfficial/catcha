<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Chat: {{ $conversation->title ?? 'Conversation #'.$conversation->id }}</h2>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-4">
            <div class="flex justify-between mb-4 text-xs text-gray-500">
                <div>Model: {{ $conversation->model ?? 'n/a' }}</div>
                <a href="{{ route('ai.chat') }}" class="text-indigo-600 hover:underline">Open Live Chat</a>
            </div>
            <div class="space-y-4 text-sm">
                @foreach($messages as $m)
                    <div class="{{ $m->role==='user' ? 'text-right' : 'text-left' }} space-y-1">
                        <div class="inline-block px-3 py-2 rounded max-w-[80%] whitespace-pre-wrap {{ $m->role==='user' ? 'bg-indigo-600 text-white' : 'bg-gray-100' }}">
                            {!! nl2br(e($m->content)) !!}
                        </div>
                        @if($m->role==='assistant')
                            <form method="post" action="{{ route('ai.messages.to-guidance',$m) }}" class="inline-flex items-center gap-1 mt-1">
                                @csrf
                                <input type="text" name="title" placeholder="Draft title" class="border border-gray-300 rounded px-1 py-0.5 text-[10px] focus:border-indigo-500 focus:ring-indigo-500" value="{{ Str::limit(preg_replace('/\s+/',' ', trim($m->content)), 40) }}" />
                                <button class="text-[10px] text-indigo-600 hover:underline" title="Create inactive Guidance draft from this answer">Save Guidance</button>
                            </form>
                        @endif
                    </div>
                @endforeach
            </div>
            <div class="mt-6 text-right">
                <form action="{{ route('ai.conversations.destroy',$conversation) }}" method="post" onsubmit="return confirm('Delete this conversation?')" class="inline-block">
                    @csrf @method('DELETE')
                    <button class="text-red-600 text-xs hover:underline">Delete</button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
