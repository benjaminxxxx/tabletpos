<?php
// app/Livewire/Products/ProductRegister.php

namespace App\Livewire\Products;

use App\Concerns\HasActiveAccount;
use App\Models\Category;
use App\Models\Product;
use Flux\Flux;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;

class ProductRegister extends Component
{
    use HasActiveAccount;

    public string $purchaseDate = '';
    public array $rows = [];
    public array $errors = [];
    public array $saved = [];
    public bool $saving = false;
    public array $categoriesList = [];

    public function mount(): void
    {
        $this->bootActiveAccount();
        $this->authorizeManage();
        $this->purchaseDate = now()->toDateString();
        $this->categoriesList = Category::whereNotNull('parent_id') // Solo subcategorías
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($cat) => ['id' => $cat->id, 'label' => $cat->name])->toArray();
        $this->loadProducts(false);

    }

    public function loadProducts($dispatched = true)
    {
        $this->rows = Product::where('account_id', $this->activeAccount->id)
            ->where('purchase_date', $this->purchaseDate)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'public_code' => $product->public_code,
                    'name' => $product->name,
                    'brand' => $product->brand,
                    'origin' => $product->origin,
                    'color' => $product->color,
                    'size' => $product->size,
                    'material' => $product->material,
                    'location_name' => $product->location_name,
                    'purchase_price' => $product->purchase_price,
                    'purchase_date' => $product->purchase_date ? $product->purchase_date->toDateString() : null,
                    'category_id' => $product->category_id,
                    'gender' => $product->gender
                ];
            })->toArray();
        if ($dispatched) {
            $this->dispatch('products-loaded', rows: $this->rows);
        }
    }
    /**
     * Llamado desde JS con las filas del Handsontable.
     * Livewire no puede leer el DOM directo — JS empuja los datos.
     */
    public function saveRows(array $rows): void
    {
        try {
            $this->bootActiveAccount();
            $this->authorizeManage();
            $savedCount = 0;

            foreach ($rows as $index => $row) {
                // Ignorar filas completamente vacías
                if (empty(array_filter($row)))
                    continue;


                $productType = $row['product_type'] ?? 'sellable';

                $data = [
                    'account_id' => $this->activeAccount->id,
                    'category_id' => $row['category_id'] ?? null,
                    'name' => trim($row['name'] ?? ''),
                    'brand' => trim($row['brand'] ?? ''),
                    'origin' => trim($row['origin'] ?? ''),
                    'color' => trim($row['color'] ?? ''),
                    'size' => trim($row['size'] ?? ''),
                    'material' => trim($row['material'] ?? ''),
                    'location_name' => trim($row['location_name'] ?? ''),
                    'purchase_price' => is_numeric($row['purchase_price'] ?? null)
                        ? (float) $row['purchase_price'] : null,
                    'purchase_date' => $this->purchaseDate,
                    'product_type' => $productType,
                    'status' => 'available',
                    'can_sell' => in_array($productType, ['sellable', 'stock_only']),
                    'can_rent' => $productType === 'rentable',
                    'stock' => $productType === 'stock_only'
                        ? (int) ($row['stock'] ?? 1) : 1,
                    'updated_by' => auth()->id(),
                ];

                if (!empty($row['id'])) {
                    // Actualización
                    $product = Product::findOrFail($row['id']);
                    // Si cambió categoría o género, recalculamos código
                    if ($product->category_id != $row['category_id'] || $product->gender != $row['gender']) {
                        $data['public_code'] = Product::generateSmartCode($this->activeAccount->id, $row['category_id'], $row['gender']);
                    }
                    $product->update($data);

                    $this->saved[] = $product->public_code;
                } else {
                    // Nuevo registro
                    $data['created_by'] = auth()->id();
                    $data['public_code'] = Product::generateSmartCode($this->activeAccount->id, $row['category_id'], $row['gender']);
                    Product::create($data);
                }

                $savedCount++;
            }
            Flux::toast(variant: 'success', text: __('Registros guardados: :count', ['count' => $savedCount]));
            $this->loadProducts();
        } catch (\Throwable $th) {
            Flux::toast(variant: 'error', text: __($th->getMessage()));
        }
    }

    /**
     * Genera el siguiente código para un prefijo dado.
     * Llamado desde JS al cambiar el prefijo en una fila.
     */
    public function generateCode(string $prefix): string
    {
        $this->bootActiveAccount();
        return Product::nextCodeForPrefix(
            strtoupper($prefix),
            $this->activeAccount->id
        );
    }


    private function authorizeManage(): void
    {
        abort_unless(Gate::allows('manage-account-users'), 403);
    }
    public function updatedPurchaseDate()
    {
        $this->loadProducts();
    }
    public function render()
    {
        // Compras recientes = productos agrupados por fecha de compra
        $recentDates = Product::where('account_id', $this->activeAccount->id)
            ->whereDate('purchase_date', $this->purchaseDate)
            ->orderByDesc('created_at')
            ->get();

        return view('livewire.products.product-register', [
            'recentProducts' => $recentDates,
        ]);
    }
}