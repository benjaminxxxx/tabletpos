<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
        <h1 class="text-2xl font-bold text-gray-900">POS - Sell</h1>
        <p class="text-sm text-gray-600 mt-1">Search products and manage sales</p>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto p-4 grid grid-cols-2 gap-4 lg:grid-cols-3">
        <!-- Search & Selection Column -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow-md p-4 sticky top-20">
                <label class="block text-sm font-semibold text-gray-700 mb-2">Search Product</label>
                <input 
                    type="text" 
                    wire:model.live="productSearch" 
                    placeholder="Code, name, or brand..."
                    class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:outline-none text-lg"
                />

                <!-- Search Results -->
                @if($searchResults)
                    <div class="mt-3 max-h-64 overflow-y-auto space-y-2">
                        @foreach($searchResults as $product)
                            <button 
                                wire:click="selectProduct({{ $product['id'] }})"
                                class="w-full text-left px-4 py-3 bg-blue-50 hover:bg-blue-100 border-2 border-blue-200 rounded-lg transition touch-manipulation"
                            >
                                <div class="font-semibold text-gray-900">{{ $product['public_code'] }}</div>
                                <div class="text-sm text-gray-600">{{ $product['name'] }}</div>
                            </button>
                        @endforeach
                    </div>
                @endif

                <!-- Selected Product Preview -->
                @if($selectedProduct)
                    <div class="mt-4 p-3 bg-green-50 border-2 border-green-300 rounded-lg">
                        <p class="text-sm font-semibold text-green-900">Selected</p>
                        <p class="font-bold text-gray-900">{{ $selectedProduct->public_code }}</p>
                        <p class="text-sm text-gray-600">{{ $selectedProduct->name }}</p>
                        <button 
                            wire:click="addToCart"
                            class="w-full mt-2 px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition touch-manipulation text-lg"
                        >
                            Add to Cart
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Cart Column -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-4">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-bold text-gray-900">Cart</h2>
                    <span class="text-2xl font-bold text-blue-600">{{ count($cartItems) }} items</span>
                </div>

                @if(!empty($cartItems))
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($cartItems as $index => $item)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg border-2 border-gray-200">
                                <div class="flex-1">
                                    <p class="font-semibold text-gray-900">{{ $item['public_code'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $item['name'] }}</p>
                                </div>
                                <input 
                                    type="number" 
                                    step="0.01"
                                    wire:change="updateAmount({{ $index }}, $event.target.value)"
                                    value="{{ $item['amount'] }}"
                                    placeholder="Amount"
                                    class="w-24 px-3 py-2 border-2 border-gray-200 rounded-lg text-lg"
                                />
                                <button 
                                    wire:click="removeFromCart({{ $index }})"
                                    class="px-4 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition touch-manipulation"
                                >
                                    ✕
                                </button>
                            </div>
                        @endforeach
                    </div>

                    <!-- Cart Total -->
                    <div class="mt-4 pt-4 border-t-2 border-gray-200">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-lg font-semibold text-gray-900">Total:</span>
                            <span class="text-3xl font-bold text-blue-600">${{ number_format($total, 2) }}</span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <button 
                                wire:click="clearCart"
                                class="px-4 py-4 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg transition touch-manipulation text-lg"
                            >
                                Clear
                            </button>
                            <button 
                                wire:click="confirmSale"
                                class="px-4 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition touch-manipulation text-lg"
                            >
                                Confirm Sale
                            </button>
                        </div>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        <p class="text-xl">Cart is empty</p>
                        <p class="text-sm">Select a product from the left to add items</p>
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

    <!-- Confirmation Modal -->
    @if($showConfirmation)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
            <div class="bg-white rounded-lg shadow-lg max-w-sm w-full p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Confirm Sale?</h3>
                <p class="text-gray-600 mb-2">Total: <span class="font-bold text-lg">${{ number_format($total, 2) }}</span></p>
                <p class="text-gray-600 mb-4">Items: <span class="font-bold">{{ count($cartItems) }}</span></p>

                <div class="grid grid-cols-2 gap-3">
                    <button 
                        wire:click="$set('showConfirmation', false)"
                        class="px-4 py-4 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg transition touch-manipulation text-lg"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="completeSale"
                        class="px-4 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition touch-manipulation text-lg"
                    >
                        Complete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
