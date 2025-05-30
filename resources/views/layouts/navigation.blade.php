<nav x-data="{ open: false }" class="w-64 min-h-screen text-white flex flex-col justify-between p-4 space-y-6"
    style="background: linear-gradient(to top, #c9b0a2, #ddccbf);">

    <style>
        .sidebar-links {
            display: flex;
            flex-direction: column;
            gap: 2rem;
            color: white;
            font-size: 3rem;
            letter-spacing: 0.05em;
            font-family: 'Cookie', cursive;
        }
    </style>
    <div>
        <div class="mb-6 text-center">
            <div class="font-medium text-base" style="font-family: 'Cookie', cursive; font-size:20px;">
                {{ Auth::user()->name }}</div>
            <div class="font-medium text-sm" style="font-family: 'Cookie', cursive; color: #b2967d;">
                {{ Auth::user()->email }}</div>
        </div>

        <div class="sidebar-links">
            <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" style="color:#5e503f; font-weight:bold;">
                {{ __('Info about project') }}
            </x-nav-link>
            <x-nav-link :href="route('tasks.index')" :active="request()->routeIs('tasks.*')" style="color:#5e503f;font-weight:bold;">
                {{ __('Dashboard') }}
            </x-nav-link>
            <x-nav-link :href="route('calendar.index')" :active="request()->routeIs('calendar.*')" style="color:#5e503f;font-weight:bold;">
                {{ __('Calendar') }}
            </x-nav-link>
            <x-nav-link :href="route('messages.index')" :active="request()->routeIs('messages.*')" style="color:#5e503f;font-weight:bold;">
                {{ __('Messages') }}
            </x-nav-link>
            <x-nav-link :href="route('zoom-meeting.index')" :active="request()->routeIs('zoom-meeting.*')" style="color:#5e503f;font-weight:bold;">
                {{ __('Zoom Meeting Testing') }}
            </x-nav-link>
        </div>
    </div>

    <div>
        <!-- Settings Dropdown -->
        <div class="sm:flex sm:items-center sm:ms-0 hidden">
            <div
                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-[#4a403a] hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                <div style="font-family: 'Cookie', cursive; font-size: 20px">Settings</div>
            </div>
        </div>

        <!-- Mobile Hamburger -->
        <div class="sm:hidden mt-4">
            <button @click="open = ! open"
                class="inline-flex items-center justify-center p-2 rounded-md text-white hover:text-gray-300 focus:outline-none transition duration-150 ease-in-out">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                    <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex" stroke-linecap="round"
                        stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                        stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Responsive Navigation Menu -->
        <div class="pt-4 pb-1 border-t">
            <div class="space-y-1">
            </div>
            <div s style="font-family: 'Cookie', cursive;">
                <x-responsive-nav-link :href="route('profile.edit')" style="color:#5e503f;border-radius:8px"
                    onmouseover="this.style.backgroundColor='#d9c1b4'" onmouseout="this.style.backgroundColor=''">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault();
                                        this.closest('form').submit();"
                        style="color:#5e503f;border-radius:8px" onmouseover="this.style.backgroundColor='#d9c1b4'"
                        onmouseout="this.style.backgroundColor=''">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
