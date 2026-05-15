<x-layouts::app :title="__('Dashboard')">
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Bienvenido, {{ auth()->user()->name }}
                </h1>
                <p class="mt-2 text-gray-600">
                    Sistema de Gestión de Ventas y Rentals
                </p>
            </div>

            <!-- User Role Info -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-700">
                    <strong>Rol:</strong> 
                    <span class="capitalize font-semibold">
                        @if(auth()->user()->hasRole('admin'))
                            Administrador (Acceso Total)
                        @elseif(auth()->user()->hasRole('seller'))
                            Vendedor
                        @else
                            Visualizador
                        @endif
                    </span>
                </p>
            </div>

            <!-- Modules Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- 1. POS - Vender -->
                @can('sell')
                    <a href="{{ route('pos.sell') }}" class="group">
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition h-full p-6 border-l-4 border-green-500">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Vender</h3>
                                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                Realizar ventas de productos. Busca, añade al carrito y confirma.
                            </p>
                            <div class="flex items-center text-green-600 group-hover:text-green-700">
                                <span class="text-sm font-medium">Acceder</span>
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endcan

                <!-- 2. Rentals -->
                @can('rent')
                    <a href="{{ route('pos.rent') }}" class="group">
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition h-full p-6 border-l-4 border-purple-500">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Rentals</h3>
                                <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                Gestionar rentals de productos. Datos de cliente, depósitos y seguimiento.
                            </p>
                            <div class="flex items-center text-purple-600 group-hover:text-purple-700">
                                <span class="text-sm font-medium">Acceder</span>
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endcan

                <!-- 3. Catálogo de Productos -->
                @can('manage-products')
                    <a href="{{ route('inventory.catalog') }}" class="group">
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition h-full p-6 border-l-4 border-blue-500">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Productos</h3>
                                <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.972 1.972 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                Ver catálogo de productos, filtrar, editar y gestionar inventario.
                            </p>
                            <div class="flex items-center text-blue-600 group-hover:text-blue-700">
                                <span class="text-sm font-medium">Acceder</span>
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endcan

                <!-- 4. Registro en Lote -->
                @can('manage-products')
                    <a href="{{ route('inventory.batch-register') }}" class="group">
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition h-full p-6 border-l-4 border-indigo-500">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Registro en Lote</h3>
                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                Registra múltiples productos desde Excel o CSV de una vez.
                            </p>
                            <div class="flex items-center text-indigo-600 group-hover:text-indigo-700">
                                <span class="text-sm font-medium">Acceder</span>
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endcan

                <!-- 5. Reportes -->
                @can('view-reports')
                    <a href="{{ route('dashboard.reports') }}" class="group">
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition h-full p-6 border-l-4 border-orange-500">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Reportes</h3>
                                <svg class="w-8 h-8 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                Ver resumen diario de ventas, rentals, movimientos y ganancias.
                            </p>
                            <div class="flex items-center text-orange-600 group-hover:text-orange-700">
                                <span class="text-sm font-medium">Acceder</span>
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endcan

                <!-- 6. Cierre de Caja -->
                @can('cash-close')
                    <a href="{{ route('dashboard.cash-close') }}" class="group">
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition h-full p-6 border-l-4 border-red-500">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Cierre de Caja</h3>
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                Hacer cierre diario de caja y reconciliar montos esperados vs confirmados.
                            </p>
                            <div class="flex items-center text-red-600 group-hover:text-red-700">
                                <span class="text-sm font-medium">Acceder</span>
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endcan

                <!-- 7. Gestión de Usuarios (ADMIN ONLY) -->
                @can('manage-users')
                    <a href="{{ route('settings.users') }}" class="group">
                        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition h-full p-6 border-l-4 border-cyan-500">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900">Usuarios</h3>
                                <svg class="w-8 h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 12H9m0 0H6m3 0v4m0 0v3m6-12a4 4 0 110 5.292M12 20H9m3 0h3m0 0v3"></path>
                                </svg>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                Invitar vendedores, asignar roles, bloquear usuarios y gestionar permisos.
                            </p>
                            <div class="flex items-center text-cyan-600 group-hover:text-cyan-700">
                                <span class="text-sm font-medium">Acceder</span>
                                <svg class="w-4 h-4 ml-2 group-hover:translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </div>
                        </div>
                    </a>
                @endcan

            </div>

            <!-- Empty State -->
            @if(auth()->user()->cannot('sell') && auth()->user()->cannot('rent') && auth()->user()->cannot('manage-products') && auth()->user()->cannot('view-reports') && auth()->user()->cannot('cash-close') && auth()->user()->cannot('manage-users'))
                <div class="mt-12 p-8 bg-yellow-50 border border-yellow-200 rounded-lg text-center">
                    <p class="text-yellow-700">
                        No tienes permisos para acceder a ningún módulo. Contacta al administrador.
                    </p>
                </div>
            @endif
        </div>
    </div>
</x-layouts::app>
