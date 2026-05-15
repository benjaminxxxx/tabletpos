<?php

namespace App\Livewire\Pos;

use App\Models\Account;
use App\Models\Product;
use App\Models\Sale;
use App\Models\Movement;
use Livewire\Component;
use Livewire\Attributes\Validate;

class SellProduct extends Component
{
    public Account $account;

    #[Validate('required|string')]
    public string $productSearch = '';

    public ?Product $selectedProduct = null;
    public array $cartItems = [];
    public bool $showConfirmation = false;
    public string $successMessage = '';
    public string $errorMessage = '';

    public function mount(Account $account)
    {
        $this->account = $account;
    }

    public function searchProducts()
    {
        if (strlen($this->productSearch) < 2) {
            return [];
        }

        return Product::where('account_id', $this->account->id)
            ->where('status', 'available')
            ->where('can_sell', true)
            ->where(function ($query) {
                $query->where('public_code', 'like', '%' . $this->productSearch . '%')
                    ->orWhere('name', 'like', '%' . $this->productSearch . '%')
                    ->orWhere('brand', 'like', '%' . $this->productSearch . '%');
            })
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->productSearch = '';
    }

    public function addToCart()
    {
        if (!$this->selectedProduct) {
            $this->errorMessage = 'Please select a product first';
            return;
        }

        $this->cartItems[] = [
            'product_id' => $this->selectedProduct->id,
            'public_code' => $this->selectedProduct->public_code,
            'name' => $this->selectedProduct->name,
            'description' => $this->selectedProduct->description,
            'amount' => 0,
        ];

        $this->selectedProduct = null;
        $this->dispatch('item-added-to-cart');
    }

    public function removeFromCart($index)
    {
        unset($this->cartItems[$index]);
        $this->cartItems = array_values($this->cartItems);
    }

    public function updateAmount($index, $amount)
    {
        if (isset($this->cartItems[$index])) {
            $this->cartItems[$index]['amount'] = max(0, (float) $amount);
        }
    }

    public function confirmSale()
    {
        if (empty($this->cartItems)) {
            $this->errorMessage = 'Cart is empty';
            return;
        }

        foreach ($this->cartItems as $item) {
            if ($item['amount'] <= 0) {
                $this->errorMessage = 'All items must have a valid amount';
                return;
            }
        }

        $this->showConfirmation = true;
    }

    public function completeSale()
    {
        try {
            $totalAmount = 0;

            foreach ($this->cartItems as $item) {
                $product = Product::find($item['product_id']);

                $sale = Sale::create([
                    'account_id' => $this->account->id,
                    'user_id' => auth()->id(),
                    'product_id' => $product->id,
                    'public_code_snapshot' => $product->public_code,
                    'description_snapshot' => $product->description,
                    'amount' => $item['amount'],
                    'status' => 'completed',
                ]);

                // Update product status
                $product->update(['status' => 'blocked']);
                $product->increment('sale_count');

                // Record movement
                Movement::create([
                    'account_id' => $this->account->id,
                    'user_id' => auth()->id(),
                    'type' => 'sale',
                    'reference_id' => $sale->id,
                    'reference_type' => 'Sale',
                    'amount' => $item['amount'],
                    'direction' => 'in',
                    'notes' => 'Sale: ' . $item['public_code'],
                ]);

                $totalAmount += $item['amount'];
            }

            $this->successMessage = "Sale completed! Total: \$" . number_format($totalAmount, 2);
            $this->cartItems = [];
            $this->showConfirmation = false;
        } catch (\Exception $e) {
            $this->errorMessage = 'Error completing sale: ' . $e->getMessage();
        }
    }

    public function clearCart()
    {
        $this->cartItems = [];
        $this->selectedProduct = null;
        $this->productSearch = '';
        $this->errorMessage = '';
    }

    public function render()
    {
        return view('livewire.pos.sell-product', [
            'searchResults' => $this->searchProducts(),
            'total' => collect($this->cartItems)->sum('amount'),
        ]);
    }
}
