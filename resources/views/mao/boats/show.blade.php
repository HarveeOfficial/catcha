<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Boat Details
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Header with Status -->
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900">{{ $boat->name }}</h3>
                            <p class="text-gray-500">{{ $boat->registration_number }}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            @php
                                $statusColors = [
                                    'active' => 'bg-green-100 text-green-800',
                                    'expired' => 'bg-red-100 text-red-800',
                                    'suspended' => 'bg-yellow-100 text-yellow-800',
                                    'decommissioned' => 'bg-gray-100 text-gray-800',
                                ];
                            @endphp
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$boat->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst($boat->status) }}
                            </span>
                            @if ($boat->isExpired())
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    ⚠️ Expired
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Owner Information -->
                    <div class="border-t pt-6 mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Owner Information</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Owner Name</span>
                                <p class="font-medium">{{ $boat->owner_name }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Contact</span>
                                <p class="font-medium">{{ $boat->owner_contact ?: '—' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Details -->
                    <div class="border-t pt-6 mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Registration Details</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Registration Date</span>
                                <p class="font-medium">{{ $boat->registration_date?->format('M d, Y') ?: '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Expiry Date</span>
                                <p class="font-medium {{ $boat->isExpired() ? 'text-red-600' : '' }}">
                                    {{ $boat->expiry_date?->format('M d, Y') ?: '—' }}
                                </p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Status</span>
                                <p class="font-medium">{{ ucfirst($boat->status) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Boat Specifications -->
                    <div class="border-t pt-6 mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Boat Specifications</h4>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Boat Type</span>
                                <p class="font-medium">
                                    @if ($boat->boat_type === 'motorized')
                                        ⚡ Motorized
                                    @else
                                        🚣 Non-Motorized
                                    @endif
                                </p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Length</span>
                                <p class="font-medium">{{ $boat->length_m ? $boat->length_m . ' m' : '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Width</span>
                                <p class="font-medium">{{ $boat->width_m ? $boat->width_m . ' m' : '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Gross Tonnage</span>
                                <p class="font-medium">{{ $boat->gross_tonnage ?: '—' }}</p>
                            </div>
                            @if ($boat->boat_type === 'motorized')
                                <div>
                                    <span class="text-sm text-gray-500">Engine Type</span>
                                    <p class="font-medium">{{ $boat->engine_type ?: '—' }}</p>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-500">Engine HP</span>
                                    <p class="font-medium">{{ $boat->engine_horsepower ? $boat->engine_horsepower . ' HP' : '—' }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Home Port & Location -->
                    <div class="border-t pt-6 mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Home Port & Location</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <span class="text-sm text-gray-500">Home Port</span>
                                <p class="font-medium">{{ $boat->home_port ?: '—' }}</p>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500">Location</span>
                                <p class="font-medium">
                                    @php
                                        $location = collect([$boat->psgc_barangay, $boat->psgc_municipality, $boat->psgc_region])
                                            ->filter()
                                            ->implode(', ');
                                    @endphp
                                    {{ $location ?: '—' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    @if ($boat->notes)
                        <div class="border-t pt-6 mb-6">
                            <h4 class="text-lg font-semibold text-gray-900 mb-4">Notes</h4>
                            <p class="text-gray-700">{{ $boat->notes }}</p>
                        </div>
                    @endif

                    <!-- Metadata -->
                    <div class="border-t pt-6 mb-6">
                        <div class="flex gap-6 text-sm text-gray-500">
                            <div>Created: {{ $boat->created_at->format('M d, Y H:i') }}</div>
                            <div>Last Updated: {{ $boat->updated_at->format('M d, Y H:i') }}</div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between pt-6 border-t">
                        <a href="{{ route('mao.boats.index') }}" class="text-gray-600 hover:text-gray-900">
                            ← Back to List
                        </a>
                        <div class="flex gap-3">
                            <a href="{{ route('mao.boats.edit', $boat) }}" 
                                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                                Edit
                            </a>
                            <form method="POST" action="{{ route('mao.boats.destroy', $boat) }}" 
                                onsubmit="return confirm('Are you sure you want to delete this boat registration?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
