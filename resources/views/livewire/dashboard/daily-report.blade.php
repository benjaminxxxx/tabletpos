<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header with Date Navigation -->
    <div class="bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
        <div class="flex justify-between items-center mb-3">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Daily Report</h1>
                <p class="text-sm text-gray-600">Sales, rentals & cash summary</p>
            </div>
            <div class="flex gap-2">
                <button 
                    wire:click="previousDay" 
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold touch-manipulation"
                >
                    ← Prev
                </button>
                <button 
                    wire:click="goToToday" 
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold touch-manipulation"
                >
                    Today
                </button>
                <button 
                    wire:click="nextDay" 
                    class="px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg font-semibold touch-manipulation"
                >
                    Next →
                </button>
            </div>
        </div>
        <p class="text-lg font-semibold text-blue-600">{{ $selectedDate->format('l, F j, Y') }}</p>
    </div>

    <!-- Summary Cards -->
    <div class="p-4 grid grid-cols-3 gap-3 lg:grid-cols-3">
        <div class="bg-green-50 border-2 border-green-300 rounded-lg p-4">
            <p class="text-xs font-semibold text-green-900 uppercase">Income</p>
            <p class="text-3xl font-bold text-green-600">${{ number_format($income, 2) }}</p>
        </div>
        <div class="bg-red-50 border-2 border-red-300 rounded-lg p-4">
            <p class="text-xs font-semibold text-red-900 uppercase">Outflow</p>
            <p class="text-3xl font-bold text-red-600">${{ number_format($outflow, 2) }}</p>
        </div>
        <div class="bg-blue-50 border-2 border-blue-300 rounded-lg p-4">
            <p class="text-xs font-semibold text-blue-900 uppercase">Net</p>
            <p class="text-3xl font-bold text-blue-600">${{ number_format($net, 2) }}</p>
        </div>
    </div>

    <!-- Transactions -->
    <div class="flex-1 overflow-y-auto p-4">
        <!-- Sales Section -->
        @if($sales->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-4 mb-4">
                <h2 class="text-lg font-bold text-gray-900 mb-3">Sales ({{ $sales->count() }})</h2>
                <div class="space-y-2">
                    @foreach($sales as $sale)
                        <div class="flex justify-between items-center p-3 bg-green-50 border-l-4 border-green-500 rounded">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $sale->public_code_snapshot }}</p>
                                <p class="text-xs text-gray-600">by {{ $sale->user?->name ?? 'Unknown' }}</p>
                            </div>
                            <p class="text-lg font-bold text-green-600">${{ number_format($sale->amount, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Rentals Section -->
        @if($rentals->count() > 0)
            <div class="bg-white rounded-lg shadow-md p-4 mb-4">
                <h2 class="text-lg font-bold text-gray-900 mb-3">Rentals ({{ $rentals->count() }})</h2>
                <div class="space-y-2">
                    @foreach($rentals as $rental)
                        <div class="flex justify-between items-center p-3 bg-blue-50 border-l-4 border-blue-500 rounded">
                            <div>
                                <p class="font-semibold text-gray-900">{{ $rental->public_code_snapshot }}</p>
                                <p class="text-xs text-gray-600">{{ $rental->customer?->full_name ?? 'Unknown' }}</p>
                            </div>
                            <p class="text-lg font-bold text-blue-600">${{ number_format($rental->amount, 2) }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($sales->count() === 0 && $rentals->count() === 0)
            <div class="text-center py-12 text-gray-500">
                <p class="text-lg">No transactions today</p>
            </div>
        @endif
    </div>
</div>
