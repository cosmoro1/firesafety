<aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between hidden md:flex">
    <div>
        <div class="h-20 flex items-center px-6 border-b border-gray-100">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6c/Bureau_of_Fire_Protection.png/1200px-Bureau_of_Fire_Protection.png" alt="Logo" class="h-10 w-10 mr-3">
            <div>
                <h1 class="font-bold text-sm text-gray-900 leading-tight">BFP Calamba</h1>
                <p class="text-xs text-gray-500">Fire Safety Platform</p>
            </div>
        </div>

        <nav class="p-4 space-y-1">
            
            @if(auth()->user()->role === 'admin')
            <a href="/dashboard" class="flex items-center px-4 py-3 rounded-lg transition {{ request()->is('dashboard') ? 'text-red-500 bg-red-50 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fa-solid fa-border-all w-6 {{ request()->is('dashboard') ? '' : 'text-gray-400' }}"></i>
                Dashboard
            </a>
            @endif

            @if(auth()->user()->role === 'admin')
            <a href="/analytics" class="flex items-center px-4 py-3 rounded-lg transition {{ request()->is('analytics') ? 'text-red-500 bg-red-50 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fa-solid fa-chart-column w-6 {{ request()->is('analytics') ? '' : 'text-gray-400' }}"></i>
                Analytics
            </a>
            @endif

            {{-- UPDATED: Added 'records_clerk' here --}}
            @if(in_array(auth()->user()->role, ['admin', 'officer', 'clerk']))
            <a href="/incidents" class="flex items-center px-4 py-3 rounded-lg transition {{ request()->is('incidents') ? 'text-red-500 bg-red-50 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fa-solid fa-fire w-6 {{ request()->is('incidents') ? '' : 'text-gray-400' }}"></i>
                Incident Reporting
            </a>
            @endif

            @if(in_array(auth()->user()->role, ['admin', 'officer']))
            <a href="/site-audit" class="flex items-center px-4 py-3 rounded-lg transition {{ request()->is('site-audit') ? 'text-red-500 bg-red-50 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fa-solid fa-clipboard-check w-6 {{ request()->is('site-audit') ? '' : 'text-gray-400' }}"></i>
                Site Audit
            </a>
            @endif

            @if(auth()->user()->role === 'admin')
            <a href="{{ route('high_risk.index') }}" 
               class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('high_risk.index') ? 'text-red-500 bg-red-50 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fa-solid fa-map-location-dot w-6 {{ request()->routeIs('high_risk.index') ? '' : 'text-gray-400' }}"></i>
                High Risk Barangays
            </a>
            @endif

            @if(in_array(auth()->user()->role, ['admin', 'clerk']))
            <a href="/documents" class="flex items-center px-4 py-3 rounded-lg transition {{ request()->is('documents') ? 'text-red-500 bg-red-50 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fa-regular fa-folder-open w-6 {{ request()->is('documents') ? '' : 'text-gray-400' }}"></i>
                Document Management
            </a>
            @endif

            @if(in_array(auth()->user()->role, ['admin', 'clerk']))
            <a href="/training" class="flex items-center px-4 py-3 rounded-lg transition {{ request()->is('training') ? 'text-red-500 bg-red-50 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                <i class="fa-solid fa-graduation-cap w-6 {{ request()->is('training') ? '' : 'text-gray-400' }}"></i>
                Training Management
            </a>
            @endif

            {{-- ADMIN CONTROLS SECTION --}}
            @if(auth()->user()->role === 'admin')
                <div class="mt-4 mb-2 px-4 text-xs font-bold text-gray-400 uppercase tracking-wider">
                    Admin Controls
                </div>

                <a href="{{ route('users.index') }}" 
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('users.*') ? 'text-red-500 bg-red-50 font-medium' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
                    <i class="fa-solid fa-users-gear w-6 {{ request()->routeIs('users.*') ? '' : 'text-gray-400' }}"></i>
                    User Management
                </a>
            @endif
            
        </nav>
    </div>

    <div class="p-4 border-t border-gray-100">
        <div class="flex items-center p-2 rounded-lg hover:bg-gray-50 cursor-pointer">
            <div class="h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-500 font-bold mr-3">
                <i class="fa-regular fa-user"></i>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-gray-900">{{ auth()->user()->name }}</h4>
                <p class="text-xs text-gray-500 capitalize">{{ auth()->user()->role }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-gray-400 hover:text-red-500 transition" title="Logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                </button>
            </form>
        </div>
    </div>
</aside>