<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Catch Feedback</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
        <div class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-4">Catch Details</h3>
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="font-medium text-gray-600">Caught At</dt><dd>{{ $catch->caught_at->format('Y-m-d H:i') }}</dd>
                <dt class="font-medium text-gray-600">Species</dt><dd>{{ optional($catch->species)->common_name ?? '—' }}</dd>
                <dt class="font-medium text-gray-600">Quantity (kg)</dt><dd>{{ $catch->quantity }}</dd>
                <dt class="font-medium text-gray-600">Count</dt><dd>{{ $catch->count }}</dd>
                <dt class="font-medium text-gray-600">Location</dt><dd>{{ $catch->location }}</dd>
                <dt class="font-medium text-gray-600">Gear Type</dt><dd>{{ $catch->gear_type ?? '—' }}</dd>
                @php($w = $catch->weather)
                @if($w)
                    <dt class="font-medium text-gray-600">Weather Temp</dt><dd>{{ $w['temperature_c'] ?? '—' }} °C</dd>
                    <dt class="font-medium text-gray-600">Wind</dt><dd>{{ $w['wind_speed_kmh'] ?? '—' }} km/h @if(isset($w['wind_dir_deg'])) {{ $w['wind_dir_deg'] }}° @endif</dd>
                    <dt class="font-medium text-gray-600">Humidity</dt><dd>{{ $w['humidity_percent'] ?? '—' }}%</dd>
                    <dt class="font-medium text-gray-600">Recorded</dt><dd>{{ $w['time'] ?? '—' }}</dd>
                @endif
            </dl>
        </div>

    <div class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-4">Feedback</h3>
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
                                @if(auth()->id() === $fb->expert_id)
                                    <form method="POST" action="{{ route('catches.feedback.destroy',$fb) }}" onsubmit="return confirm('Delete this feedback?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 hover:underline" type="submit">Delete</button>
                                    </form>
                                @endif
                            </span>
                        </div>
                        <div class="flex flex-wrap gap-2 items-center">
                            @if($fb->approved)
                                <span class="inline-block px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded">Approved</span>
                            @endif
                        </div>
                        <p class="text-sm whitespace-pre-line">{{ $fb->comments }}</p>
                        <div class="flex flex-wrap items-center gap-4 text-xs">
                            <div class="flex items-center gap-1">
                                @php($liked = $fb->likes->where('user_id',auth()->id())->count()>0)
                                @if($liked)
                                    <form method="POST" action="{{ route('catches.feedback.unlike',$fb) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-indigo-600 hover:underline" type="submit">Unlike</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('catches.feedback.like',$fb) }}">
                                        @csrf
                                        <button class="text-indigo-600 hover:underline" type="submit">Like</button>
                                    </form>
                                @endif
                                <span class="text-gray-500">({{ $fb->likes->count() }})</span>
                            </div>
                            <button type="button" class="text-gray-600 hover:underline" onclick="navigator.clipboard.writeText('{{ addslashes(Str::limit($fb->comments,140)) }}');this.textContent='Copied';setTimeout(()=>this.textContent='Share',1500);">Share</button>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No feedback yet.</p>
                @endforelse
            </div>
        </div>

        @if(auth()->user()->isExpert() || auth()->user()->isAdmin())
        <div class="bg-white shadow rounded p-6">
            <h3 class="text-lg font-semibold mb-4">Add Feedback</h3>
            <form method="post" action="{{ route('catches.feedback.store', $catch) }}" class="space-y-4">
                @csrf
                <div class="flex items-center space-x-2">
                    <input id="approved" name="approved" type="checkbox" value="1" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" />
                    <label for="approved" class="text-sm font-medium text-gray-700">Approved</label>
                </div>
                <div>
                    <x-input-label for="comments" value="Comments" />
                    <textarea id="comments" name="comments" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" rows="4"></textarea>
                    <x-input-error :messages="$errors->get('comments')" class="mt-2" />
                </div>

                <!-- AI Assistant (suggest feedback) -->
                <div x-data="aiSuggest()" class="border rounded p-4 bg-gray-50 mt-6">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-sm text-gray-700">AI Assistant Suggestion</h4>
                        <span x-text="status" class="text-xs text-gray-500"></span>
                    </div>
                    <p class="text-xs text-gray-600 mb-3">Generate a draft sustainability / compliance review based on this catch's details. Edit before submitting.</p>
                    <div class="flex flex-wrap gap-2 mb-3">
                        <button type="button" @click="generate()" :disabled="loading" class="px-3 py-1.5 text-xs rounded bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white">Generate</button>
                        <button type="button" @click="apply()" :disabled="!suggestion || loading" class="px-3 py-1.5 text-xs rounded bg-green-600 hover:bg-green-700 disabled:opacity-50 text-white">Insert Into Comments</button>
                        <button type="button" @click="clear()" :disabled="!suggestion || loading" class="px-3 py-1.5 text-xs rounded bg-gray-300 hover:bg-gray-400 disabled:opacity-50 text-gray-800">Clear</button>
                    </div>
                    <template x-if="loading">
                        <div class="text-xs text-gray-500 animate-pulse">Thinking...</div>
                    </template>
                    <template x-if="error">
                        <div class="text-xs text-red-600" x-text="error"></div>
                    </template>
                    <template x-if="suggestion">
                        <div class="text-xs whitespace-pre-wrap bg-white border rounded p-2 max-h-56 overflow-y-auto" x-text="suggestion"></div>
                    </template>
                </div>
                <script>
                    function aiSuggest(){
                        return {
                            loading:false,
                            status:'',
                            suggestion:'',
                            error:'',
                            async generate(){
                                this.error=''; this.suggestion=''; this.loading=true; this.status='Generating';
                                const question = `Provide a concise professional sustainability & compliance review for the following fish catch. Include potential issues (size, season, gear, weather safety) and positive practices. Return plain text, no markdown lists. Do NOT assign a numeric rating.\n\nCatch Data:\nDate/Time: {{ $catch->caught_at->format('Y-m-d H:i') }}\nSpecies: {{ optional($catch->species)->common_name ?? 'N/A' }}\nQuantity (kg): {{ $catch->quantity }}\nCount: {{ $catch->count ?? 'N/A' }}\nLocation: {{ $catch->location }}\nGear: {{ $catch->gear_type ?? 'N/A' }}@php($w=$catch->weather)@if($w)\nWeather: Temp {{ $w['temperature_c'] ?? 'N/A' }}C, Wind {{ $w['wind_speed_kmh'] ?? 'N/A' }} km/h @if(isset($w['wind_dir_deg']))Dir {{ $w['wind_dir_deg'] }}°@endif, Humidity {{ $w['humidity_percent'] ?? 'N/A' }}%@endif`;
                                try {
                                    const resp = await fetch("{{ route('ai.consult') }}", {
                                        method:'POST',
                                        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},
                                        body: JSON.stringify({question})
                                    });
                                    const data = await resp.json();
                                    if(!resp.ok || data.error){
                                        this.error = data.error || 'AI error';
                                    } else {
                                        this.suggestion = data.answer;
                                        this.status='Ready';
                                    }
                                } catch(e){
                                    console.error(e); this.error='Network error';
                                } finally { this.loading=false; if(!this.error && !this.suggestion){ this.status=''; } }
                            },
                            apply(){
                                if(!this.suggestion) return;
                                const ta = document.getElementById('comments');
                                if(ta.value.trim().length){
                                    ta.value = ta.value + "\n\n" + this.suggestion;
                                } else { ta.value = this.suggestion; }
                                ta.dispatchEvent(new Event('input'));
                            },
                            clear(){ this.suggestion=''; this.error=''; this.status=''; }
                        }
                    }
                </script>
                <div>
                    <x-primary-button>Submit Feedback</x-primary-button>
                </div>
            </form>
        </div>
        @endif
    </div>
</x-app-layout>
