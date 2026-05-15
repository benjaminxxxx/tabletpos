<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
        <h1 class="text-2xl font-bold text-gray-900">Rental Manager</h1>
        <p class="text-sm text-gray-600 mt-1">Manage rental transactions</p>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto p-4 grid grid-cols-2 gap-4 lg:grid-cols-3">
        <!-- Left Column: Search & Selection -->
        <div class="lg:col-span-1 space-y-4">
            <!-- Product Search -->
            <div class="bg-white rounded-lg shadow-md p-4 sticky top-20">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Search Product</label>
                <input 
                    type="text" 
                    wire:model.live="productSearch" 
                    placeholder="Product code or name..."
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none text-lg"
                />

                @if($searchResults)
                    <div class="mt-3 max-h-48 overflow-y-auto space-y-2">
                        @foreach($searchResults as $product)
                            <button 
                                wire:click="selectProduct({{ $product['id'] }})"
                                class="w-full text-left px-4 py-3 bg-blue-50 hover:bg-blue-100 border-2 border-blue-200 rounded-lg transition touch-manipulation"
                            >
                                <div class="font-semibold">{{ $product['public_code'] }}</div>
                                <div class="text-sm text-gray-600">{{ $product['name'] }}</div>
                            </button>
                        @endforeach
                    </div>
                @endif

                @if($selectedProduct)
                    <div class="mt-3 p-3 bg-green-50 border-2 border-green-300 rounded-lg">
                        <p class="text-xs font-semibold text-green-900">✓ Selected</p>
                        <p class="font-bold">{{ $selectedProduct->public_code }}</p>
                        <p class="text-sm text-gray-600">{{ $selectedProduct->name }}</p>
                    </div>
                @endif
            </div>

            <!-- Customer Search -->
            <div class="bg-white rounded-lg shadow-md p-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Find Customer</label>
                <input 
                    type="text" 
                    wire:model.live="customerSearch" 
                    placeholder="DNI or name..."
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none text-lg"
                />

                @if($customerResults)
                    <div class="mt-3 max-h-40 overflow-y-auto space-y-2">
                        @foreach($customerResults as $customer)
                            <button 
                                wire:click="selectCustomer({{ $customer['id'] }})"
                                class="w-full text-left px-4 py-3 bg-blue-50 hover:bg-blue-100 border-2 border-blue-200 rounded-lg transition touch-manipulation"
                            >
                                <div class="font-semibold">{{ $customer['full_name'] }}</div>
                                <div class="text-xs text-gray-600">DNI: {{ $customer['dni'] ?? 'N/A' }}</div>
                            </button>
                        @endforeach
                    </div>
                @endif

                @if($selectedCustomer)
                    <div class="mt-3 p-3 bg-green-50 border-2 border-green-300 rounded-lg">
                        <p class="text-xs font-semibold text-green-900">✓ Selected</p>
                        <p class="font-bold">{{ $selectedCustomer->full_name }}</p>
                        <p class="text-sm text-gray-600">{{ $selectedCustomer->dni }}</p>
                    </div>
                @endif

                <button 
                    wire:click="toggleCustomerForm"
                    class="w-full mt-3 px-4 py-3 border-2 border-blue-500 text-blue-600 font-semibold rounded-lg transition touch-manipulation"
                >
                    {{ $showCustomerForm ? 'Cancel' : 'Create New' }}
                </button>
            </div>
        </div>

        <!-- Right Column: Rental Form -->
        <div class="lg:col-span-2">
            @if($showCustomerForm)
                <!-- New Customer Form -->
                <div class="bg-white rounded-lg shadow-md p-4 space-y-3">
                    <h3 class="text-lg font-bold text-gray-900">New Customer</h3>
                    
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">DNI *</label>
                        <input 
                            type="text" 
                            wire:model="customerDni" 
                            placeholder="Customer DNI"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 text-lg"
                        />
                        @error('customerDni') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
                        <input 
                            type="text" 
                            wire:model="customerName" 
                            placeholder="Full name"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 text-lg"
                        />
                        @error('customerName') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Phone</label>
                        <input 
                            type="tel" 
                            wire:model="customerPhone" 
                            placeholder="Phone number"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 text-lg"
                        />
                    </div>

                    <button 
                        wire:click="createCustomer"
                        class="w-full px-4 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition touch-manipulation text-lg"
                    >
                        Create Customer
                    </button>
                </div>
            @else
                <!-- Rental Details Form -->
                <div class="bg-white rounded-lg shadow-md p-4 space-y-4">
                    <h3 class="text-lg font-bold text-gray-900">Rental Details</h3>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rental Amount *</label>
                        <input 
                            type="number" 
                            step="0.01"
                            wire:model="rentalAmount" 
                            placeholder="0.00"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 text-lg"
                        />
                        @error('rentalAmount') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Deposit Amount</label>
                        <input 
                            type="number" 
                            step="0.01"
                            wire:model="depositAmount" 
                            placeholder="0.00 (optional)"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 text-lg"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Return Date *</label>
                        <input 
                            type="date" 
                            wire:model="returnDate"
                            class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 text-lg"
                        />
                        @error('returnDate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 border-t-2 border-gray-200">
                        <button 
                            wire:click="confirmRental"
                            class="w-full px-4 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition touch-manipulation text-lg"
                        >
                            Create Rental
                        </button>
                    </div>
                </div>
            @endif
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

    <!-- Confirmation Modal -->
    @if($showConfirmation)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-lg max-w-sm w-full p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Confirm Rental?</h3>
                <div class="space-y-2 mb-4 text-gray-600">
                    <p>Product: <span class="font-bold">{{ $selectedProduct?->public_code ?? 'N/A' }}</span></p>
                    <p>Customer: <span class="font-bold">{{ $selectedCustomer?->full_name ?? 'N/A' }}</span></p>
                    <p>Amount: <span class="font-bold">${{ number_format($rentalAmount, 2) }}</span></p>
                    @if($depositAmount)
                    <p>Deposit: <span class="font-bold">${{ number_format($depositAmount, 2) }}</span></p>
                    @endif
                    <p>Return Date: <span class="font-bold">{{ \Carbon\Carbon::parse($returnDate)->format('M d, Y') }}</span></p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button 
                        wire:click="$set('showConfirmation', false)"
                        class="px-4 py-4 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg transition touch-manipulation text-lg"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="completeRental"
                        class="px-4 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition touch-manipulation text-lg"
                    >
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
