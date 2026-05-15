{{-- resources/views/livewire/products/product-index.blade.php --}}
<div class="flex flex-col h-full">

    {{-- Cabecera + filtros --}}
    <div class="mb-4">
        <div class="flex items-center justify-between mb-4">
            <flux:heading size="xl">{{ __('Productos') }}</flux:heading>
            @can('manage-account-users')
                <flux:button icon="plus-circle" variant="primary" :href="route('products.register')" wire:navigate>
                    {{ __('Registrar') }}
                </flux:button>
            @endcan
        </div>

        {{-- Chips de estado rápido --}}
        <div class="flex gap-2 flex-wrap mb-3">
            @foreach(['' => 'Todos', 'EN STOCK' => 'Stock', 'ALQUILADO' => 'Alquilados', 'LAVANDERIA' => 'Lavandería', 'MANTENIMIENTO' => 'Mant.', 'BLOQUEADO' => 'Bloqueados', 'PEDIDO' => 'Pedidos'] as $val => $label)
                    <button wire:click="$set('status', '{{ $val }}')" class="px-3 py-1 rounded-full text-sm border transition-colors
                                                {{ $status === $val
                ? 'bg-zinc-800 text-white border-zinc-800 dark:bg-white dark:text-zinc-900'
                : 'border-zinc-300 dark:border-zinc-600 hover:border-zinc-500' }}">
                        {{ $label }}
                        @if(isset($statusCounts[$val]))
                            <span class="ml-1 opacity-60 text-xs">{{ $statusCounts[$val] }}</span>
                        @endif
                    </button>
            @endforeach
        </div>

        {{-- Filtros secundarios --}}
        <div class="flex gap-2 flex-wrap">
            <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
                placeholder="Código, nombre, marca, color..." class="w-52" clearable />
            <flux:select wire:model.live="gender" class="w-36">
                <flux:select.option value="">Género</flux:select.option>
                <flux:select.option value="MASCULINO">Masculino</flux:select.option>
                <flux:select.option value="FEMENINO">Femenino</flux:select.option>
                <flux:select.option value="UNISEX">Unisex</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="productType" class="w-44">
                <flux:select.option value="">Tipo</flux:select.option>
                <flux:select.option value="VENTA Y ALQUILER">Venta y alquiler</flux:select.option>
                <flux:select.option value="VENTA">Solo venta</flux:select.option>
                <flux:select.option value="GENERAL">General</flux:select.option>
            </flux:select>
            <flux:select wire:model.live="sortBy" class="w-44">
                <flux:select.option value="created_at">Más recientes</flux:select.option>
                <flux:select.option value="rent_count">Más alquilados</flux:select.option>
                <flux:select.option value="sale_count">Más vendidos</flux:select.option>
                <flux:select.option value="name">Nombre A-Z</flux:select.option>
            </flux:select>
        </div>
    </div>

    {{-- Grid de productos --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3">
        @forelse($products as $product)
            @php
                $firstMedia = $product->media->first();
                $statusColors = [
                    'EN STOCK' => 'bg-green-500',
                    'ALQUILADO' => 'bg-amber-500',
                    'LAVANDERIA' => 'bg-blue-500',
                    'MANTENIMIENTO' => 'bg-gray-500',
                    'BLOQUEADO' => 'bg-red-500',
                    'PEDIDO' => 'bg-purple-500',
                ];
                $dot = $statusColors[$product->status] ?? 'bg-zinc-400';
            @endphp

            <div wire:key="product-{{ $product->id }}" class="relative group bg-white dark:bg-zinc-900 border border-zinc-200
                                   dark:border-zinc-700 rounded-xl overflow-hidden cursor-pointer
                                   hover:border-zinc-400 transition-all hover:shadow-md active:scale-95"
                wire:click="openDetail({{ $product->id }})">

                {{-- Imagen --}}
                <div class="aspect-square bg-zinc-100 dark:bg-zinc-800 relative overflow-hidden">
                    @if($firstMedia)
                        <img src="{{ $firstMedia->thumb_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover"
                            loading="lazy" />
                    @else
                        <div class="w-full h-full flex items-center justify-center">
                            <flux:icon name="photo" class="size-10 text-zinc-300" />
                        </div>
                    @endif

                    {{-- Dot de estado --}}
                    <span class="absolute top-2 right-2 size-3 rounded-full {{ $dot }} ring-2 ring-white"></span>

                    {{-- Tipo badge --}}
                    @if($product->product_type === 'VENTA Y ALQUILER')
                        <span class="absolute top-2 left-2 text-xs bg-black/60 text-white
                                                             px-1.5 py-0.5 rounded-full">V+A</span>
                    @elseif($product->product_type === 'VENTA')
                        <span class="absolute top-2 left-2 text-xs bg-blue-600/80 text-white
                                                             px-1.5 py-0.5 rounded-full">Venta</span>
                    @endif
                </div>

                {{-- Info --}}
                <div class="p-2">
                    <p class="text-xs font-mono text-zinc-400">{{ $product->public_code }}</p>
                    <p class="text-sm font-medium truncate leading-tight mt-0.5">{{ $product->name }}</p>
                    <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                        {{-- Color --}}
                        @if($product->color)
                            <span class="text-xs text-zinc-500">{{ $product->color }}</span>
                        @endif
                        {{-- Talla --}}
                        @if($product->size)
                            <span class="text-xs bg-zinc-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded">
                                {{ $product->size }}
                            </span>
                        @endif
                    </div>
                    {{-- Precio venta --}}
                    @if($product->sale_price)
                        <p class="text-sm font-semibold mt-1">
                            S/ {{ number_format($product->sale_price, 2) }}
                        </p>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-span-full py-20 text-center">
                <flux:icon name="cube" class="size-12 text-zinc-300 mx-auto mb-3" />
                <p class="text-zinc-500">No se encontraron productos.</p>
            </div>
        @endforelse
    </div>

    {{-- Paginación --}}
    <div class="mt-6">{{ $products->links() }}</div>

    {{-- ═══ PANEL DETALLE (slide-over) ═══ --}}
    <flux:modal wire:model="showDetail" variant="flyout" position="right" class="w-full max-w-lg">
        @if($this->selectedProduct)
            @php $p = $this->selectedProduct; @endphp
            <div class="flex flex-col h-full">

                {{-- Header --}}
                <div class="flex items-start justify-between p-4 border-b border-zinc-200 dark:border-zinc-700">
                    <div>
                        <p class="text-xs font-mono text-zinc-400">{{ $p->public_code }}</p>
                        <flux:heading size="lg">{{ $p->name }}</flux:heading>
                        @if($p->brand)
                            <p class="text-sm text-zinc-500">{{ $p->brand }}</p>
                        @endif
                    </div>
                </div>

                {{-- Galería --}}
                <div class="p-4 border-b border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center justify-between mb-2">
                        <p class="text-sm font-medium">Galería</p>
                        <flux:button size="sm" variant="ghost" icon="camera" wire:click="openUpload({{ $p->id }})">
                            Agregar foto
                        </flux:button>
                    </div>

                    @php $allMedia = $this->selectedProductMedia; @endphp

                    @if($allMedia->isEmpty())
                        <div class="aspect-video bg-zinc-100 dark:bg-zinc-800 rounded-lg
                                                            flex items-center justify-center">
                            <flux:icon name="photo" class="size-10 text-zinc-300" />
                        </div>
                    @else
                        {{-- Imagen principal --}}
                        @php $main = $allMedia->first(); @endphp
                        <div class="aspect-video bg-zinc-100 dark:bg-zinc-800 rounded-lg
                                                            overflow-hidden mb-2">
                            @if($main->isVideo())
                                <video src="{{ $main->url }}" controls class="w-full h-full object-cover"></video>
                            @else
                                <img src="{{ $main->full_url }}" alt="{{ $p->name }}" class="w-full h-full object-cover" />
                            @endif

                            {{-- Badge pending --}}
                            @if($main->isPending())
                                <div class="absolute inset-0 bg-black/40 flex items-center
                                                                                justify-center rounded-lg">
                                    <flux:badge color="amber">Pendiente aprobación</flux:badge>
                                </div>
                            @endif
                        </div>

                        {{-- Miniaturas --}}
                        <div class="flex gap-2 overflow-x-auto pb-1">
                            @foreach($allMedia->skip(1) as $media)
                                <div class="relative flex-shrink-0 w-16 h-16 rounded-lg
                                                                                overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                                    @if($media->isVideo())
                                        <video src="{{ $media->url }}" class="w-full h-full object-cover"></video>
                                        <flux:icon name="play-circle" class="absolute inset-0 m-auto size-6 text-white" />
                                    @else
                                        <img src="{{ $media->url }}" class="w-full h-full object-cover" />
                                    @endif

                                    {{-- Pending overlay + aprobar/rechazar --}}
                                    @if($media->isPending())
                                        <div class="absolute inset-0 bg-black/50 flex flex-col
                                                                                                    items-center justify-center gap-1">
                                            <span class="text-xs text-amber-300">⏳</span>
                                            @can('manage-account-users')
                                                <button wire:click="approveMedia({{ $media->id }})"
                                                    class="text-xs text-green-300">✓</button>
                                                <button wire:click="rejectMedia({{ $media->id }})" class="text-xs text-red-300">✕</button>
                                            @endcan
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Info del producto --}}
                <div class="p-4 flex-1 overflow-y-auto space-y-3">

                    {{-- Estado --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-500">Estado</span>
                        @php
                            $sc = [
                                'EN STOCK' => 'green',
                                'ALQUILADO' => 'amber',
                                'LAVANDERIA' => 'blue',
                                'MANTENIMIENTO' => 'gray',
                                'BLOQUEADO' => 'red',
                                'PEDIDO' => 'purple'
                            ];
                        @endphp
                        <flux:badge variant="pill" :color="$sc[$p->status] ?? 'zinc'" size="sm">
                            {{ $p->status }}
                        </flux:badge>
                    </div>

                    {{-- Tipo --}}
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-zinc-500">Tipo</span>
                        <span class="text-sm font-medium">{{ $p->product_type }}</span>
                    </div>

                    {{-- Color --}}
                    @if($p->color)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-500">Color</span>
                            <div class="flex items-center gap-2">
                                <span class="text-sm">{{ $p->color }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Talla --}}
                    @if($p->size)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-500">Talla</span>
                            <span class="text-sm font-medium">{{ $p->size }}</span>
                        </div>
                    @endif

                    {{-- Género --}}
                    @if($p->gender)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-500">Género</span>
                            <span class="text-sm">{{ $p->gender }}</span>
                        </div>
                    @endif

                    {{-- Ubicación --}}
                    @if($p->location_name)
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-zinc-500">Ubicación</span>
                            <span class="text-sm font-medium">{{ $p->location_name }}</span>
                        </div>
                    @endif

                    {{-- Precios --}}
                    @if($p->sale_price || $p->rent_price)
                        <div class="border-t border-zinc-100 dark:border-zinc-800 pt-3 space-y-2">
                            @if($p->sale_price)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-zinc-500">Precio venta</span>
                                    <span class="text-sm font-semibold">
                                        S/ {{ number_format($p->sale_price, 2) }}
                                    </span>
                                </div>
                            @endif
                            @if($p->rent_price)
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-zinc-500">Precio alquiler</span>
                                    <span class="text-sm font-semibold">
                                        S/ {{ number_format($p->rent_price, 2) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Contadores --}}
                    <div class="border-t border-zinc-100 dark:border-zinc-800 pt-3
                                            grid grid-cols-2 gap-3">
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold">{{ $p->rent_count }}</p>
                            <p class="text-xs text-zinc-500">Alquileres</p>
                        </div>
                        <div class="bg-zinc-50 dark:bg-zinc-800 rounded-lg p-3 text-center">
                            <p class="text-2xl font-bold">{{ $p->sale_count }}</p>
                            <p class="text-xs text-zinc-500">Ventas</p>
                        </div>
                    </div>

                    {{-- Acciones admin --}}
                    @can('manage-account-users')
                        <div class="border-t border-zinc-100 dark:border-zinc-800 pt-3 space-y-2">
                            <flux:button variant="ghost" class="w-full" icon="pencil-square" href="#" wire:navigate>
                                {{ __('Editar producto') }}
                            </flux:button>
                        </div>
                    @endcan
                </div>

            </div>
        @endif
    </flux:modal>

    {{-- ═══ MODAL SUBIR FOTO ═══ --}}
    {{-- Modal upload — reemplazar el x-data="mediaUploader" por esto --}}
    <flux:modal wire:model="showUploadModal" name="upload-modal" class="max-w-md">
        <div class="space-y-4 p-1">
            <flux:heading size="lg">Agregar imagen de referencia</flux:heading>

            @if(!Gate::allows('manage-account-users'))
                <flux:callout variant="info" icon="information-circle">
                    Tu imagen quedará pendiente de aprobación por el administrador.
                </flux:callout>
            @endif

            <flux:field>
                <flux:label>Seleccionar imagen</flux:label>
                <input type="file" wire:model="uploadPhoto" accept="image/*" class="block w-full text-sm text-zinc-500
                       file:mr-4 file:py-2 file:px-4
                       file:rounded-lg file:border-0
                       file:text-sm file:font-medium
                       file:bg-zinc-100 file:text-zinc-700
                       hover:file:bg-zinc-200 dark:file:bg-zinc-800
                       dark:file:text-zinc-300" />
                <flux:error name="uploadPhoto" />
            </flux:field>

            {{-- Preview mientras sube --}}
            <div wire:loading wire:target="uploadPhoto" class="text-sm text-zinc-500 flex items-center gap-2">
                <flux:icon name="arrow-path" class="size-4 animate-spin" />
                Procesando...
            </div>

            {{-- Preview de la imagen --}}
            @if($uploadPhoto)
                <div class="aspect-video rounded-lg overflow-hidden bg-zinc-100">
                    <img src="{{ $uploadPhoto->temporaryUrl() }}" class="w-full h-full object-cover" />
                </div>
            @endif

            <div class="flex gap-3 justify-end pt-2">
                <flux:button variant="ghost" wire:click="$set('showUploadModal', false)">
                    Cancelar
                </flux:button>
                <flux:button variant="primary" wire:click="saveUploadedPhoto" wire:loading.attr="disabled"
                    :disabled="! $uploadPhoto">
                    <span wire:loading.remove wire:target="saveUploadedPhoto">Guardar</span>
                    <span wire:loading wire:target="saveUploadedPhoto">Guardando...</span>
                </flux:button>
            </div>
        </div>
    </flux:modal>

</div>

@script
<script>
    Alpine.data('mediaUploader', (productId) => ({
        preview: null,
        uploading: false,
        uploadedUrl: null,
        uploadError: null,
        dragging: false,

        init() { },

        handleDrop(e) {
            this.dragging = false;
            const file = e.dataTransfer.files[0];
            if (file) this.handleFile(file);
        },

        async handleFile(file) {
            if (!file) return;

            // Preview local
            if (file.type.startsWith('image/')) {
                this.preview = URL.createObjectURL(file);
            }

            this.uploading = true;
            this.uploadError = null;
            this.uploadedUrl = null;

            const form = new FormData();
            form.append('file', file);
            form.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            try {
                const res = await fetch('/media/upload', { method: 'POST', body: form });
                const data = await res.json();

                if (!res.ok) throw new Error(data.message ?? 'Error al subir.');

                this.uploadedUrl = data.url;

                // Refrescar el componente Livewire para ver la nueva imagen
                $wire.$refresh();

            } catch (err) {
                this.uploadError = err.message;
            } finally {
                this.uploading = false;
            }
        },
    }));
</script>
@endscript