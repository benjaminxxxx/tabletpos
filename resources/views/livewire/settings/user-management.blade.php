<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
        <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
        <p class="text-sm text-gray-600 mt-1">Manage team members and their roles</p>
    </div>

    <div class="flex-1 overflow-y-auto p-4 grid grid-cols-1 lg:grid-cols-3 gap-4">
        <!-- Invite Form -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-4 sticky top-20">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Invite User</h2>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Email *</label>
                        <input 
                            type="email" 
                            wire:model="inviteEmail" 
                            placeholder="user@example.com"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 text-lg"
                        />
                        @error('inviteEmail') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Role *</label>
                        <select 
                            wire:model="inviteRole"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 text-lg"
                        >
                            <option value="seller">Seller</option>
                            <option value="admin">Admin</option>
                        </select>
                        @error('inviteRole') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <button 
                        wire:click="inviteUser"
                        class="w-full px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition touch-manipulation text-lg"
                    >
                        Send Invite
                    </button>
                </div>
            </div>
        </div>

        <!-- Users List -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-4">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Team Members</h2>

                @if(!empty($users))
                    <div class="space-y-3">
                        @foreach($users as $user)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border-2 border-gray-200">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $user['name'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $user['email'] }}</p>
                                    
                                    <div class="mt-2 flex items-center gap-2">
                                        <select 
                                            wire:change="updateRole({{ $user['id'] }}, $event.target.value)"
                                            class="px-3 py-1 border-2 border-gray-300 rounded text-sm font-semibold"
                                        >
                                            <option value="seller" {{ ($user['pivot']['role'] ?? '') === 'seller' ? 'selected' : '' }}>Seller</option>
                                            <option value="admin" {{ ($user['pivot']['role'] ?? '') === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>

                                        @if($user['pivot']['is_blocked'])
                                            <span class="px-3 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded">Blocked</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button 
                                        wire:click="toggleBlock({{ $user['id'] }})"
                                        class="px-4 py-2 {{ $user['pivot']['is_blocked'] ? 'bg-green-600 hover:bg-green-700' : 'bg-yellow-600 hover:bg-yellow-700' }} text-white font-semibold rounded-lg transition touch-manipulation text-sm"
                                    >
                                        {{ $user['pivot']['is_blocked'] ? 'Unblock' : 'Block' }}
                                    </button>

                                    <button 
                                        wire:click="removeUser({{ $user['id'] }})"
                                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition touch-manipulation text-sm"
                                    >
                                        Remove
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        <p class="text-lg">No team members yet</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Messages -->
    @if($successMessage)
        <div class="fixed bottom-4 right-4 px-6 py-4 bg-green-600 text-white rounded-lg shadow-lg max-w-sm">
            {{ $successMessage }}
        </div>
    @endif

    @if($errorMessage)
        <div class="fixed bottom-4 right-4 px-6 py-4 bg-red-600 text-white rounded-lg shadow-lg max-w-sm">
            {{ $errorMessage }}
        </div>
    @endif
</div>
