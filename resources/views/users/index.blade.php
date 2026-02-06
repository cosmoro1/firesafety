<x-layout>
    {{-- SUCCESS ALERT --}}
    @if(session('success'))
        <div class="mb-4 p-4 bg-green-100 border-l-4 border-green-500 text-green-700 shadow-sm rounded-r-lg flex items-center">
            <i class="fa-solid fa-circle-check mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    {{-- HEADER & ADD BUTTON --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">User Management</h2>
            <p class="text-gray-500 text-sm">Manage access for Fire Officers and Records Clerks.</p>
        </div>
        
        <button onclick="openUserModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-medium shadow-sm transition flex items-center">
            <i class="fa-solid fa-user-plus mr-2"></i> Add New User
        </button>
    </div>

    {{-- USERS TABLE --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm mb-6 overflow-hidden">
        <table class="w-full text-left border-collapse">
            <thead class="bg-gray-50 border-b border-gray-100 text-xs uppercase text-gray-500 font-semibold">
                <tr>
                    <th class="px-6 py-4">Name</th>
                    <th class="px-6 py-4">Email</th>
                    <th class="px-6 py-4">Role</th>
                    <th class="px-6 py-4">Joined</th>
                    <th class="px-6 py-4 text-center">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-sm">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 font-medium text-gray-900">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
                                {{ substr($user->name, 0, 1) }}
                            </div>
                            {{ $user->name }}
                        </div>
                    </td>
                    <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                    <td class="px-6 py-4">
                        @php
                            $roleColors = ['admin' => 'bg-purple-100 text-purple-700', 'officer' => 'bg-red-100 text-red-700', 'clerk' => 'bg-green-100 text-green-700'];
                            $roleLabel = ['admin' => 'Admin', 'officer' => 'Fire Officer', 'clerk' => 'Records Clerk'];
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $roleLabel[$user->role] ?? ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center justify-center gap-4">
                            {{-- RESET PASSWORD BUTTON --}}
                            <button onclick="openResetModal('{{ $user->id }}', '{{ $user->name }}')" class="text-blue-500 hover:text-blue-700 transition" title="Reset Password">
                                <i class="fa-solid fa-key"></i>
                            </button>

                            @if(auth()->id() !== $user->id)
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700 transition" title="Delete User">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                            @else
                                <span class="text-xs text-gray-400 italic">You</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>

    {{-- MODAL 1: CREATE NEW USER --}}
    {{-- LOGIC: Only show if there are errors AND the form submitted was 'create' --}}
    <div id="userModal" class="fixed inset-0 z-50 {{ $errors->any() && old('form_type') == 'create' ? '' : 'hidden' }}" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeUserModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-lg">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        {{-- IDENTIFIER TAG --}}
                        <input type="hidden" name="form_type" value="create">

                        <div class="bg-white px-6 py-6">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="text-lg font-bold text-gray-900">Create New Account</h3>
                                <button type="button" onclick="closeUserModal()" class="text-gray-400 hover:text-gray-500"><i class="fa-solid fa-xmark"></i></button>
                            </div>
                            
                            <div class="space-y-4">
                                {{-- Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full border @error('name') border-red-500 @else border-gray-300 @enderror rounded-lg px-3 py-2 text-sm focus:ring-blue-500">
                                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                    <input type="email" name="email" value="{{ old('email') }}" required class="w-full border @error('email') border-red-500 @else border-gray-300 @enderror rounded-lg px-3 py-2 text-sm focus:ring-blue-500">
                                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                {{-- Role --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign Role</label>
                                    <select name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white">
                                        <option value="officer" {{ old('role') == 'officer' ? 'selected' : '' }}>Fire Officer</option>
                                        <option value="clerk" {{ old('role') == 'clerk' ? 'selected' : '' }}>Records Clerk</option>
                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                </div>

                                {{-- Password --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                        <input type="password" name="password" required class="w-full border @error('password') border-red-500 @else border-gray-300 @enderror rounded-lg px-3 py-2 text-sm focus:ring-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                        <input type="password" name="password_confirmation" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500">
                                    </div>
                                    <div class="col-span-2">
                                        @error('password') 
                                            <p class="text-red-500 text-xs font-semibold">{{ $message }}</p> 
                                        @else
                                            <p class="text-[10px] text-gray-500 italic">Requirement: 8+ chars, uppercase, lowercase, number, and symbol.</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-3 flex flex-row-reverse">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-blue-500 ml-3">Create Account</button>
                            <button type="button" onclick="closeUserModal()" class="text-gray-700 px-4 py-2 text-sm font-semibold">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL 2: RESET PASSWORD --}}
    <div id="resetModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeResetModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:w-full sm:max-w-md">
                    
                    <form id="resetPasswordForm" method="POST">
                        @csrf @method('PUT')
                        
                        {{-- IDENTIFIER TAGS --}}
                        <input type="hidden" name="form_type" value="reset">
                        <input type="hidden" name="user_id" id="hiddenUserId" value="{{ old('user_id') }}">
                        <input type="hidden" name="user_name" id="hiddenUserName" value="{{ old('user_name') }}">

                        <div class="bg-white px-6 py-6">
                            <h3 class="text-lg font-bold text-gray-900 mb-2">Reset User Password</h3>
                            <p class="text-sm text-gray-500 mb-6">Updating password for: <span id="resetUserName" class="font-bold text-blue-600">{{ old('user_name') }}</span></p>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">New Strong Password</label>
                                    <input type="password" name="password" required class="w-full border @error('password') border-red-500 @else border-gray-300 @enderror rounded-lg px-3 py-2 text-sm focus:ring-blue-500">
                                    @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                    <input type="password" name="password_confirmation" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-blue-500">
                                </div>
                                <p class="text-[10px] text-gray-500 italic">Requirement: 8+ chars, uppercase, lowercase, number, and symbol.</p>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-6 py-3 flex flex-row-reverse">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-semibold hover:bg-blue-500 ml-3">Update Password</button>
                            <button type="button" onclick="closeResetModal()" class="text-gray-700 px-4 py-2 text-sm font-semibold">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // --- MODAL 1 FUNCTIONS ---
        function openUserModal() { document.getElementById('userModal').classList.remove('hidden'); }
        function closeUserModal() { document.getElementById('userModal').classList.add('hidden'); }

        // --- MODAL 2 FUNCTIONS ---
        function openResetModal(userId, userName) {
            const form = document.getElementById('resetPasswordForm');
            // Dynamically update the route
            form.action = `/users/${userId}/reset-password`;
            
            // Fill hidden inputs for state recovery
            document.getElementById('hiddenUserId').value = userId;
            document.getElementById('hiddenUserName').value = userName;
            
            // Update visual text
            document.getElementById('resetUserName').innerText = userName;
            
            document.getElementById('resetModal').classList.remove('hidden');
        }

        function closeResetModal() { document.getElementById('resetModal').classList.add('hidden'); }

        // --- ERROR HANDLING AUTO-OPEN ---
        // If there are errors AND the hidden tag says it was the 'reset' form...
        @if($errors->any() && old('form_type') == 'reset')
            document.addEventListener('DOMContentLoaded', function() {
                // ...re-open the reset modal for the correct user automatically.
                openResetModal('{{ old('user_id') }}', '{{ old('user_name') }}');
            });
        @endif
    </script>
</x-layout>