<?php

namespace App\Livewire\Inventory;

use App\Models\Account;
use App\Models\Product;
use App\Models\Location;
use Livewire\Component;
use Livewire\Attributes\Validate;

class ProductCatalog extends Component
{
    public Account $account;

    #[Validate('nullable|string')]
    public string $categoryFilter = '';

    #[Validate('nullable|string')]
    public string $statusFilter = '';

    #[Validate('nullable|numeric')]
    public ?int $locationFilter = null;

    public ?Product $selectedProduct = null;

    public function mount(Account $account)
    {
        $this->account = $account;
    }

    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::find($productId);
    }

    public function getProducts()
    {
        $query = Product::where('account_id', $this->account->id);

        if ($this->categoryFilter) {
            $query->where('category_prefix', $this->categoryFilter);
        }

        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }

        if ($this->locationFilter) {
            $query->where('location_id', $this->locationFilter);
        }

        return $query->with('location', 'media')
            ->paginate(12);
    }

    public function getCategories()
    {
        return Product::where('account_id', $this->account->id)
            ->distinct()
            ->pluck('category_prefix')
            ->sort();
    }

    public function getStatuses()
    {
        return ['available', 'rented', 'blocked', 'laundry', 'maintenance'];
    }

    public function getLocations()
    {
        return Location::where('account_id', $this->account->id)
            ->get();
    }

    public function clearFilters()
    {
        $this->categoryFilter = '';
        $this->statusFilter = '';
        $this->locationFilter = null;
    }

    public function render()
    {
        return view('livewire.inventory.product-catalog', [
            'products' => $this->getProducts(),
            'categories' => $this->getCategories(),
            'statuses' => $this->getStatuses(),
            'locations' => $this->getLocations(),
        ]);
    }
}
