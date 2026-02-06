<x-layout>
    {{-- SUCCESS ALERT --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 text-green-700 shadow-sm rounded-r-lg flex items-center animate-fade-in-down">
            <i class="fa-solid fa-circle-check mr-3 text-lg"></i>
            <div><span class="font-bold">Success!</span> {{ session('success') }}</div>
        </div>
    @endif

    {{-- HEADER --}}
    <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900">Account Settings</h2>
        <p class="text-gray-500 text-sm mt-1">Manage your personal profile and system security.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        {{-- LEFT COLUMN: PROFILE CARD --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden h-full">
                <div class="p-6 border-b border-gray-100 bg-gray-50">
                    <h3 class="font-bold text-gray-800 flex items-center">
                        <i class="fa-solid fa-user-circle mr-2 text-blue-600"></i> Personal Info
                    </h3>
                </div>
                
                <div class="p-6">
                    {{-- AVATAR & ROLE --}}
                    <div class="flex flex-col items-center mb-6">
                        <div class="w-24 h-24 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-3xl font-bold mb-3 shadow-inner">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </div>
                        <div class="text-center">
                            <h4 class="text-lg font-bold text-gray-900">{{ auth()->user()->name }}</h4>
                            @php
                                $roleColors = ['admin' => 'bg-purple-100 text-purple-700', 'officer' => 'bg-red-100 text-red-700', 'clerk' => 'bg-green-100 text-green-700'];
                                $roleNames  = ['admin' => 'Administrator', 'officer' => 'Fire Officer', 'clerk' => 'Records Clerk'];
                            @endphp
                            <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide {{ $roleColors[auth()->user()->role] ?? 'bg-gray-100' }}">
                                {{ $roleNames[auth()->user()->role] ?? auth()->user()->role }}
                            </span>
                            <p class="text-xs text-gray-400 mt-2">Joined {{ auth()->user()->created_at->format('M Y') }}</p>
                        </div>
                    </div>

                    <hr class="border-gray-100 mb-6">

                    {{-- PROFILE FORM --}}
                    <form action="{{ route('settings.update-profile') }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}" required 
                                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500 focus:border-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                {{-- Email is READ ONLY to prevent lockout --}}
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400">
                                        <i class="fa-solid fa-envelope"></i>
                                    </span>
                                    <input type="email" value="{{ auth()->user()->email }}" disabled 
                                        class="w-full pl-10 border border-gray-200 bg-gray-50 text-gray-500 rounded-lg px-3 py-2 text-sm cursor-not-allowed">
                                </div>
                                <p class="text-[10px] text-gray-400 mt-1">Contact Admin to change email.</p>
                            </div>

                            <button type="submit" class="w-full bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 font-medium py-2 px-4 rounded-lg text-sm transition shadow-sm mt-2">
                                Update Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: SECURITY CARD --}}
        <div class="lg:col-span-2">
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden h-full">
                
                {{-- HEADER --}}
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex items-center justify-between">
                    <h3 class="font-bold text-gray-800 flex items-center">
                        <i class="fa-solid fa-shield-halved mr-2 text-blue-600"></i> Password & Security
                    </h3>
                </div>

                {{-- FORM CONTENT --}}
                <div class="p-6 md:p-8">
                    <form action="{{ route('settings.update-password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- SECTION 1: VERIFICATION --}}
                        <div class="mb-8">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 border-b pb-2 border-gray-100">1. Verification</h4>
                            
                            <div class="bg-blue-50/50 p-5 rounded-lg border border-blue-100">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-blue-400">
                                        <i class="fa-solid fa-key"></i>
                                    </span>
                                    <input type="password" name="current_password" required 
                                        class="w-full pl-10 border @error('current_password') border-red-500 bg-red-50 @else border-gray-300 bg-white @enderror rounded-lg px-3 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400">
                                </div>
                                @error('current_password')
                                    <p class="text-red-600 text-xs mt-1.5 font-medium"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- SECTION 2: NEW PASSWORD --}}
                        <div class="mb-6">
                            <h4 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-4 border-b pb-2 border-gray-100">2. New Credentials</h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                    <input type="password" name="password" required 
                                        class="w-full border @error('password') border-red-500 bg-red-50 @else border-gray-300 @enderror rounded-lg px-3 py-2.5 text-sm focus:ring-blue-500 transition-colors"
                                        placeholder="Min. 8 characters">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                    <input type="password" name="password_confirmation" required 
                                        class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:ring-blue-500 transition-colors"
                                        placeholder="Re-enter new password">
                                </div>
                            </div>

                            <div class="mt-4">
                                @error('password') 
                                    <div class="p-3 bg-red-50 border border-red-100 rounded-lg flex items-start text-red-700">
                                        <i class="fa-solid fa-circle-xmark mt-0.5 mr-2"></i>
                                        <span class="text-sm font-medium">{{ $message }}</span>
                                    </div>
                                @else
                                    <div class="flex items-start p-3 text-gray-500 bg-gray-50 rounded-lg border border-gray-100">
                                        <i class="fa-solid fa-lock mt-0.5 mr-2 text-xs"></i>
                                        <p class="text-xs">Must include: 8+ chars, uppercase, lowercase, number, symbol.</p>
                                    </div>
                                @enderror
                            </div>
                        </div>

                        {{-- ACTION BUTTONS --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium shadow-md shadow-blue-100 transition-all flex items-center">
                                <i class="fa-solid fa-check mr-2"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>