<?php
// app/Livewire/Products/ProductRegister.php

namespace App\Livewire\Products;

use App\Concerns\HasActiveAccount;
use App\Models\Category;
use App\Models\Product;
use Exception;
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

        $this->categoriesList = Category::visibleFor($this->activeAccount->id)
            ->children() // Solo subcategorías — las que tienen prefix de producto
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn($cat) => ['id' => $cat->id, 'label' => $cat->name])
            ->toArray();
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
                    'gender' => $product->gender,
                    'status' => $product->status,
                    'product_type' => $product->product_type,
                    'stock' => $product->stock,

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
                // 1. Ignorar filas completamente vacías
                if (empty(array_filter($row))) {
                    continue;
                }

                // Calcular el número de fila real para mostrar en el error (basado en índice 0)
                $rowNumber = $index + 1;

                // 2. Validaciones estrictas antes de generar el código inteligente
                if (empty($row['category_id'])) {
                    throw new Exception(__("Fila :num: La categoría es obligatoria para generar el código.", ['num' => $rowNumber]));

                }

                if (empty(trim($row['name'] ?? ''))) {
                    throw new Exception(__("Fila :num: El nombre es obligatorio.", ['num' => $rowNumber]));
                }

                $productType = $row['product_type'] ?? 'VENTA Y ALQUILER';
                $gender = $row['gender'] ?? 'Unisex';

                $data = [
                    'account_id' => $this->activeAccount->id,
                    'category_id' => (int) $row['category_id'],
                    'name' => mb_strtoupper(trim($row['name'])),
                    'brand' => mb_strtoupper(trim($row['brand'] ?? '')),
                    'origin' => mb_strtoupper(trim($row['origin'] ?? '')),
                    'color' => mb_strtoupper(trim($row['color'] ?? '')),
                    'size' => mb_strtoupper(trim($row['size'] ?? '')),
                    'material' => mb_strtoupper(trim($row['material'] ?? '')),
                    'location_name' => mb_strtoupper(trim($row['location_name'] ?? '')),
                    'purchase_price' => is_numeric($row['purchase_price'] ?? null) ? (float) $row['purchase_price'] : null,
                    'purchase_date' => $this->purchaseDate,
                    'product_type' => $productType,
                    'status' => $row['status'] ?? 'EN STOCK',
                    'stock' => $productType === 'stock_only' ? (int) ($row['stock'] ?? 1) : 1,
                    'updated_by' => auth()->id(),
                    'gender' => $gender,
                ];

                // 3. Procesar Actualización o Creación
                if (!empty($row['id'])) {
                    $product = Product::findOrFail($row['id']);

                    // Si cambió de categoría o género, recalculamos su código inteligente
                    if ($product->category_id != $row['category_id'] || $product->gender != $gender) {
                        $data['public_code'] = Product::generateSmartCode(
                            $this->activeAccount->id,
                            (int) $row['category_id'],
                            $gender
                        );
                    }

                    $product->update($data);
                    $this->saved[] = $product->public_code;
                } else {
                    // Registro Nuevo
                    $data['created_by'] = auth()->id();
                    $data['public_code'] = Product::generateSmartCode(
                        $this->activeAccount->id,
                        (int) $row['category_id'],
                        $gender
                    );

                    Product::create($data);
                }

                $savedCount++;
            }

            // 4. Mostrar alertas de feedback al usuario
            if ($savedCount > 0) {
                Flux::toast(variant: 'success', text: __('Registros procesados con éxito: :count', ['count' => $savedCount]));
            }


            $this->loadProducts();

        } catch (\Throwable $th) {
            Flux::toast(variant: 'error', text: __('Error general: :msg', ['msg' => $th->getMessage()]));
        }
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

        return view('livewire.products.product-register');
    }
}