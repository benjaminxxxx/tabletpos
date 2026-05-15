{{-- resources/views/livewire/products/product-register.blade.php --}}
<div x-data="productRegister()">
    {{-- Cabecera --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">{{ __('Registrar productos') }}</flux:heading>
            <flux:subheading>{{ $activeAccount->name }}</flux:subheading>
        </div>
    </div>

    {{-- Fecha de compra --}}
    <div class="flex items-center gap-4 mb-6">
        <flux:field class="w-52">
            <x-selector-dia label="Fecha de compra" wire:model.live="purchaseDate" />
        </flux:field>

        <div class="pt-5">
            <flux:badge variant="pill" color="zinc" size="sm" x-text="countLabel"></flux:badge>
        </div>
    </div>

    {{-- Tabla Handsontable --}}
    <div class="border border-zinc-200 dark:border-zinc-700 rounded-xl overflow-hidden mb-4" wire:ignore>
        <div id="hot-container" style="width:100%; height:420px;"></div>
    </div>

    {{-- Acciones --}}
    <div class="flex items-center gap-3 mb-6">
        <flux:button variant="primary" x-on:click="saveAll()" wire:loading.attr="disabled">
            Guardar
        </flux:button>

        <flux:button variant="ghost" x-on:click="addRow()">
            {{ __('+ Agregar fila') }}
        </flux:button>
    </div>

    



</div>

@script
<script>
    Alpine.data('productRegister', () => ({
        hot: null,
        countLabel: '0 productos hoy',
        duplicateData: {},
        rows: @js($rows),
        listaCategorias: @js($categoriesList), // Pasar la lista de productos desde el Controller

        getColumns() {
            const categoriaLabels = this.listaCategorias.map(p => p.label);
            const categoriaMap = Object.fromEntries(this.listaCategorias.map(p => [p.label, p.id]));
            const categoriaRevMap = Object.fromEntries(this.listaCategorias.map(p => [p.id, p.label]));
            const autocompleteCol = (labels, map, revMap, prop, title, width) => ({
                data: prop,
                title,
                type: 'autocomplete',
                source: labels,
                strict: false,
                allowInvalid: false,
                filter: true,
                width: width,
                renderer(instance, td, row, col, prop, value) {
                    td.classList.remove('text-gray-400', 'italic', 'text-red-500');
                    if (value === null || value === undefined || value === '') {
                        td.classList.add('text-gray-400', 'italic');
                        td.innerText = 'Buscar...';
                        return;
                    }
                    const label = revMap[value] ?? revMap[String(value)];
                    if (label) {
                        td.innerText = label;
                    } else {
                        td.classList.add('text-red-500');
                        td.innerText = '⚠️ ' + value;
                    }
                },
                validator(value, callback) {
                    if (!value || value === '') return callback(true);
                    if (revMap[value] || revMap[String(value)]) return callback(true);
                    if (typeof value === 'string' && map[value]) {
                        setTimeout(() => {
                            this.instance.setDataAtCell(this.row, this.col, map[value],
                                'validator');
                        }, 0);
                        return callback(true);
                    }
                    callback(false);
                }
            });
            const columns = [{ data: 'public_code', title: 'Código', readOnly: true },
            autocompleteCol(categoriaLabels, categoriaMap, categoriaRevMap, 'category_id',
                'Categoría', 120),
            { data: 'name', title: 'Nombre *', width: 160 },
            {
                data: 'gender',
                title: 'Género *',
                type: 'dropdown',
                strict: true,
                source: ['MASCULINO', 'FEMENINO', 'UNISEX'],
            },
            { data: 'purchase_price', title: 'Precio compra', width: 110, type: 'numeric', numericFormat: { pattern: '0,0.00' } },
            {
                data: 'product_type',
                title: 'Tipo *',
                type: 'dropdown',
                strict: true,
                source: ['VENTA Y ALQUILER', 'VENTA', 'GENERAL'],
            },
            { data: 'brand', title: 'Marca', width: 100 },
            {
                data: 'status',
                title: 'Estado *',
                type: 'dropdown',
                strict: true,
                source: ['EN STOCK', 'ALQUILADO', 'LAVANDERIA', 'MANTENIMIENTO', 'BLOQUEADO', 'PEDIDO'],
            },
            { data: 'color', title: 'Color', width: 90 },
            { data: 'size', title: 'Talla', width: 70 },
            { data: 'material', title: 'Material', width: 100 },
            { data: 'origin', title: 'Procedencia', width: 110 },
            { data: 'location_name', title: 'Ubicación', width: 110 },

            { data: 'stock', title: 'Stock', width: 70, type: 'numeric' }

            ]
            return columns;
        },

        emptyRow() {
            return {};
        },

        init() {
            const container = document.getElementById('hot-container');

            this.hot = new Handsontable(container, {
                data: this.rows,
                columns: this.getColumns(),
                //colHeaders: this.columns.map(c => c.title),
                rowHeaders: true,
                stretchH: 'all',
                contextMenu: ['row_above', 'row_below', 'remove_row', '---------', 'copy', 'paste'],
                licenseKey: 'non-commercial-and-evaluation',
                minSpareRows: 1,
                height: '100%',
                afterChange: (changes) => {
                    if (!changes) return;

                },
            });

            // Escuchar evento de Livewire al cargar productos por fecha
            Livewire.on('products-loaded', ({ rows }) => {
                this.rows = rows;
                this.hot.loadData(this.rows);
                this.updateCountLabel();
            });

        },

        addRow() {
            this.hot.alter('insert_row_below', this.hot.countRows() - 1);
        },

        saveAll() {
            const rawData = this.hot.getSourceData();
            // Filtrar filas vacías antes de enviar
            const rows = rawData.filter(r =>
                r.public_code || r.name
            );

            if (rows.length === 0) return;

            $wire.saveRows(rows);
        },

    }));
</script>
@endscript