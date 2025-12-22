<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">
            Edit Boat Registration
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form method="POST" action="{{ route('mao.boats.update', $boat) }}" class="space-y-6">
                        @csrf
                        @method('PATCH')

                        <!-- Registration Info Section -->
                        <fieldset class="border-b pb-6">
                            <legend class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-700 text-sm font-bold">1</span>
                                Registration Information
                            </legend>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <x-input-label for="registration_number" value="Registration Number *" />
                                    <x-text-input id="registration_number" name="registration_number" type="text" class="mt-1 block w-full" 
                                        :value="old('registration_number', $boat->registration_number)" required />
                                    <x-input-error :messages="$errors->get('registration_number')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="name" value="Boat Name *" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" 
                                        :value="old('name', $boat->name)" required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="registration_date" value="Registration Date" />
                                    <x-text-input id="registration_date" name="registration_date" type="date" class="mt-1 block w-full" 
                                        :value="old('registration_date', $boat->registration_date?->format('Y-m-d'))" />
                                    <x-input-error :messages="$errors->get('registration_date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="expiry_date" value="Expiry Date" />
                                    <x-text-input id="expiry_date" name="expiry_date" type="date" class="mt-1 block w-full" 
                                        :value="old('expiry_date', $boat->expiry_date?->format('Y-m-d'))" />
                                    <x-input-error :messages="$errors->get('expiry_date')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="status" value="Status *" />
                                    <select id="status" name="status" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="active" @selected(old('status', $boat->status) === 'active')>Active</option>
                                        <option value="expired" @selected(old('status', $boat->status) === 'expired')>Expired</option>
                                        <option value="suspended" @selected(old('status', $boat->status) === 'suspended')>Suspended</option>
                                        <option value="decommissioned" @selected(old('status', $boat->status) === 'decommissioned')>Decommissioned</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('status')" class="mt-2" />
                                </div>
                            </div>
                        </fieldset>

                        <!-- Owner Info Section -->
                        <fieldset class="border-b pb-6">
                            <legend class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 text-green-700 text-sm font-bold">2</span>
                                Owner Information
                            </legend>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <x-input-label for="owner_name" value="Owner Name *" />
                                    <x-text-input id="owner_name" name="owner_name" type="text" class="mt-1 block w-full" 
                                        :value="old('owner_name', $boat->owner_name)" required />
                                    <x-input-error :messages="$errors->get('owner_name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="owner_contact" value="Owner Contact" />
                                    <x-text-input id="owner_contact" name="owner_contact" type="text" class="mt-1 block w-full" 
                                        :value="old('owner_contact', $boat->owner_contact)" placeholder="Phone or email" />
                                    <x-input-error :messages="$errors->get('owner_contact')" class="mt-2" />
                                </div>
                            </div>
                        </fieldset>

                        <!-- Boat Specifications Section -->
                        <fieldset class="border-b pb-6">
                            <legend class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-purple-100 text-purple-700 text-sm font-bold">3</span>
                                Boat Specifications
                            </legend>
                            <div class="grid gap-4 md:grid-cols-3">
                                <div>
                                    <x-input-label for="boat_type" value="Boat Type *" />
                                    <select id="boat_type" name="boat_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="motorized" @selected(old('boat_type', $boat->boat_type) === 'motorized')>Motorized</option>
                                        <option value="non-motorized" @selected(old('boat_type', $boat->boat_type) === 'non-motorized')>Non-Motorized</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('boat_type')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="length_m" value="Length (meters)" />
                                    <x-text-input id="length_m" name="length_m" type="number" step="0.01" class="mt-1 block w-full" 
                                        :value="old('length_m', $boat->length_m)" />
                                    <x-input-error :messages="$errors->get('length_m')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="width_m" value="Width (meters)" />
                                    <x-text-input id="width_m" name="width_m" type="number" step="0.01" class="mt-1 block w-full" 
                                        :value="old('width_m', $boat->width_m)" />
                                    <x-input-error :messages="$errors->get('width_m')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="gross_tonnage" value="Gross Tonnage" />
                                    <x-text-input id="gross_tonnage" name="gross_tonnage" type="number" step="0.01" class="mt-1 block w-full" 
                                        :value="old('gross_tonnage', $boat->gross_tonnage)" />
                                    <x-input-error :messages="$errors->get('gross_tonnage')" class="mt-2" />
                                </div>
                                <div id="engine_type_container">
                                    <x-input-label for="engine_type" value="Engine Type" />
                                    <select id="engine_type" name="engine_type" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- Select --</option>
                                        <option value="Diesel" @selected(old('engine_type', $boat->engine_type) === 'Diesel')>Diesel</option>
                                        <option value="Gasoline" @selected(old('engine_type', $boat->engine_type) === 'Gasoline')>Gasoline</option>
                                        <option value="Outboard" @selected(old('engine_type', $boat->engine_type) === 'Outboard')>Outboard</option>
                                        <option value="Inboard" @selected(old('engine_type', $boat->engine_type) === 'Inboard')>Inboard</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('engine_type')" class="mt-2" />
                                </div>
                                <div id="engine_hp_container">
                                    <x-input-label for="engine_horsepower" value="Engine HP" />
                                    <x-text-input id="engine_horsepower" name="engine_horsepower" type="number" class="mt-1 block w-full" 
                                        :value="old('engine_horsepower', $boat->engine_horsepower)" />
                                    <x-input-error :messages="$errors->get('engine_horsepower')" class="mt-2" />
                                </div>
                            </div>
                        </fieldset>

                        <!-- Location Section -->
                        <fieldset class="border-b pb-6">
                            <legend class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-orange-100 text-orange-700 text-sm font-bold">4</span>
                                Home Port & Location
                            </legend>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <x-input-label for="home_port" value="Home Port" />
                                    <x-text-input id="home_port" name="home_port" type="text" class="mt-1 block w-full" 
                                        :value="old('home_port', $boat->home_port)" />
                                    <x-input-error :messages="$errors->get('home_port')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="region" value="Region" />
                                    <select id="region" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">-- Select Region --</option>
                                        @foreach ($regions as $region)
                                            <option value="{{ $region['id'] }}" @selected(old('psgc_region', $boat->psgc_region) === $region['name'])>{{ $region['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" id="psgc_region" name="psgc_region" value="{{ old('psgc_region', $boat->psgc_region) }}">
                                    <x-input-error :messages="$errors->get('psgc_region')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="municipality" value="Municipality / City" />
                                    <select id="municipality" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                                        <option value="">-- Select Municipality / City --</option>
                                    </select>
                                    <input type="hidden" id="psgc_municipality" name="psgc_municipality" value="{{ old('psgc_municipality', $boat->psgc_municipality) }}">
                                    <x-input-error :messages="$errors->get('psgc_municipality')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="barangay" value="Barangay" />
                                    <select id="barangay" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                                        <option value="">-- Select Barangay --</option>
                                    </select>
                                    <input type="hidden" id="psgc_barangay" name="psgc_barangay" value="{{ old('psgc_barangay', $boat->psgc_barangay) }}">
                                    <x-input-error :messages="$errors->get('psgc_barangay')" class="mt-2" />
                                </div>
                            </div>
                        </fieldset>

                        <!-- Notes Section -->
                        <fieldset>
                            <legend class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                                <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-100 text-gray-700 text-sm font-bold">5</span>
                                Additional Notes
                            </legend>
                            <div>
                                <textarea id="notes" name="notes" rows="3" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Any additional information...">{{ old('notes', $boat->notes) }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </fieldset>

                        <!-- Form Actions -->
                        <div class="flex items-center justify-between pt-6 border-t">
                            <a href="{{ route('mao.boats.show', $boat) }}" class="text-gray-600 hover:text-gray-900">
                                ← Cancel
                            </a>
                            <x-primary-button>
                                Update Boat
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const boatTypeSelect = document.getElementById('boat_type');
            const engineTypeContainer = document.getElementById('engine_type_container');
            const engineHpContainer = document.getElementById('engine_hp_container');

            function toggleEngineFields() {
                const isMotorized = boatTypeSelect.value === 'motorized';
                engineTypeContainer.style.display = isMotorized ? 'block' : 'none';
                engineHpContainer.style.display = isMotorized ? 'block' : 'none';
            }

            boatTypeSelect.addEventListener('change', toggleEngineFields);
            toggleEngineFields();

            // PSGC Cascading Dropdowns
            const regionSelect = document.getElementById('region');
            const municipalitySelect = document.getElementById('municipality');
            const barangaySelect = document.getElementById('barangay');

            // Saved values for restoring
            const savedMunicipality = document.getElementById('psgc_municipality').value;
            const savedBarangay = document.getElementById('psgc_barangay').value;

            async function fetchOptions(url, params) {
                try {
                    const queryString = new URLSearchParams(params).toString();
                    const fullUrl = `${url}?${queryString}`;
                    const response = await fetch(fullUrl, {
                        headers: { 'Accept': 'application/json' }
                    });
                    if (!response.ok) return {};
                    return await response.json();
                } catch (error) {
                    console.error('Fetch error:', error);
                    return {};
                }
            }

            function populateSelect(selectElement, options, placeholder = '-- Select --', selectedValue = null) {
                selectElement.innerHTML = `<option value="">${placeholder}</option>`;
                if (!options || options.length === 0) {
                    selectElement.disabled = true;
                    return;
                }
                options.forEach(option => {
                    const optionElement = document.createElement('option');
                    optionElement.value = option.id;
                    optionElement.textContent = option.name;
                    if (selectedValue && option.name === selectedValue) {
                        optionElement.selected = true;
                    }
                    selectElement.appendChild(optionElement);
                });
                selectElement.disabled = false;
            }

            // Load municipalities if region is selected (for edit mode)
            async function loadInitialData() {
                if (regionSelect && regionSelect.value) {
                    const data = await fetchOptions('/api/locations/municipalities', { region_id: regionSelect.value });
                    populateSelect(municipalitySelect, data.places || [], '-- Select Municipality / City --', savedMunicipality);
                    
                    // If municipality was restored, load barangays
                    if (municipalitySelect.value && savedBarangay) {
                        const brgyData = await fetchOptions('/api/locations/barangays', { municipality_id: municipalitySelect.value });
                        populateSelect(barangaySelect, brgyData.barangays || [], '-- Select Barangay --', savedBarangay);
                    }
                }
            }

            // Region change
            if (regionSelect) {
                regionSelect.addEventListener('change', async function() {
                    municipalitySelect.innerHTML = '<option value="">Loading...</option>';
                    barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
                    municipalitySelect.disabled = true;
                    barangaySelect.disabled = true;

                    // Update hidden field
                    const text = this.selectedOptions[0]?.text || '';
                    document.getElementById('psgc_region').value = (text && !text.startsWith('--')) ? text : '';
                    document.getElementById('psgc_municipality').value = '';
                    document.getElementById('psgc_barangay').value = '';

                    if (!this.value) {
                        municipalitySelect.innerHTML = '<option value="">-- Select Municipality / City --</option>';
                        return;
                    }

                    const data = await fetchOptions('/api/locations/municipalities', { region_id: this.value });
                    populateSelect(municipalitySelect, data.places || [], '-- Select Municipality / City --');
                });
            }

            // Municipality change
            if (municipalitySelect) {
                municipalitySelect.addEventListener('change', async function() {
                    barangaySelect.innerHTML = '<option value="">Loading...</option>';
                    barangaySelect.disabled = true;

                    // Update hidden field
                    const text = this.selectedOptions[0]?.text || '';
                    document.getElementById('psgc_municipality').value = (text && !text.startsWith('--')) ? text : '';
                    document.getElementById('psgc_barangay').value = '';

                    if (!this.value) {
                        barangaySelect.innerHTML = '<option value="">-- Select Barangay --</option>';
                        return;
                    }

                    const data = await fetchOptions('/api/locations/barangays', { municipality_id: this.value });
                    populateSelect(barangaySelect, data.barangays || [], '-- Select Barangay --');
                });
            }

            // Barangay change
            if (barangaySelect) {
                barangaySelect.addEventListener('change', function() {
                    const text = this.selectedOptions[0]?.text || '';
                    document.getElementById('psgc_barangay').value = (text && !text.startsWith('--')) ? text : '';
                });
            }

            // Load initial data for edit mode
            loadInitialData();
        });
    </script>
</x-app-layout>
