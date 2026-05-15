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

                <button 
                    wire:click="$set('selectedProduct', null)"
                    class="w-full mt-4 px-4 py-3 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg"
                >
                    Close
                </button>
            </div>
        </div>
    @endif
</div>
