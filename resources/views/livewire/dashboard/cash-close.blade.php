<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
        <h1 class="text-2xl font-bold text-gray-900">Cash Close</h1>
        <p class="text-sm text-gray-600 mt-1">End-of-day cash reconciliation</p>
    </div>

    <div class="flex-1 overflow-y-auto p-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Close Form -->
        <div class="bg-white rounded-lg shadow-md p-4">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Close Cash Register</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Date *</label>
                    <input 
                        type="date" 
                        wire:model.live="closeDate"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 text-lg"
                    />
                    @error('closeDate') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="p-4 bg-blue-50 border-2 border-blue-300 rounded-lg">
                    <p class="text-sm text-blue-900 font-semibold">Expected Amount</p>
                    <p class="text-3xl font-bold text-blue-600">${{ number_format($expectedAmount ?? 0, 2) }}</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Confirmed Amount</label>
                    <input 
                        type="number" 
                        step="0.01"
                        wire:model="confirmedAmount" 
                        placeholder="0.00"
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 text-lg"
                    />
                    @error('confirmedAmount') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                </div>

                @if($confirmedAmount !== null && $expectedAmount !== null)
                    <div class="p-4 rounded-lg {{ $confirmedAmount == $expectedAmount ? 'bg-green-50 border-2 border-green-300' : 'bg-red-50 border-2 border-red-300' }}">
                        <p class="text-sm {{ $confirmedAmount == $expectedAmount ? 'text-green-900' : 'text-red-900' }} font-semibold">
                            {{ $confirmedAmount == $expectedAmount ? '✓ Match' : '✗ Discrepancy' }}
                        </p>
                        <p class="text-2xl font-bold {{ $confirmedAmount == $expectedAmount ? 'text-green-600' : 'text-red-600' }}">
                            {{ $confirmedAmount == $expectedAmount ? '$0.00' : '$' . number_format($confirmedAmount - $expectedAmount, 2) }}
                        </p>
                    </div>
                @endif

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Notes</label>
                    <textarea 
                        wire:model="notes" 
                        placeholder="Any notes about this close..."
                        class="w-full px-4 py-3 border-2 border-gray-200 rounded-lg focus:border-blue-500 text-lg h-24"
                    ></textarea>
                </div>

                <button 
                    wire:click="confirmClose"
                    class="w-full px-4 py-4 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition touch-manipulation text-lg"
                >
                    Confirm Close
                </button>
            </div>
        </div>

        <!-- History Column -->
        <div class="bg-white rounded-lg shadow-md p-4 overflow-y-auto">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Recent Closes</h2>

            @if($lastClose)
                <div class="mb-6 p-4 bg-gray-50 border-2 border-gray-200 rounded-lg">
                    <p class="text-xs text-gray-600 uppercase font-semibold">Last Close</p>
                    <p class="text-lg font-bold text-gray-900 mt-1">{{ $lastClose->close_date->format('M d, Y') }}</p>
                    <div class="mt-2 space-y-1 text-sm text-gray-600">
                        <p><strong>Expected:</strong> ${{ number_format($lastClose->expected_amount, 2) }}</p>
                        @if($lastClose->confirmed_amount)
                            <p><strong>Confirmed:</strong> ${{ number_format($lastClose->confirmed_amount, 2) }}</p>
                            @if($lastClose->hasDiscrepancy())
                                <p class="text-red-600"><strong>Discrepancy:</strong> ${{ number_format($lastClose->getDiscrepancyAmount(), 2) }}</p>
                            @endif
                        @endif
                    </div>
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <p class="text-lg">No previous closes</p>
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
                <h3 class="text-2xl font-bold text-gray-900 mb-4">Confirm Cash Close</h3>
                <div class="space-y-3 mb-6 text-gray-600">
                    <p>Date: <span class="font-bold">{{ \Carbon\Carbon::parse($closeDate)->format('M d, Y') }}</span></p>
                    <p>Expected: <span class="font-bold">${{ number_format($expectedAmount ?? 0, 2) }}</span></p>
                    <p>Confirmed: <span class="font-bold">${{ number_format($confirmedAmount ?? 0, 2) }}</span></p>
                    @if($confirmedAmount !== null && $expectedAmount !== null && $confirmedAmount != $expectedAmount)
                        <p class="text-red-600">Discrepancy: <span class="font-bold">${{ number_format($confirmedAmount - $expectedAmount, 2) }}</span></p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <button 
                        wire:click="$set('showConfirmation', false)"
                        class="px-4 py-3 bg-gray-300 hover:bg-gray-400 text-gray-900 font-semibold rounded-lg transition touch-manipulation"
                    >
                        Cancel
                    </button>
                    <button 
                        wire:click="completeClose"
                        class="px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition touch-manipulation"
                    >
                        Complete
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
