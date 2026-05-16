<div class="bg-white rounded-lg shadow-lg p-6" x-data="cartData()" @cart-updated.window="calculateTotals()">
    <!-- Botón para abrir carrito -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-900">Carrito</h2>
        <button @click="$wire.showCart = !$wire.showCart" class="relative">
            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            @if ($saleItemCount + $rentalItemCount > 0)
                <span class="absolute top-0 right-0 bg-red-500 text-white text-xs font-bold rounded-full w-6 h-6 flex items-center justify-center">
                    {{ $saleItemCount + $rentalItemCount }}
                </span>
            @endif
        </button>
    </div>

    @if ($showCart)
        <!-- Carrito abierto -->
        <div class="border-t pt-6">
            <!-- Tabs: Ventas y Alquileres -->
            <div class="flex gap-4 mb-6 border-b">
                <button @click="activeTab = 'sales'" :class="activeTab === 'sales' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="pb-2 font-semibold">
                    Ventas ({{ $saleItemCount }})
                </button>
                <button @click="activeTab = 'rentals'" :class="activeTab === 'rentals' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-600'" class="pb-2 font-semibold">
                    Alquileres ({{ $rentalItemCount }})
                </button>
            </div>

            <!-- TAB: VENTAS -->
            <div x-show="activeTab === 'sales'" class="space-y-4 mb-6">
                @forelse ($saleItems as $key => $item)
                    <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-green-500">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $item['product_name'] }}</p>
                                <p class="text-sm text-gray-500">ID: {{ $item['product_id'] }}</p>
                            </div>
                            <button wire:click="removeSaleItem('{{ $key }}')" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-3 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Cantidad</label>
                                <input type="number" min="1" x-model.number="cart.sales['{{ $key }}'].quantity" 
                                    wire:change="updateSaleItemQuantity('{{ $key }}', $event.target.value)"
                                    @change="calculateTotals()"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Precio Unitario</label>
                                <input type="number" step="0.01" min="0" x-model.number="cart.sales['{{ $key }}'].unit_price"
                                    wire:change="updateSaleItemPrice('{{ $key }}', $event.target.value)"
                                    @change="calculateTotals()"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Subtotal</label>
                                <p class="text-lg font-bold text-green-600">
                                    $<span x-text="(cart.sales['{{ $key }}']?.quantity * cart.sales['{{ $key }}']?.unit_price).toFixed(2)"></span>
                                </p>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">Sin ventas en el carrito</p>
                @endforelse
            </div>

            <!-- TAB: ALQUILERES -->
            <div x-show="activeTab === 'rentals'" class="space-y-4 mb-6">
                @forelse ($rentalItems as $key => $item)
                    <div class="bg-gray-50 p-4 rounded-lg border-l-4 border-purple-500">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $item['product_name'] }}</p>
                                <p class="text-sm text-gray-500">Cliente: {{ $item['customer_name'] }}</p>
                                <p class="text-sm text-gray-500">DNI: {{ $item['dni_number'] }}</p>
                            </div>
                            <button wire:click="removeRentalItem('{{ $key }}')" class="text-red-600 hover:text-red-800">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </div>

                        <div class="grid grid-cols-2 gap-3 mb-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Cantidad</label>
                                <input type="number" min="1" x-model.number="cart.rentals['{{ $key }}'].quantity"
                                    @change="calculateTotals()"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Precio de Alquiler</label>
                                <input type="number" step="0.01" min="0" x-model.number="cart.rentals['{{ $key }}'].unit_rental_price"
                                    wire:change="updateRentalItemPrice('{{ $key }}', $event.target.value)"
                                    @change="calculateTotals()"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Garantía</label>
                                <input type="number" step="0.01" min="0" x-model.number="cart.rentals['{{ $key }}'].guarantee_amount"
                                    wire:change="updateRentalItemGuarantee('{{ $key }}', $event.target.value)"
                                    @change="calculateTotals()"
                                    class="w-full px-2 py-1 border border-gray-300 rounded text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-600 mb-1">Subtotal</label>
                                <p class="text-lg font-bold text-purple-600">
                                    $<span x-text="(cart.rentals['{{ $key }}']?.quantity * cart.rentals['{{ $key }}']?.unit_rental_price).toFixed(2)"></span>
                                </p>
                            </div>
                        </div>

                        <div class="mt-3 pt-3 border-t text-sm text-gray-600">
                            <p><strong>Fechas:</strong> {{ $item['rental_start_date'] }} a {{ $item['rental_return_date'] }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-500 text-center py-4">Sin alquileres en el carrito</p>
                @endforelse
            </div>

            <!-- RESUMEN DE TOTALES -->
            <div class="bg-blue-50 p-4 rounded-lg border-2 border-blue-200 mb-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-600">Total Ventas</p>
                        <p class="text-xl font-bold text-green-600">
                            $<span x-text="totalSales.toFixed(2)">{{ number_format($totalSales, 2) }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Alquileres</p>
                        <p class="text-xl font-bold text-purple-600">
                            $<span x-text="totalRentals.toFixed(2)">{{ number_format($totalRentals, 2) }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Garantías</p>
                        <p class="text-xl font-bold text-orange-600">
                            $<span x-text="totalGuarantees.toFixed(2)">{{ number_format($totalGuarantees, 2) }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">TOTAL FINAL</p>
                        <p class="text-2xl font-bold text-blue-600">
                            $<span x-text="grandTotal.toFixed(2)">{{ number_format($grandTotal, 2) }}</span>
                        </p>
                    </div>
                </div>

                @if ($saleItemCount + $rentalItemCount > 0)
                    <button wire:click="processCart" @click="$wire.showConfirmation = true"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition">
                        Procesar Transacción
                    </button>
                @endif
            </div>
        </div>
    @endif

    <!-- Alpine.js data -->
    <script>
        function cartData() {
            return {
                activeTab: 'sales',
                cart: {
                    sales: @json($saleItems),
                    rentals: @json($rentalItems),
                },
                totalSales: @json($totalSales),
                totalRentals: @json($totalRentals),
                totalGuarantees: @json($totalGuarantees),
                grandTotal: @json($grandTotal),

                calculateTotals() {
                    let salesTotal = 0;
                    let rentalsTotal = 0;
                    let guaranteesTotal = 0;

                    // Calcular ventas
                    Object.values(this.cart.sales).forEach(item => {
                        salesTotal += item.quantity * item.unit_price;
                    });

                    // Calcular alquileres
                    Object.values(this.cart.rentals).forEach(item => {
                        rentalsTotal += item.quantity * item.unit_rental_price;
                        guaranteesTotal += item.guarantee_amount || 0;
                    });

                    this.totalSales = salesTotal;
                    this.totalRentals = rentalsTotal;
                    this.totalGuarantees = guaranteesTotal;
                    this.grandTotal = salesTotal + rentalsTotal + guaranteesTotal;

                    // Emitir evento para Livewire
                    window.dispatchEvent(new CustomEvent('cart-updated'));
                }
            }
        }
    </script>
</div>
