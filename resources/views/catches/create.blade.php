<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Record Catch') }}</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white shadow-sm rounded-md p-6">
            <form action="{{ route('catches.store') }}" method="post" class="space-y-6">
                @csrf
                <div>
                    <x-input-label for="caught_at" value="Caught At" />
                    <x-text-input id="caught_at" type="datetime-local" name="caught_at" value="{{ old('caught_at') }}" class="mt-1 block w-full" required />
                    <x-input-error :messages="$errors->get('caught_at')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="species_id" value="Species" />
                    <select id="species_id" name="species_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">-- Unknown --</option>
                        @foreach($species as $s)
                            <option value="{{ $s->id }}" @selected(old('species_id')==$s->id)>{{ $s->common_name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('species_id')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="location" value="Location" />
                    <x-text-input id="location" type="text" name="location" value="{{ old('location') }}" placeholder="e.g. Zone A, GPS spot, etc." class="mt-1 block w-full" />
                    <x-input-error :messages="$errors->get('location')" class="mt-1" />
                </div>
                <div class="grid gap-6 md:grid-cols-3">
                    <div>
                        <x-input-label for="quantity" value="Quantity (kg)" />
                        <x-text-input id="quantity" type="number" step="0.01" name="quantity" value="{{ old('quantity') }}" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="count" value="Count" />
                        <x-text-input id="count" type="number" name="count" value="{{ old('count') }}" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="avg_size_cm" value="Avg Size (cm)" />
                        <x-text-input id="avg_size_cm" type="number" step="0.01" name="avg_size_cm" value="{{ old('avg_size_cm') }}" class="mt-1 block w-full" />
                    </div>
                </div>
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <x-input-label for="gear_type" value="Gear Type" />
                        <x-text-input id="gear_type" type="text" name="gear_type" value="{{ old('gear_type') }}" class="mt-1 block w-full" />
                    </div>
                    <div>
                        <x-input-label for="vessel_name" value="Vessel Name" />
                        <x-text-input id="vessel_name" type="text" name="vessel_name" value="{{ old('vessel_name') }}" class="mt-1 block w-full" />
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <x-primary-button>{{ __('Save') }}</x-primary-button>
                    <a href="{{ route('catches.index') }}" class="text-sm text-gray-600 hover:text-gray-800">{{ __('Cancel') }}</a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
