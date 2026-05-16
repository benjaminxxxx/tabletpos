<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 p-6 sticky top-0 z-10 shadow-sm">
        <h1 class="text-3xl font-bold text-gray-900">Reportes de Ventas y Alquileres</h1>
        <p class="text-gray-600 mt-2">Gestiona todas tus transacciones en un lugar</p>
    </div>

    <!-- Totals Cards -->
    <div class="bg-white border-b border-gray-200 p-6">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div class="bg-gradient-to-br from-green-50 to-green-100 p-4 rounded-lg border border-green-200">
                <p class="text-xs font-semibold text-green-700 uppercase">Total Ventas</p>
                <p class="text-2xl font-bold text-green-900">${{ number_format($totals['total_sales'], 2) }}</p>
                <p class="text-xs text-green-600 mt-1">{{ $saleCount }} items</p>
            </div>

            <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-4 rounded-lg border border-purple-200">
                <p class="text-xs font-semibold text-purple-700 uppercase">Total Alquileres</p>
                <p class="text-2xl font-bold text-purple-900">${{ number_format($totals['total_rentals'], 2) }}</p>
                <p class="text-xs text-purple-600 mt-1">{{ $rentalCount }} items</p>
            </div>

            <div class="bg-gradient-to-br from-orange-50 to-orange-100 p-4 rounded-lg border border-orange-200">
                <p class="text-xs font-semibold text-orange-700 uppercase">Total Garantías</p>
                <p class="text-2xl font-bold text-orange-900">${{ number_format($totals['total_guarantees'], 2) }}</p>
            </div>

            <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-4 rounded-lg border border-blue-200">
                <p class="text-xs font-semibold text-blue-700 uppercase">Transacciones</p>
                <p class="text-2xl font-bold text-blue-900">{{ $totals['transaction_count'] }}</p>
            </div>

            <div class="bg-gradient-to-br from-slate-50 to-slate-100 p-4 rounded-lg border border-slate-200">
                <p class="text-xs font-semibold text-slate-700 uppercase">Total Final</p>
                <p class="text-2xl font-bold text-slate-900">${{ number_format($totals['grand_total'], 2) }}</p>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white border-b border-gray-200 p-6 space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Filter Type -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Tipo</label>
                <select wire:model.live="filterType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                    <option value="all">Todo</option>
                    <option value="sales">Solo Ventas</option>
                    <option value="rentals">Solo Alquileres</option>
                </select>
            </div>

            <!-- Filter by Single Date -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Fecha Específica</label>
                <input type="date" wire:model.live="filterDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Desde</label>
                <input type="date" wire:model.live="dateFrom" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Hasta</label>
                <input type="date" wire:model.live="dateTo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        <!-- Clear Filters Button -->
        <div class="flex justify-end">
            <button wire:click="$set('filterType', 'all'); $set('filterDate', null); $set('dateFrom', null); $set('dateTo', null);"
                class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg transition">
                Limpiar Filtros
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 overflow-y-auto p-6">
        @if($allItems->count() > 0)
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-100 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Transacción</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Tipo</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Fecha</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Producto</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Cantidad</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700">Precio Unit.</th>
                            <th class="px-4 py-3 text-right font-semibold text-gray-700">Subtotal</th>
                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Vendedor</th>
                            <th class="px-4 py-3 text-center font-semibold text-gray-700">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allItems as $item)
                            <tr class="border-b border-gray-200 hover:bg-gray-50 transition">
                                <!-- Transaction Number -->
                                <td class="px-4 py-3">
                                    <span class="font-mono font-bold text-blue-600">{{ $item->transaction_number }}</span>
                                </td>

                                <!-- Type Badge -->
                                <td class="px-4 py-3">
                                    @if($item->line_type === 'sale')
                                        <span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-xs font-bold">Venta</span>
                                    @else
                                        <span class="px-3 py-1 bg-purple-100 text-purple-800 rounded-full text-xs font-bold">Alquiler</span>
                                    @endif
                                </td>

                                <!-- Date -->
                                <td class="px-4 py-3 text-gray-600">{{ $item->transaction_date->format('d/m/Y H:i') }}</td>

                                <!-- Product -->
                                <td class="px-4 py-3">
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $item->product->public_code }}</p>
                                        <p class="text-xs text-gray-500">{{ $item->product->name }}</p>
                                    </div>
                                </td>

                                <!-- Quantity -->
                                <td class="px-4 py-3 text-gray-600">{{ $item->quantity }}</td>

                                <!-- Unit Price -->
                                <td class="px-4 py-3 text-right text-gray-600">${{ number_format($item->line_type === 'sale' ? $item->unit_price : $item->unit_rental_price, 2) }}</td>

                                <!-- Subtotal -->
                                <td class="px-4 py-3 text-right font-bold text-gray-900">${{ number_format($item->subtotal, 2) }}</td>

                                <!-- Seller -->
                                <td class="px-4 py-3 text-gray-600">{{ $item->user_name }}</td>

                                <!-- Actions -->
                                <td class="px-4 py-3 text-center">
                                    <div class="flex justify-center gap-2">
                                        <button 
                                            wire:click="edit{{ ucfirst($item->line_type) }}Detail({{ $item->id }})"
                                            class="text-blue-600 hover:text-blue-800 font-semibold"
                                        >
                                            ✎
                                        </button>
                                        <button 
                                            wire:click="delete{{ ucfirst($item->line_type) }}Detail({{ $item->id }})"
                                            onclick="confirm('¿Eliminar este item?') || event.stopImmediatePropagation()"
                                            class="text-red-600 hover:text-red-800 font-semibold"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-12 text-center">
                <p class="text-xl text-gray-600">No hay transacciones para mostrar</p>
                <p class="text-gray-500">Intenta ajustando los filtros</p>
            </div>
        @endif
    </div>

    <!-- EDIT MODAL -->
    @if($showEditModal && $editingDetailId)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
                <h3 class="text-2xl font-bold text-gray-900 mb-4">
                    Editar {{ $editingType === 'sale' ? 'Venta' : 'Alquiler' }}
                </h3>

                <form wire:submit="{{ $editingType === 'sale' ? 'saveSaleDetail' : 'saveRentalDetail' }}" class="space-y-4">
                    <!-- Product Selection (only for sales) -->
                    @if($editingType === 'sale')
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Producto</label>
                            <select wire:model="editProductId" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500">
                                @foreach($this->getAvailableProducts() as $product)
                                    <option value="{{ $product->id }}">{{ $product->public_code }} - {{ $product->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <!-- Quantity -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Cantidad</label>
                        <input type="number" wire:model="editQuantity" min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2">
                        @error('editQuantity') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ $editingType === 'sale' ? 'Precio Unitario' : 'Precio de Alquiler' }}
                        </label>
                        <input type="number" step="0.01" wire:model="editPrice" min="0" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2">
                        @error('editPrice') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <!-- Preview Subtotal -->
                    <div class="bg-gray-50 p-3 rounded-lg">
                        <p class="text-sm text-gray-600">Subtotal:</p>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($editQuantity * $editPrice, 2) }}</p>
                    </div>

                    <div class="flex gap-2 pt-4">
                        <button type="submit" class="flex-1 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                            Guardar
                        </button>
                        <button type="button" wire:click="$set('showEditModal', false)" class="px-4 py-3 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
