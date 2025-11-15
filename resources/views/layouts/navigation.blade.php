<nav x-data="{ open: false }" class="bg-white text-black border-b border-gray-100">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <img src="{{ asset('logo/catcha_logo_updated.png') }}" alt="{{ config('app.name') }} logo" class="h-9 w-auto">
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                    <x-nav-link :href="route('catches.index')" :active="request()->routeIs('catches.*') && ! request()->routeIs('catches.analytics')">
                        {{ __('Catches') }}
                    </x-nav-link>
                    @unless(auth()->check() && auth()->user()->role === 'expert')
                    <x-nav-link :href="route('catches.analytics')" :active="request()->routeIs('catches.analytics')">
                        {{ __('Analytics') }}
                    </x-nav-link>
                    @endunless
                     <x-nav-link :href="route('ai.seasonal-trends.view')" :active="request()->routeIs('ai.seasonal-trends.view')">
                        {{ __('Seasonal Trends') }}
                    </x-nav-link>
                    @unless(auth()->check() && auth()->user()->role === 'admin')
                        <x-nav-link :href="route('weather.map')" :active="request()->routeIs('weather.map')">
                            {{ __('Weather Map') }}
                        </x-nav-link>
                   
                    <x-nav-link :href="route('guidances.index')" :active="request()->routeIs('guidances.*')">
                        {{ __('Guides') }}
                    </x-nav-link>

                    {{-- @unless(auth()->check() && auth()->user()->role === 'fisher')
                        <x-nav-link :href="route('ai.chat')" :active="request()->routeIs('ai.chat')">
                            {{ __('AI Chat') }}
                        </x-nav-link>
                    @endunless --}}
                    @endunless
                    <x-nav-link :href="route('catches.heatmap')" :active="request()->routeIs('catches.heatmap')">
                        {{ __('Heatmap') }}
                    </x-nav-link>
                    @can('viewLiveTracksAdmin')
                        <x-nav-link :href="route('live-tracks.index')" :active="request()->routeIs('live-tracks.*')">
                            {{ __('Live Track') }}
                        </x-nav-link>
                    @endcan
                    @if(auth()->check() && auth()->user()->isAdmin())
                        <x-nav-link :href="route('admin.zones.index')" :active="request()->routeIs('admin.zones.*')">
                            {{ __('Zones') }}
                        </x-nav-link>
                    @endif
                    </div>
            </div>

            <!-- Settings Dropdown / Auth Links -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-black bg-white hover:text-black focus:outline-none transition ease-in-out duration-150">
                                <div class="flex items-center gap-2">
                                    <span>{{ Auth::user()->name }}</span>
                                    <span class="text-[10px] uppercase tracking-wide px-2 py-0.5 rounded bg-gray-200 text-black">{{ Auth::user()->role ?? 'fisher' }}</span>
                                </div>

                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf

                                <x-dropdown-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @endauth
                @guest
                    <div class="flex items-center gap-3">
                        <a href="{{ route('login') }}" class="text-sm text-black hover:text-black">{{ __('Log in') }}</a>
                        <a href="{{ route('register') }}" class="text-sm text-black hover:text-black font-medium">{{ __('Register') }}</a>
                    </div>
                @endguest
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-black hover:text-black hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-black transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('catches.index')" :active="request()->routeIs('catches.index')">
                {{ __('Catches') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('catches.analytics')" :active="request()->routeIs('catches.analytics')">
                {{ __('Analytics') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('guidances.index')" :active="request()->routeIs('guidances.*')">
                {{ __('Guidance') }}
            </x-responsive-nav-link>
            {{-- <x-responsive-nav-link :href="route('ai.chat')" :active="request()->routeIs('ai.chat')">
                {{ __('AI Chat') }}
            </x-responsive-nav-link> --}}
            
            <x-responsive-nav-link :href="route('ai.seasonal-trends.view')" :active="request()->routeIs('ai.seasonal-trends.view')">
                {{ __('Seasonal Trends') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('weather.map')" :active="request()->routeIs('weather.map')">
                {{ __('Weather Map') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('catches.heatmap')" :active="request()->routeIs('catches.heatmap')">
                {{ __('Heatmap') }}
            </x-responsive-nav-link>
            @can('viewLiveTracksAdmin')
                <x-responsive-nav-link :href="route('live-tracks.index')" :active="request()->routeIs('live-tracks.*')">
                    {{ __('Live Track') }}
                </x-responsive-nav-link>
            @endcan
            @if(auth()->check() && auth()->user()->isAdmin())
                <x-responsive-nav-link :href="route('admin.zones.index')" :active="request()->routeIs('admin.zones.*')">
                    {{ __('Zones') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        @auth
            <div class="pt-4 pb-1 border-t border-gray-200 text-black">
                <div class="px-4">
                    <div class="font-medium text-base text-black flex items-center gap-2">{{ Auth::user()->name }} <span class="text-[10px] uppercase tracking-wide px-1.5 py-0.5 rounded bg-gray-200 text-black">{{ Auth::user()->role ?? 'fisher' }}</span></div>
                    <div class="font-medium text-sm text-black">{{ Auth::user()->email }}</div>
                </div>

                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf

                        <x-responsive-nav-link :href="route('logout')"
                                onclick="event.preventDefault();
                                            this.closest('form').submit();">
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @endauth
        @guest
            <div class="pt-4 pb-4 border-t border-gray-200 text-black">
                <div class="px-4 space-y-2">
                    <a href="{{ route('login') }}" class="block text-sm text-black">{{ __('Log in') }}</a>
                    <a href="{{ route('register') }}" class="block text-sm text-black font-medium">{{ __('Register') }}</a>
                </div>
            </div>
        @endguest
    </div>
</nav>
