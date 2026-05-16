<?php

namespace App\Livewire\Cart;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\RentalDetail;
use App\Models\Customer;
use App\Concerns\HasActiveAccount;
use Livewire\Component;
use Livewire\Attributes\Validate;

class CartManager extends Component
{
    use HasActiveAccount;

    // Arrays para items del carrito
    public array $saleItems = [];
    public array $rentalItems = [];

    // Totales (calculados con Alpine)
    public float $totalSales = 0;
    public float $totalRentals = 0;
    public float $totalGuarantees = 0;
    public float $grandTotal = 0;

    // Contadores
    public int $saleItemCount = 0;
    public int $rentalItemCount = 0;

    // Modal estados
    public bool $showCart = false;
    public bool $showConfirmation = false;

    // Estado de edición
    public ?string $editingKey = null;
    public string $editingType = ''; // 'sale' | 'rental'

    public function mount(): void
    {
        $this->bootActiveAccount();
        $this->loadCartFromSession();
    }

    public function loadCartFromSession(): void
    {
        $cartData = session('cart', ['sales' => [], 'rentals' => []]);
        $this->saleItems = $cartData['sales'] ?? [];
        $this->rentalItems = $cartData['rentals'] ?? [];
        $this->calculateTotals();
    }

    public function saveCartToSession(): void
    {
        session(['cart' => [
            'sales' => $this->saleItems,
            'rentals' => $this->rentalItems,
        ]]);
    }

    public function addSaleItem(int $productId, int $quantity = 1): void
    {
        $product = Product::findOrFail($productId);

        // Validar stock disponible
        if ($product->quantity_available < $quantity) {
            $this->dispatch('notify', type: 'error', message: 'Stock insuficiente');
            return;
        }

        // Buscar si el producto ya existe en el carrito
        $existingKey = collect($this->saleItems)->search(
            fn($item) => $item['product_id'] === $productId
        );

        if ($existingKey !== false) {
            // Incrementar cantidad
            $newQuantity = $this->saleItems[$existingKey]['quantity'] + $quantity;
            if ($product->quantity_available < $newQuantity) {
                $this->dispatch('notify', type: 'error', message: 'Stock insuficiente para esa cantidad');
                return;
            }
            $this->saleItems[$existingKey]['quantity'] = $newQuantity;
            $this->saleItems[$existingKey]['subtotal'] = 
                $newQuantity * $this->saleItems[$existingKey]['unit_price'];
        } else {
            // Agregar nuevo item
            $this->saleItems[uniqid('sale_')] = [
                'product_id' => $productId,
                'product_name' => $product->name,
                'quantity' => $quantity,
                'unit_price' => $product->sale_price ?? 0,
                'subtotal' => $quantity * ($product->sale_price ?? 0),
            ];
        }

        $this->calculateTotals();
        $this->saveCartToSession();
        $this->dispatch('notify', message: 'Producto agregado al carrito');
    }

    public function addRentalItem(
        int $productId,
        int $quantity,
        float $unitPrice,
        int $customerId,
        array $rentalData
    ): void {
        $product = Product::findOrFail($productId);
        $customer = Customer::findOrFail($customerId);

        // Validar stock disponible
        if ($product->quantity_available < $quantity) {
            $this->dispatch('notify', type: 'error', message: 'Stock insuficiente para alquiler');
            return;
        }

        // Validar datos de alquiler
        if (empty($rentalData['dni_number']) || empty($rentalData['dni_photo_url'])) {
            $this->dispatch('notify', type: 'error', message: 'DNI y foto de DNI son requeridos');
            return;
        }

        if (!isset($rentalData['rental_start_date']) || !isset($rentalData['rental_return_date'])) {
            $this->dispatch('notify', type: 'error', message: 'Fechas de alquiler requeridas');
            return;
        }

        // Agregar item
        $this->rentalItems[uniqid('rental_')] = [
            'product_id' => $productId,
            'product_name' => $product->name,
            'customer_id' => $customerId,
            'customer_name' => $customer->name,
            'quantity' => $quantity,
            'unit_rental_price' => $unitPrice,
            'subtotal' => $quantity * $unitPrice,
            'guarantee_amount' => $rentalData['guarantee_amount'] ?? 0,
            'dni_number' => $rentalData['dni_number'],
            'dni_photo_url' => $rentalData['dni_photo_url'],
            'additional_photo_url' => $rentalData['additional_photo_url'] ?? null,
            'rental_start_date' => $rentalData['rental_start_date'],
            'rental_return_date' => $rentalData['rental_return_date'],
            'observations' => $rentalData['observations'] ?? '',
        ];

        $this->calculateTotals();
        $this->saveCartToSession();
        $this->dispatch('notify', message: 'Alquiler agregado al carrito');
    }

    public function updateSaleItemPrice(string $key, float $newPrice): void
    {
        if (isset($this->saleItems[$key])) {
            $this->saleItems[$key]['unit_price'] = $newPrice;
            $this->saleItems[$key]['subtotal'] = 
                $this->saleItems[$key]['quantity'] * $newPrice;
            $this->calculateTotals();
            $this->saveCartToSession();
        }
    }

