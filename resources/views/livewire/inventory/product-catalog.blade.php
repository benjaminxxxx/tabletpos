<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
        <h1 class="text-2xl font-bold text-gray-900">Product Catalog</h1>
        <p class="text-sm text-gray-600 mt-1">Browse inventory by category, status, or location</p>
    </div>

    <!-- Filters -->
    <div class="bg-white border-b border-gray-200 p-4 flex gap-2 overflow-x-auto sticky top-16">
        <select 
            wire:model.live="categoryFilter"
            class="px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 font-semibold"
        >
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}">{{ $cat }}</option>
            @endforeach
        </select>

        <select 
            wire:model.live="statusFilter"
            class="px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 font-semibold"
        >
            <option value="">All Status</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}">{{ ucfirst($status) }}</option>
            @endforeach
        </select>

        <select 
            wire:model.live="locationFilter"
            class="px-4 py-2 border-2 border-gray-200 rounded-lg focus:border-blue-500 font-semibold"
        >
            <option value="">All Locations</option>
            @foreach($locations as $loc)
                <option value="{{ $loc->id }}">{{ $loc->name }}</option>
            @endforeach
        </select>

        <button 
            wire:click="clearFilters"
            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded-lg font-semibold touch-manipulation"
        >
            Clear
        </button>
    </div>

    <!-- Product Grid -->
    <div class="flex-1 overflow-y-auto p-4">
        @if($products->count() > 0)
            <div class="grid grid-cols-2 gap-4 lg:grid-cols-3">
                @foreach($products as $product)
                    <button 
                        wire:click="selectProduct({{ $product->id }})"
                        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition touch-manipulation text-left"
                    >
                        <!-- Product Image -->
                        <div class="relative w-full aspect-square bg-gray-200 flex items-center justify-center overflow-hidden">
                            @if($product->media->count() > 0)
                                <img 
                                    src="{{ asset('storage/' . $product->media->first()->path) }}" 
                                    alt="{{ $product->name }}"
                                    class="w-full h-full object-cover"
                                />
                            @else
                                <div class="text-gray-400 text-center">
                                    <p class="text-sm">No image</p>
                                </div>
                            @endif
                            
                            <!-- Status Badge -->
                            <div class="absolute top-2 right-2">
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-bold text-white"
                                    style="background-color: {{ match($product->status) {
                                        'available' => '#10b981',
                                        'rented' => '#f59e0b',
                                        'blocked' => '#ef4444',
                                        'laundry' => '#3b82f6',
                                        'maintenance' => '#9ca3af',
                                        default => '#9ca3af',
                                    } }}"
                                >
                                    {{ ucfirst($product->status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Product Info -->
                        <div class="p-3">
                            <p class="font-bold text-lg text-gray-900">{{ $product->public_code }}</p>
                            <p class="text-sm text-gray-600 line-clamp-2">{{ $product->name }}</p>
                            
                            <div class="mt-2 text-xs text-gray-500 space-y-1">
                                @if($product->brand)
                                    <p><strong>Brand:</strong> {{ $product->brand }}</p>
                                @endif
                                @if($product->location)
                                    <p><strong>Location:</strong> {{ $product->location->name }}</p>
                                @endif
                            </div>

                            <div class="mt-2 flex gap-1 text-xs">
                                @if($product->can_sell)
                                    <span class="px-2 py-1 bg-green-100 text-green-900 rounded">Can Sell</span>
                                @endif
                                @if($product->can_rent)
                                    <span class="px-2 py-1 bg-blue-100 text-blue-900 rounded">Can Rent</span>
                                @endif
                            </div>
                        </div>
                    </button>
                @endforeach
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="mt-6 flex justify-center">
                    {{ $products->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12 text-gray-500">
                <p class="text-lg">No products found</p>
                <p class="text-sm">Try adjusting your filters</p>
            </div>
        @endif
    </div>

    <!-- Product Details Modal -->
    @if($selectedProduct)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-end z-50">
            <div class="bg-white w-full rounded-t-lg shadow-lg p-4 max-h-96 overflow-y-auto">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-2xl font-bold text-gray-900">{{ $selectedProduct->public_code }}</h3>
                        <p class="text-gray-600">{{ $selectedProduct->name }}</p>
                    </div>
                    <button 
                        wire:click="$set('selectedProduct', null)"
                        class="text-2xl text-gray-400 hover:text-gray-600"
                    >
                        ✕
                    </button>
                </div>

                <div class="space-y-3 text-sm">
                    <div>
                        <p class="font-semibold text-gray-700">Status</p>
                        <p class="text-gray-600">{{ ucfirst($selectedProduct->status) }}</p>
                    </div>
                    @if($selectedProduct->brand)
                        <div>
                            <p class="font-semibold text-gray-700">Brand</p>
                            <p class="text-gray-600">{{ $selectedProduct->brand }}</p>
                        </div>
                    @endif
                    @if($selectedProduct->origin)
                        <div>
                            <p class="font-semibold text-gray-700">Origin</p>
                            <p class="text-gray-600">{{ $selectedProduct->origin }}</p>
                        </div>
                    @endif
                    @if($selectedProduct->location)
                        <div>
                            <p class="font-semibold text-gray-700">Location</p>
                            <p class="text-gray-600">{{ $selectedProduct->location->name }}</p>
                        </div>
                    @endif
                    <div>
                        <p class="font-semibold text-gray-700">Rentals / Sales</p>
                        <p class="text-gray-600">{{ $selectedProduct->rent_count }} / {{ $selectedProduct->sale_count }}</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2 mt-6">
                    @if($selectedProduct->can_sell)
                        <button 
                            wire:click="openSellModal"
                            class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition"
                        >
                            Vender
                        </button>
                    @endif
                    
                    @if($selectedProduct->can_rent)
                        <button 
                            wire:click="openRentalModal"
                            class="flex-1 px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition"
                        >
                            Alquilar
                        </button>
                    @endif
                    
                    <button 
                        wire:click="$set('selectedProduct', null)"
                        class="px-4 py-3 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg"
                    >
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- SELL MODAL -->
    @if($showSellModal && $selectedProduct)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Vender: {{ $selectedProduct->name }}</h3>
                
                <form wire:submit="addToCartSell" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Cantidad</label>
                        <input type="number" wire:model="sellQuantity" min="1" max="{{ $selectedProduct->quantity_available }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        @error('sellQuantity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1">Disponible: {{ $selectedProduct->quantity_available }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Precio Unitario</label>
                        <input type="number" step="0.01" wire:model="sellPrice"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        @error('sellPrice') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="bg-green-50 p-3 rounded-lg">
                        <p class="text-sm text-gray-600">Subtotal:</p>
                        <p class="text-2xl font-bold text-green-600">${{ number_format($sellQuantity * $sellPrice, 2) }}</p>
                    </div>

                    <div class="flex gap-2 pt-4">
                        <button type="submit" class="flex-1 px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                            Agregar al Carrito
                        </button>
                        <button type="button" wire:click="$set('showSellModal', false)" class="px-4 py-3 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- RENTAL MODAL -->
    @if($showRentalModal && $selectedProduct)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
            <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6 my-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Alquilar: {{ $selectedProduct->name }}</h3>
                
                <form wire:submit="addToCartRental" class="space-y-4">
                    <!-- Customer Selection -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Cliente</label>
                        <select wire:model="customerId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            <option value="">Seleccionar cliente</option>
                            @foreach($this->getCustomers() as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->dni }})</option>
                            @endforeach
                        </select>
                        @error('customerId') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- DNI Number -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">DNI</label>
                        <input type="text" wire:model="dniNumber" placeholder="Ej: 12345678"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        @error('dniNumber') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- DNI Photo (will be handled by file upload in real implementation) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Foto del DNI</label>
                        <input type="text" wire:model="dniPhotoUrl" placeholder="URL o ruta de la foto"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-xs">
                        @error('dniPhotoUrl') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Additional Photo (optional) -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Foto Adicional (Opcional)</label>
                        <input type="text" wire:model="additionalPhotoUrl" placeholder="URL o ruta"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-xs">
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Cantidad</label>
                        <input type="number" wire:model="rentalQuantity" min="1" max="{{ $selectedProduct->quantity_available }}"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        @error('rentalQuantity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                        <p class="text-xs text-gray-500 mt-1">Disponible: {{ $selectedProduct->quantity_available }}</p>
                    </div>

                    <!-- Rental Price -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Precio de Alquiler</label>
                        <input type="number" step="0.01" wire:model="rentalPrice"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                        @error('rentalPrice') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Guarantee Amount -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Monto de Garantía (Opcional)</label>
                        <input type="number" step="0.01" wire:model="guaranteeAmount"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                    </div>

                    <!-- Rental Dates -->
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Salida</label>
                            <input type="date" wire:model="rentalStartDate"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            @error('rentalStartDate') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Retorno</label>
                            <input type="date" wire:model="rentalReturnDate"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500">
                            @error('rentalReturnDate') <span class="text-red-600 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Observations -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Observaciones</label>
                        <textarea wire:model="observations" rows="2" placeholder="Notas adicionales"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500"></textarea>
                    </div>

                    <div class="bg-purple-50 p-3 rounded-lg">
                        <p class="text-sm text-gray-600">Subtotal Alquiler:</p>
                        <p class="text-2xl font-bold text-purple-600">${{ number_format($rentalQuantity * $rentalPrice, 2) }}</p>
                        @if($guaranteeAmount > 0)
                            <p class="text-sm text-gray-600 mt-2">+ Garantía: ${{ number_format($guaranteeAmount, 2) }}</p>
                        @endif
                    </div>

                    <div class="flex gap-2 pt-4">
                        <button type="submit" class="flex-1 px-4 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition">
                            Agregar al Carrito
                        </button>
                        <button type="button" wire:click="$set('showRentalModal', false)" class="px-4 py-3 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
