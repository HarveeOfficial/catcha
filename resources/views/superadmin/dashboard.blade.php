<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Superadmin Dashboard') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6 bg-gradient-to-br from-blue-50 via-white to-indigo-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
             <!-- Quick Actions -->
            <div class="rounded-lg bg-white shadow-md p-6">
                <h3 class="font-semibold text-lg text-gray-900 mb-4">Quick Actions</h3>
                <div class="flex gap-4 flex-wrap">
                    <a href="{{ route('superadmin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.5 1.5H9.5V9.5H1.5v1h8V18.5h1v-8h8v-1h-8V1.5z"></path>
                        </svg>
                        Create New User
                    </a>
                    <a href="{{ route('superadmin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition">
                        👤Manage Users
                    </a>
                </div>
            </div>
            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6">
                <!-- Total Users -->
                <div class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-md hover:shadow-xl transition-all duration-300 transform hover:scale-105 border-l-4 border-blue-500">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-blue-50 rounded-bl-full -mr-8 -mt-8 group-hover:bg-blue-100 transition-colors"></div>
                    <div class="relative z-10">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Users</h3>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-blue-600">{{ $totalUsers }}</div>
                            <div class="text-sm text-gray-500 mt-1">registered</div>
                        </div>
                    </div>
                </div>

                <!-- Admins -->
                <div class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-md hover:shadow-xl transition-all duration-300 transform hover:scale-105 border-l-4 border-purple-500">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-purple-50 rounded-bl-full -mr-8 -mt-8 group-hover:bg-purple-100 transition-colors"></div>
                    <div class="relative z-10">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Admins</h3>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-purple-600">{{ $admins }}</div>
                            <div class="text-sm text-gray-500 mt-1">administrators</div>
                        </div>
                    </div>
                </div>

                <!-- Fish Landing Site -->
                <div class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-md hover:shadow-xl transition-all duration-300 transform hover:scale-105 border-l-4 border-green-500">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-green-50 rounded-bl-full -mr-8 -mt-8 group-hover:bg-green-100 transition-colors"></div>
                    <div class="relative z-10">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Fish Landing Site</h3>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-green-600">{{ $experts }}</div>
                            <div class="text-sm text-gray-500 mt-1">experts</div>
                        </div>
                    </div>
                </div>

                <!-- MAO -->
                <div class="group relative overflow-hidden rounded-lg bg-white p-6 shadow-md hover:shadow-xl transition-all duration-300 transform hover:scale-105 border-l-4 border-orange-500">
                    <div class="absolute top-0 right-0 w-20 h-20 bg-orange-50 rounded-bl-full -mr-8 -mt-8 group-hover:bg-orange-100 transition-colors"></div>
                    <div class="relative z-10">
                        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">MAO</h3>
                        <div class="mt-4">
                            <div class="text-3xl font-bold text-orange-600">{{ $maos }}</div>
                            <div class="text-sm text-gray-500 mt-1">municipal officers</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Users Section -->
            <div class="rounded-lg bg-white shadow-md overflow-hidden">
                <div class="flex justify-between items-center p-6 border-b border-gray-200">
                    <h3 class="font-semibold text-lg text-gray-900">Recent Users</h3>
                    <a href="{{ route('superadmin.users.index') }}" class="text-blue-600 hover:text-blue-800 font-medium text-sm">
                        View All Users →
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse($recentUsers as $user)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->email }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            @if($user->role === 'admin') bg-purple-100 text-purple-800
                                            @elseif($user->role === 'expert') bg-green-100 text-green-800
                                            @elseif($user->role === 'mao') bg-orange-100 text-orange-800
                                            @elseif($user->role === 'superadmin') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif
                                        ">
                                            {{ ucfirst($user->role ?? 'user') }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ $user->created_at->format('M d, Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-center text-gray-500">No users found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