    public function updateSaleItemQuantity(string $key, int $newQuantity): void
    {
        if (isset($this->saleItems[$key])) {
            $product = Product::find($this->saleItems[$key]['product_id']);
            if ($product->quantity_available < $newQuantity) {
                $this->dispatch('notify', type: 'error', message: 'Stock insuficiente');
                return;
            }
            $this->saleItems[$key]['quantity'] = $newQuantity;
            $this->saleItems[$key]['subtotal'] = 
                $newQuantity * $this->saleItems[$key]['unit_price'];
            $this->calculateTotals();
            $this->saveCartToSession();
        }
    }

    public function updateRentalItemPrice(string $key, float $newPrice): void
    {
        if (isset($this->rentalItems[$key])) {
            $this->rentalItems[$key]['unit_rental_price'] = $newPrice;
            $this->rentalItems[$key]['subtotal'] = 
                $this->rentalItems[$key]['quantity'] * $newPrice;
            $this->calculateTotals();
            $this->saveCartToSession();
        }
    }

    public function updateRentalItemGuarantee(string $key, float $newGuarantee): void
    {
        if (isset($this->rentalItems[$key])) {
            $this->rentalItems[$key]['guarantee_amount'] = $newGuarantee;
            $this->calculateTotals();
            $this->saveCartToSession();
        }
    }

    public function changeProductInSale(string $key, int $newProductId): void
    {
        if (isset($this->saleItems[$key])) {
            $product = Product::findOrFail($newProductId);
            $this->saleItems[$key]['product_id'] = $newProductId;
            $this->saleItems[$key]['product_name'] = $product->name;
            $this->saleItems[$key]['unit_price'] = $product->sale_price ?? 0;
            $this->saleItems[$key]['subtotal'] = 
                $this->saleItems[$key]['quantity'] * ($product->sale_price ?? 0);
            $this->calculateTotals();
            $this->saveCartToSession();
        }
    }

    public function removeSaleItem(string $key): void
    {
        unset($this->saleItems[$key]);
        $this->calculateTotals();
        $this->saveCartToSession();
    }

    public function removeRentalItem(string $key): void
    {
        unset($this->rentalItems[$key]);
        $this->calculateTotals();
        $this->saveCartToSession();
    }

    public function calculateTotals(): void
    {
        $this->totalSales = collect($this->saleItems)->sum('subtotal');
        $this->totalRentals = collect($this->rentalItems)->sum('subtotal');
        $this->totalGuarantees = collect($this->rentalItems)->sum('guarantee_amount');
        $this->grandTotal = $this->totalSales + $this->totalRentals + $this->totalGuarantees;
        $this->saleItemCount = count($this->saleItems);
        $this->rentalItemCount = count($this->rentalItems);
    }

    public function processCart(): void
    {
        // Validar que hay items
        if (empty($this->saleItems) && empty($this->rentalItems)) {
            $this->dispatch('notify', type: 'error', message: 'El carrito está vacío');
            return;
        }

        try {
            // Generar número de transacción
            $lastSale = Sale::where('account_id', $this->activeAccount->id)
                ->orderBy('id', 'desc')
                ->first();
            $lastNumber = $lastSale ? 
                (int) str_replace('#', '', $lastSale->transaction_number) : 0;
            $transactionNumber = '#' . ($lastNumber + 1);

            // Crear Sale
            $sale = Sale::create([
                'account_id' => $this->activeAccount->id,
                'user_id' => auth()->id(),
                'transaction_number' => $transactionNumber,
                'transaction_date' => now(),
                'total_amount' => $this->grandTotal,
                'status' => 'completada',
            ]);

            // Crear SaleDetails
            foreach ($this->saleItems as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => $item['subtotal'],
                    'product_status_after' => 'vendido',
                ]);

                // Actualizar stock del producto
                $product->reduceAvailableQuantity($item['quantity']);
                $product->increaseSoldTotal($item['quantity']);
                $product->update(['status' => 'vendido']);
            }

            // Crear RentalDetails
            foreach ($this->rentalItems as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                RentalDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'customer_id' => $item['customer_id'],
                    'quantity' => $item['quantity'],
                    'unit_rental_price' => $item['unit_rental_price'],
                    'subtotal' => $item['subtotal'],
                    'guarantee_amount' => $item['guarantee_amount'],
                    'dni_number' => $item['dni_number'],
                    'dni_photo_url' => $item['dni_photo_url'],
                    'additional_photo_url' => $item['additional_photo_url'],
                    'rental_start_date' => $item['rental_start_date'],
                    'rental_return_date' => $item['rental_return_date'],
                    'observations' => $item['observations'],
                    'product_status_after' => 'alquilado',
                    'status' => 'activo',
                ]);

                // Actualizar stock del producto
                $product->reduceAvailableQuantity($item['quantity']);
                $product->increaseRentedOut($item['quantity']);
                $product->update(['status' => 'alquilado']);
            }

            // Limpiar carrito
            $this->saleItems = [];
            $this->rentalItems = [];
            session()->forget('cart');
            $this->calculateTotals();

            // Notificar éxito
            $this->dispatch('notify', message: "Transacción {$transactionNumber} completada");
            $this->dispatch('cart-processed', saleId: $sale->id);
            
        } catch (\Exception $e) {
            \Log::error('Error procesando carrito: ' . $e->getMessage());
            $this->dispatch('notify', type: 'error', message: 'Error al procesar el carrito');
        }
    }

    public function render()
    {
        return view('livewire.cart.cart-manager');
    }
}
