<x-layout>
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">User Management</h2>
            <p class="text-gray-500 text-sm">Manage access for Fire Officers and Records Clerks.</p>
        </div>
        
        {{-- ADD USER BUTTON --}}
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
                            // UPDATED: Matching your specific database roles
                            $roleColors = [
                                'admin'   => 'bg-purple-100 text-purple-700',
                                'officer' => 'bg-red-100 text-red-700',   // Matches 'officer'
                                'clerk'   => 'bg-green-100 text-green-700', // Matches 'clerk'
                            ];
                            $roleLabel = [
                                'admin'   => 'Admin',
                                'officer' => 'Fire Officer',
                                'clerk'   => 'Records Clerk',
                            ];
                        @endphp
                        <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $roleColors[$user->role] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ $roleLabel[$user->role] ?? ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                    <td class="px-6 py-4 text-center">
                        @if(auth()->id() !== $user->id)
                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 transition" title="Delete User">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                        @else
                            <span class="text-xs text-gray-400 italic">Current User</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">
            {{ $users->links() }}
        </div>
    </div>

    {{-- CREATE USER MODAL --}}
    <div id="userModal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" onclick="closeUserModal()"></div>
        <div class="fixed inset-0 z-10 overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                            <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-3">
                                <h3 class="text-lg font-bold text-gray-900" id="modal-title">Create New Account</h3>
                                <button type="button" onclick="closeUserModal()" class="text-gray-400 hover:text-gray-500">
                                    <i class="fa-solid fa-xmark text-xl"></i>
                                </button>
                            </div>
                            
                            <div class="space-y-4">
                                {{-- Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <input type="text" name="name" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. Juan Dela Cruz">
                                </div>

                                {{-- Email --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                    <input type="email" name="email" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500" placeholder="e.g. officer@bfp.gov.ph">
                                </div>

                                {{-- Role Selection --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Assign Role</label>
                                    <select name="role" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm bg-white focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="" disabled selected>Select a role...</option>
                                        
                                        {{-- UPDATED VALUES: 'officer' and 'clerk' --}}
                                        <option value="officer">Fire Officer</option>
                                        <option value="clerk">Records Clerk</option>
                                        <option value="admin">Admin</option>
                                        
                                    </select>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <i class="fa-solid fa-circle-info mr-1"></i>
                                        <strong>Fire Officer:</strong> Can add incidents and view maps.<br>
                                        <strong>Records Clerk:</strong> Can manage audit files and logs.
                                    </p>
                                </div>

                                {{-- Password --}}
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                        <input type="password" name="password" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                        <input type="password" name="password_confirmation" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                            <button type="submit" class="inline-flex w-full justify-center rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 sm:ml-3 sm:w-auto">Create Account</button>
                            <button type="button" onclick="closeUserModal()" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openUserModal() {
            document.getElementById('userModal').classList.remove('hidden');
        }
        function closeUserModal() {
            document.getElementById('userModal').classList.add('hidden');
        }
    </script>
</x-layout>