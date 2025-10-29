<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catch #{{ $catch->id }}</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8" x-data="aiSuggestShow()">
        <div class="bg-white shadow-sm rounded-md p-6 space-y-6">
            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-6">
                <div class="space-y-2 w-full md:w-60">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500">Caught At</div>
                        <div class="font-medium">{{ $catch->caught_at->format('Y-m-d H:i') }}</div>
                    </div>
                    <div>
                        <div class="text-xs uppercase tracking-wide text-gray-500">Species</div>
                        <div class="font-medium">{{ $catch->species?->common_name ?? '—' }}</div>
                    </div>
                    <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm pt-2">
                        <div><span class="text-gray-500">Qty (kg)</span>
                            <div class="font-medium">{{ $catch->quantity }}</div>
                        </div>
                        <div><span class="text-gray-500">Count</span>
                            <div class="font-medium">{{ $catch->count ?? '—' }}</div>
                        </div>
                        <div><span class="text-gray-500">Avg Size (cm)</span>
                            <div class="font-medium">{{ $catch->avg_size_cm ?? '—' }}</div>
                        </div>
                        <div><span class="text-gray-500">Gear</span>
                            <div class="font-medium">{{ $catch->gearType?->name ?? '—' }}</div>
                        </div>
                        <div><span class="text-gray-500">Vessel</span>
                            <div class="font-medium">{{ $catch->vessel_name ?? '—' }}</div>
                        </div>
                        <div><span class="text-gray-500">Location Text</span>
                            <div class="font-medium truncate" title="{{ $catch->location }}">
                                {{ $catch->location ?? '—' }}</div>
                        </div>
                    </div>
                    <div class="text-[11px] text-gray-400 pt-2">Recorded by: {{ $catch->user?->name ?? '—' }}</div>
                </div>
                <div class="flex-1">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-700">Map Location</h3>
                        <div class="text-[10px] text-gray-500">Toggle layers</div>
                    </div>
                    <div id="catchShowMap" class="mt-2 h-80 w-full rounded border border-gray-300 overflow-hidden">
                    </div>
                    @if (!$catch->latitude || !$catch->longitude)
                        <p class="mt-2 text-xs text-amber-600">No precise coordinates recorded for this catch.</p>
                    @else
                        <p class="mt-2 text-xs text-gray-500">Lat: {{ number_format($catch->latitude, 6) }} | Lon:
                            {{ number_format($catch->longitude, 6) }}</p>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-4 pt-2">
                <a href="{{ route('catches.index') }}" class="text-sm text-indigo-600 hover:underline">&larr; Back</a>
                @php($u = auth()->user())
                @php($hasFeedback = $catch->feedbacks()->exists())
                @if ($u && ($u->id === $catch->user_id && !$hasFeedback) && !($u->isAdmin() || $u->isExpert()))
                    <a href="{{ route('catches.edit', $catch) }}"
                        class="text-sm inline-flex items-center px-3 py-1.5 bg-indigo-600 text-white rounded hover:bg-indigo-700">
                        Edit
                    </a>
                @endif

                <button type="button" @click="open = !open; if(!ready && !loading){ fetchOrGenerate(false, false); }"
                    class="ml-auto inline-flex items-center px-3 py-1.5 text-xs rounded border border-indigo-600 text-indigo-700 bg-white hover:bg-indigo-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20"
                        fill="currentColor">
                        <path
                            d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l.563 1.737a1 1 0 00.95.69h1.826c.969 0 1.371 1.24.588 1.81l-1.477 1.073a1 1 0 00-.364 1.118l.563 1.737c.3.921-.755 1.688-1.538 1.118l-1.477-1.073a1 1 0 00-1.175 0l-1.477 1.073c-.783.57-1.838-.197-1.538-1.118l.563-1.737a1 1 0 00-.364-1.118L3.922 7.164c-.783-.57-.38-1.81.588-1.81h1.826a1 1 0 00.95-.69l.563-1.737z" />
                    </svg>
                    <span>AI suggestions</span>
                    <span x-show="ready && !loading" x-cloak class="ml-2 h-2.5 w-2.5 bg-green-500 rounded-full"></span>
                    <span x-show="loading" x-cloak class="ml-2 text-[10px] text-gray-500">loading…</span>
                </button>
            </div>
            <!-- Collapsible AI suggestions card (replaces modal) -->
            <div x-show="open" x-collapse x-cloak class="mt-3 border rounded-md bg-white shadow p-4 relative z-20">
                <div class="flex items-start justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-800">AI suggestions (read-only)</h3>
                        <p class="mt-1 text-[11px] text-slate-500">Automatically analyzed for this catch. Suggestions
                            are cached—re-run to refresh.</p>
                    </div>
                    <div class="text-[11px] text-slate-500" x-text="lastRunAt ? 'Last run: ' + lastRunAt : ''"></div>
                </div>
                <div class="mt-3">
                    <template x-if="error">
                        <div class="text-sm text-red-600" x-text="error"></div>
                    </template>
                    <template x-if="!error">
                        <div class="prose prose-sm max-w-none">
                            <pre x-show="loading" x-cloak class="text-xs text-slate-600">Fetching suggestions…</pre>
                            <div x-show="!loading" x-cloak x-text="text"
                                class="whitespace-pre-wrap text-sm text-slate-800"></div>
                        </div>
                    </template>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <button type="button" @click="fetchOrGenerate(false, true)"
                            class="inline-flex items-center px-3 py-1.5 text-xs rounded border border-slate-300 hover:bg-slate-50">Re-run
                            analysis</button>
                        <button type="button" @click="open = false"
                            class="inline-flex items-center px-3 py-1.5 text-xs rounded bg-gray-100 text-gray-800 hover:bg-gray-200">Hide</button>
                    </div>
                    <div class="text-[11px] text-slate-500" x-show="ready && !loading" x-cloak>Cached</div>
                </div>
            </div>
        </div>
        <div class="mt-6 bg-white shadow-sm rounded-md p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Feedback</h3>
            @if (session('status'))
                <div class="mb-4 text-sm text-green-600">{{ session('status') }}</div>
            @endif
            <div class="space-y-4">
                @forelse($feedbacks as $fb)
                    <div class="border rounded p-3 space-y-2">
                        <div class="flex justify-between text-xs text-gray-500">
                            <span>By {{ $fb->expert->name }}</span>
                            <span class="flex items-center gap-2">
                                <span>{{ $fb->created_at->diffForHumans() }}</span>
                                @php($canEditFeedback = auth()->user()->id === $fb->expert_id || auth()->user()->isAdmin())
                                @if ($canEditFeedback)
                                    <details class="inline-block">
                                        <summary class="cursor-pointer text-indigo-600 hover:underline">Edit</summary>
                                        <form method="POST" action="{{ route('catches.feedback.update', $fb) }}"
                                            class="mt-2 space-y-2">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex items-center space-x-2">
                                                <input id="approved_{{ $fb->id }}" name="approved"
                                                    type="checkbox" value="1" @checked($fb->approved)
                                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                                                <label for="approved_{{ $fb->id }}"
                                                    class="text-xs font-medium text-gray-700">Approved</label>
                                            </div>
                                            <textarea name="comments" rows="4" class="w-full border-gray-300 rounded-md shadow-sm text-sm">{{ old('comments', $fb->comments) }}</textarea>
                                            <div class="flex gap-2">
                                                <button
                                                    class="px-3 py-1.5 text-xs bg-indigo-600 hover:bg-indigo-700 text-white rounded"
                                                    type="submit">Save</button>
                                                <button type="button"
                                                    class="px-3 py-1.5 text-xs bg-gray-200 text-gray-800 rounded"
                                                    onclick="this.closest('details').open=false">Cancel</button>
                                            </div>
                                        </form>
                                    </details>
                                    <form method="POST" action="{{ route('catches.feedback.destroy', $fb) }}"
                                        onsubmit="return confirm('Delete this feedback?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline" type="submit">Delete</button>
                                    </form>
                                @endif
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            @if ($fb->approved)
                                <span
                                    class="inline-block px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded">Approved</span>
                            @endif
                        </div>
                        <p class="text-sm whitespace-pre-line">{{ $fb->comments }}</p>
                        <div class="flex flex-wrap items-center gap-4 text-xs">
                            <div class="flex items-center gap-1">
                                @php($liked = $fb->likes->where('user_id', auth()->id())->count() > 0)
                                @if ($liked)
                                    <form method="POST" action="{{ route('catches.feedback.unlike', $fb) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-indigo-600 hover:underline" type="submit">Unlike</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('catches.feedback.like', $fb) }}">
                                        @csrf
                                        <button class="text-indigo-600 hover:underline" type="submit">Like</button>
                                    </form>
                                @endif
                                <span class="text-gray-500">({{ $fb->likes->count() }})</span>
                            </div>
                            <button type="button" class="text-gray-600 hover:underline"
                                onclick="navigator.clipboard.writeText('{{ addslashes(Str::limit($fb->comments, 140)) }}');this.textContent='Copied';setTimeout(()=>this.textContent='Share',1500);">Share</button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No feedback yet.</p>
                @endforelse
            </div>
        </div>
        @if (auth()->user()->isExpert() || auth()->user()->isAdmin())
            <div class="mt-6 bg-white shadow-sm rounded-md p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4">Add Feedback</h3>
                <form method="post" action="{{ route('catches.feedback.store', $catch) }}" class="space-y-6">
                    @csrf
                    <div class="flex items-center space-x-2">
                        <input id="approved" name="approved" type="checkbox" value="1"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                        <label for="approved" class="text-sm font-medium text-gray-700">Approved</label>
                    </div>
                    <div>
                        <x-input-label for="comments" value="Comments" />
                        <textarea id="comments" name="comments" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"
                            rows="5"></textarea>
                        <x-input-error :messages="$errors->get('comments')" class="mt-2" />
                    </div>
                    <!-- AI Assistant (suggest feedback) -->
                    <div x-data="aiSuggest()" class="border rounded p-4 bg-gray-50">
                        <div class="flex items-center justify-between mb-2">
                            <h4 class="font-semibold text-xs text-gray-700">AI Assistant Suggestion</h4>
                            <span x-text="status" class="text-[11px] text-gray-500"></span>
                        </div>
                        <p class="text-[11px] text-gray-600 mb-3">Generate a draft sustainability / compliance review
                            based on this catch. Edit before submitting.</p>
                        <div class="mb-3 flex items-center gap-3">
                            <label for="ai-provider" class="text-[11px] font-medium text-gray-700">AI
                                Provider:</label>
                            <select id="ai-provider" x-model="provider"
                                class="text-xs px-2 py-1 border border-gray-300 rounded">
                                <option value="openai">OpenAI</option>
                                <option value="gemini">Gemini</option>
                            </select>
                        </div>
                        <div class="flex flex-wrap gap-2 mb-3">
                            <button type="button" @click="generate()" :disabled="loading"
                                class="px-3 py-1.5 text-xs rounded bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white">Generate</button>
                            <button type="button" @click="apply()" :disabled="!suggestion || loading"
                                class="px-3 py-1.5 text-xs rounded bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white">Insert</button>
                            <button type="button" @click="clear()" :disabled="!suggestion || loading"
                                class="px-3 py-1.5 text-xs rounded bg-gray-300 hover:bg-gray-400 disabled:opacity-50 text-gray-800">Clear</button>
                        </div>
                        <template x-if="loading">
                            <div class="text-xs text-gray-500 animate-pulse">Thinking...</div>
                        </template>
                        <template x-if="error">
                            <div class="text-xs text-red-600" x-text="error"></div>
                        </template>
                        <template x-if="suggestion">
                            <div class="text-xs whitespace-pre-wrap bg-white border rounded p-2 max-h-56 overflow-y-auto"
                                x-text="suggestion"></div>
                        </template>
                    </div>
                    <div>
                        <x-primary-button>Submit Feedback</x-primary-button>
                    </div>
                </form>
                <script>
                    function aiSuggest() {
                        return {
                            loading: false,
                            status: '',
                            suggestion: '',
                            error: '',
                            provider: 'openai',
                            async generate() {
                                this.error = '';
                                this.suggestion = '';
                                this.loading = true;
                                this.status = 'Generating';
                                const question =
                                    `Provide a concise professional sustainability & compliance review for the following fish catch. Strictly ground every point in the data provided. If something (like regulation or season) is unknown, say 'insufficient data' instead of guessing. Avoid legal claims unless explicitly present in the data. Include potential issues (size, season, gear, weather safety) and positive practices. Return plain text, no markdown lists. Do NOT assign a numeric rating.\n\nCatch Data:\nDate/Time: {{ $catch->caught_at->format('Y-m-d H:i') }}\nSpecies: {{ $catch->species?->common_name ?? 'N/A' }}\nQuantity (kg): {{ $catch->quantity }}\nCount: {{ $catch->count ?? 'N/A' }}\nLocation: {{ $catch->location ?? 'N/A' }}\nGear: {{ $catch->gearType?->name ?? 'N/A' }}@php($w = $catch->weather)@if ($w)\nWeather: Temp {{ $w['temperature_c'] ?? 'N/A' }}C, Wind {{ $w['wind_speed_kmh'] ?? 'N/A' }} km/h @if (isset($w['wind_dir_deg']))Dir {{ $w['wind_dir_deg'] }}°@endif, Humidity {{ $w['humidity_percent'] ?? 'N/A' }}%@endif`;
                                try {
                                    const resp = await fetch("{{ route('ai.consult') }}", {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
                                        },
                                        body: JSON.stringify({
                                            question,
                                            provider: this.provider
                                        })
                                    });
                                    const data = await resp.json();
                                    if (!resp.ok || data.error) {
                                        this.error = data.error || 'AI error';
                                    } else {
                                        this.suggestion = data.answer;
                                        this.status = 'Ready (' + (data.provider || 'unknown') + ')';
                                    }
                                } catch (e) {
                                    console.error(e);
                                    this.error = 'Network error';
                                } finally {
                                    this.loading = false;
                                    if (!this.error && !this.suggestion) {
                                        this.status = '';
                                    }
                                }
                            },
                            apply() {
                                if (!this.suggestion) return;
                                const ta = document.getElementById('comments');
                                if (ta.value.trim().length) {
                                    ta.value = ta.value + "\n\n" + this.suggestion;
                                } else {
                                    ta.value = this.suggestion;
                                }
                                ta.dispatchEvent(new Event('input'));
                            },
                            clear() {
                                this.suggestion = '';
                                this.error = '';
                                this.status = '';
                            }
                        }
                    }
                </script>
            </div>
        @endif

    </div>
    @once
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
            integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
            integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    @endonce
    <script>
        (function() {
            const lat = {{ $catch->latitude ?? 'null' }};
            const lon = {{ $catch->longitude ?? 'null' }};
            const hasCoords = lat !== null && lon !== null;
            const center = hasCoords ? [lat, lon] : [14.5995, 120.9842];
            const map = L.map('catchShowMap', {
                center: center,
                zoom: hasCoords ? 11 : 6
            });
            const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OSM contributors'
            }).addTo(map);
            const esri = L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                    attribution: 'Imagery &copy; Esri'
                });
            L.control.layers({
                'Street': osm,
                'Satellite': esri
            }).addTo(map);
            if (hasCoords) {
                L.marker([lat, lon]).addTo(map).bindPopup('Catch Location');
            }
        })();
    </script>
    <script>
        function aiSuggestShow() {
            return {
                open: false,
                loading: false,
                ready: false,
                error: null,
                text: '',
                lastRunAt: null,
                autoAnalyze() {
                    this.fetchOrGenerate(false);
                },
                buildQuestion() {
                    return [
                        'Provide concise, actionable suggestions for this single catch. Include sustainability/compliance, size limits, seasonality, gear fit, and weather safety.',
                        'Do not modify records. Only suggest. When appropriate, add a short line starting with "Don\'t touch that:" to explicitly advise leaving something unchanged.',
                        'Return 4-6 bullet points, each under 160 characters. No markdown headings or bold.',
                        '',
                        'Catch Data:',
                        'Date/Time: {{ $catch->caught_at->format('Y-m-d H:i') }}',
                        'Species: {{ $catch->species?->common_name ?? 'N/A' }}',
                        'Quantity (kg): {{ $catch->quantity }}',
                        'Count: {{ $catch->count ?? 'N/A' }}',
                        'Location: {{ $catch->location ? addslashes($catch->location) : 'N/A' }}',
                        'Gear: {{ $catch->gearType?->name ?? 'N/A' }}',
                        @php($w = $catch->weather)
                        @if ($w)
                            'Weather: Temp {{ $w['temperature_c'] ?? 'N/A' }}C, Wind {{ $w['wind_speed_kmh'] ?? 'N/A' }} km/h{{ isset($w['wind_dir_deg']) ? ", Dir {$w['wind_dir_deg']}°" : '' }}, Humidity {{ $w['humidity_percent'] ?? 'N/A' }}%',
                        @endif
                    ].join('\n');
                },
                async fetchOrGenerate(openAfter = true, force = false) {
                    try {
                        this.loading = true;
                        this.error = null;
                        const tok = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        if (!force) {
                            const probe = await fetch("{{ route('ai.suggestions.catches.show', $catch) }}", {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            const pj = await probe.json().catch(() => ({}));
                            if (probe.ok && pj.exists) {
                                this.text = (pj.content || '').replace(/^#+\s*/gm, '');
                                this.ready = true;
                                this.lastRunAt = new Date(pj.updated_at || Date.now()).toLocaleString();
                                if (openAfter) this.open = true;
                                return;
                            }
                        }
                        const gen = await fetch("{{ route('ai.suggestions.catches.generate', $catch) }}" + (force ?
                            '?force=1' : ''), {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                ...(tok ? {
                                    'X-CSRF-TOKEN': tok
                                } : {}),
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({})
                        });
                        const gj = await gen.json().catch(() => ({}));
                        if (!gen.ok || gj.error) {
                            this.error = gj.error || 'AI service is unavailable.';
                            this.ready = false;
                            return;
                        }
                        this.text = (gj.content || '').replace(/^#+\s*/gm, '');
                        this.ready = true;
                        this.lastRunAt = new Date(gj.updated_at || Date.now()).toLocaleString();
                        if (openAfter) {
                            this.open = true;
                        }
                    } catch (e) {
                        this.error = 'Could not reach AI service.';
                        this.ready = false;
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
    <script>
        // Optional: expose a simple re-run function used by the modal button
        document.addEventListener('alpine:init', () => {
            // no global registration needed; component-scoped method is used
        });
    </script>
</x-app-layout>
