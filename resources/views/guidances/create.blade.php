<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Add Guidance') }}</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-md p-6">
            <form action="{{ route('guidances.store') }}" method="post" class="space-y-6">
                @csrf
                <div>
                    <x-input-label for="title" value="Title" />
                    <x-text-input id="title" type="text" name="title" value="{{ old('title') }}" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('title')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="species_id" value="Species" />
                    <select id="species_id" name="species_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">All Species</option>
                        @foreach($species as $s)
                            <option value="{{ $s->id }}" @selected(old('species_id')==$s->id)>{{ $s->common_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('species_id')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="type" value="Type" />
                    <select id="type" name="type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                        @foreach(['regulation','best_practice','sustainability_tip','alert'] as $t)
                            <option value="{{ $t }}" @selected(old('type')==$t)>{{ ucfirst(str_replace('_',' ', $t)) }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('type')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="content" value="Content" />
                    <textarea id="content" name="content" rows="5" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('content') }}</textarea>
                    <x-input-error :messages="$errors->get('content')" class="mt-1" />
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="effective_from" value="Effective From" />
                        <x-text-input id="effective_from" type="date" name="effective_from" value="{{ old('effective_from') }}" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="effective_to" value="Effective To" />
                        <x-text-input id="effective_to" type="date" name="effective_to" value="{{ old('effective_to') }}" class="mt-1 block w-full" />
                    </div>
                </div>
                <p class="text-xs text-gray-500">Will be <strong>pending</strong> until an admin approves.</p>
                <div class="flex items-center gap-3">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                    <a href="{{ route('guidances.index') }}" class="text-sm text-gray-600 hover:text-gray-800">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
