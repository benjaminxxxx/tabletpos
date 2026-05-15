<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        <!-- Top Navigation Bar -->
        <nav class="bg-white border-b border-gray-200 sticky top-0 z-40">
            <div class="px-4 py-3 flex items-center justify-between max-w-7xl mx-auto">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">POS Store</h1>
                </div>
                <div class="flex items-center gap-6">
                    <!-- User Menu -->
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="text-sm font-medium text-gray-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-500">
                                @if(auth()->user()->hasRole('admin'))
                                    Administrador
                                @elseif(auth()->user()->hasRole('seller'))
                                    Vendedor
                                @else
                                    Visualizador
                                @endif
                            </p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-md transition">
                                Salir
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="min-h-screen">
            {{ $slot }}
        </main>
    </flux:main>
</x-layouts::app.sidebar>
