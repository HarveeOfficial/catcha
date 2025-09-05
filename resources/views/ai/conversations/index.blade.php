<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">AI Conversations</h2>
    </x-slot>
    <div class="py-6 max-w-5xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow rounded p-4">
            <div class="flex justify-between mb-4 text-sm">
                <div class="text-gray-600">Saved chats ({{ $convs->total() }})</div>
                <a href="{{ route('ai.chat') }}" class="text-indigo-600 hover:underline">New Chat</a>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b">
                        <th class="py-2 pr-4">Title</th>
                        <th class="py-2 pr-4">Messages</th>
                        <th class="py-2 pr-4">Model</th>
                        <th class="py-2 pr-4">Updated</th>
                        <th class="py-2 pr-4"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($convs as $c)
                        <tr class="border-b last:border-0">
                            <td class="py-2 pr-4"><a class="text-indigo-600 hover:underline" href="{{ route('ai.conversations.show',$c) }}">{{ $c->title ?? 'Untitled' }}</a></td>
                            <td class="py-2 pr-4">{{ $c->messages_count }}</td>
                            <td class="py-2 pr-4">{{ $c->model ?? '-' }}</td>
                            <td class="py-2 pr-4">{{ $c->updated_at->diffForHumans() }}</td>
                            <td class="py-2 pr-4">
                                <form action="{{ route('ai.conversations.destroy',$c) }}" method="post" onsubmit="return confirm('Delete conversation?')">
                                    @csrf @method('DELETE')
                                    <button class="text-red-600 text-xs hover:underline">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-gray-500">No saved chats yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-4">{{ $convs->links() }}</div>
        </div>
    </div>
</x-app-layout>
