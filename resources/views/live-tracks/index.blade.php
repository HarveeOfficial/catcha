<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Live Track') }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white shadow sm:rounded-lg p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900">Start a new live track</h3>
                        <p class="text-sm text-gray-600">Generate a public link and a secret write key for your mobile app.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('live-tracks.active') }}" target="_blank" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">Open Active Map</a>
                        <button id="createTrackBtn" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Create Track</button>
                    </div>
                </div>
                <div id="createResult" class="mt-4 hidden">
                    <div class="space-y-2">
                        <div class="text-sm"><span class="font-semibold">Public URL:</span> <a id="mapUrl" class="text-blue-600 underline" target="_blank" rel="noopener"></a></div>
                        <div class="text-sm"><span class="font-semibold">Ingest URL:</span> <code id="ingestUrl" class="bg-gray-100 px-1 py-0.5 rounded"></code></div>
                        <div class="text-sm"><span class="font-semibold">Poll URL:</span> <code id="pollUrl" class="bg-gray-100 px-1 py-0.5 rounded"></code></div>
                        <div class="text-sm"><span class="font-semibold">Write Key:</span> <code id="writeKey" class="bg-yellow-100 px-1 py-0.5 rounded"></code></div>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow sm:rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3">Your tracks</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                        <tr class="text-left text-gray-600">
                            <th class="px-2 py-1">Title</th>
                            <th class="px-2 py-1">User</th>
                            <th class="px-2 py-1">Started</th>
                            <th class="px-2 py-1">Status</th>
                            <th class="px-2 py-1">Link</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($tracks as $t)
                            <tr class="border-t">
                                <td class="px-2 py-2">{{ $t->title ?? '-' }}</td>
                                <td class="px-2 py-2">{{ $t->user?->name ?? '—' }}</td>
                                <td class="px-2 py-2">{{ optional($t->started_at)->diffForHumans() }}</td>
                                <td class="px-2 py-2">
                                    @if($t->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs rounded bg-green-100 text-green-800">Active</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs rounded bg-gray-100 text-gray-800">Ended</span>
                                    @endif
                                </td>
                                <td class="px-2 py-2"><a class="text-blue-600 underline" href="{{ route('live-tracks.show', $t->public_id) }}" target="_blank">Open</a></td>
                            </tr>
                        @empty
                            <tr><td class="px-2 py-4 text-gray-500" colspan="5">No tracks yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $tracks->links() }}</div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.getElementById('createTrackBtn')?.addEventListener('click', async () => {
                const res = await fetch(@json(route('live-tracks.create')), {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({})
                });
                if (!res.ok) { alert('Failed to create track'); return; }
                const data = await res.json();
                document.getElementById('mapUrl').textContent = data.mapUrl;
                document.getElementById('mapUrl').href = data.mapUrl;
                document.getElementById('ingestUrl').textContent = data.ingestUrl;
                document.getElementById('pollUrl').textContent = data.pollUrl;
                document.getElementById('writeKey').textContent = data.writeKey;
                document.getElementById('createResult').classList.remove('hidden');
            });
        </script>
    @endpush
</x-app-layout>
