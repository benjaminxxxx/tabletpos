<div class="min-h-screen bg-gray-50 flex flex-col">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 p-4 sticky top-0 z-10">
        <h1 class="text-2xl font-bold text-gray-900">Batch Product Registration</h1>
        <p class="text-sm text-gray-600 mt-1">Add multiple products at once using tab-separated format</p>
    </div>

    <div class="flex-1 overflow-y-auto p-4 grid grid-cols-1 lg:grid-cols-2 gap-4">
        <!-- Input Column -->
        <div class="bg-white rounded-lg shadow-md p-4">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Input Data</h2>
            <p class="text-sm text-gray-600 mb-3">Format: name TAB description TAB brand TAB origin TAB category TAB quantity</p>
            
            <textarea 
                wire:model="batchData"
                placeholder="Example:&#10;Blue Suit&#9;Size 40&#9;Hugo Boss&#9;Gamarra&#9;ZA&#9;2&#10;Red Dress&#9;Size S&#9;Forever21&#9;Shein&#9;VE&#9;3"
                class="w-full h-64 px-4 py-3 border-2 border-gray-200 rounded-lg font-mono text-sm focus:border-blue-500 focus:outline-none"
            ></textarea>

            <button 
                wire:click="parseBatchData"
                class="w-full mt-4 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition touch-manipulation text-lg"
            >
                Parse Data
            </button>
        </div>

        <!-- Preview Column -->
        <div class="bg-white rounded-lg shadow-md p-4 overflow-y-auto">
            <h2 class="text-lg font-bold text-gray-900 mb-3">Preview</h2>

            @if(!empty($parsedRows))
                <div class="space-y-2 mb-4">
                    @foreach($parsedRows as $index => $row)
                        <div class="p-3 bg-green-50 border-l-4 border-green-500 rounded">
                            <p class="font-semibold text-gray-900">{{ $row['name'] }}</p>
                            <p class="text-xs text-gray-600">{{ $row['category_prefix'] }} × {{ $row['quantity'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t-2 border-gray-200">
                    <p class="text-sm font-semibold text-gray-700 mb-3">
                        Total Products: <span class="text-lg text-blue-600">{{ collect($parsedRows)->sum('quantity') }}</span>
                    </p>
                    <button 
                        wire:click="saveBatch"
                        class="w-full px-4 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition touch-manipulation text-lg"
                    >
                        Create Products
                    </button>
                </div>
            @elseif(!empty($errors))
                <div class="space-y-2">
                    @foreach($errors as $index => $error)
                        <div class="p-3 bg-red-50 border-l-4 border-red-500 rounded">
                            <p class="text-xs font-semibold text-red-900">Line {{ $index + 1 }}</p>
                            <p class="text-sm text-red-700">{{ $error }}</p>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12 text-gray-500">
                    <p class="text-lg">Enter data and click Parse to see preview</p>
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
</div>
