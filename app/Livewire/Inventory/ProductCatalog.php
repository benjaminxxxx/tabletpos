<?php

namespace App\Livewire\Inventory;

use App\Models\Account;
use App\Models\Product;
use App\Models\Location;
use App\Models\Customer;
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

    // States for action modals
    public bool $showSellModal = false;
    public bool $showRentalModal = false;

    // Sell form data
    #[Validate('numeric|min:0.01')]
    public float $sellPrice = 0;
    #[Validate('numeric|min:1')]
    public int $sellQuantity = 1;

    // Rental form data
    #[Validate('required|numeric')]
    public ?int $customerId = null;
    #[Validate('required|string')]
    public string $dniNumber = '';
    #[Validate('required|string')]
    public string $dniPhotoUrl = '';
    public ?string $additionalPhotoUrl = null;
    #[Validate('numeric|min:0')]
    public float $guaranteeAmount = 0;
    #[Validate('required|date')]
    public ?string $rentalStartDate = null;
    #[Validate('required|date')]
    public ?string $rentalReturnDate = null;
    #[Validate('nullable|string')]
    public string $observations = '';
    #[Validate('numeric|min:0.01')]
    public float $rentalPrice = 0;
    #[Validate('numeric|min:1')]
    public int $rentalQuantity = 1;

    public function mount(Account $account)
    {
        $this->account = $account;
    }

    public function selectProduct($productId)
    {
        $this->selectedProduct = Product::find($productId);
        $this->resetFormData();
    }

    public function resetFormData(): void
    {
        $this->sellPrice = $this->selectedProduct?->sale_price ?? 0;
        $this->sellQuantity = 1;
        $this->customerId = null;
        $this->dniNumber = '';
        $this->dniPhotoUrl = '';
        $this->additionalPhotoUrl = null;
        $this->guaranteeAmount = 0;
        $this->rentalStartDate = null;
        $this->rentalReturnDate = null;
        $this->observations = '';
        $this->rentalPrice = $this->selectedProduct?->rental_price ?? 0;
        $this->rentalQuantity = 1;
    }

    public function openSellModal(): void
    {
        if (!$this->selectedProduct) return;
        $this->showSellModal = true;
    }

    public function openRentalModal(): void
    {
        if (!$this->selectedProduct) return;
        $this->showRentalModal = true;
    }

    public function addToCartSell(): void
    {
        $this->validate([
            'sellPrice' => 'required|numeric|min:0.01',
            'sellQuantity' => 'required|numeric|min:1',
        ]);

        if (!$this->selectedProduct) {
            $this->dispatch('notify', type: 'error', message: 'Producto no seleccionado');
            return;
        }

        // Call CartManager addSaleItem method
        $this->dispatch('add-sale-item', 
            productId: $this->selectedProduct->id,
            quantity: $this->sellQuantity,
            unitPrice: $this->sellPrice
        );

        $this->showSellModal = false;
        $this->selectedProduct = null;
    }

    public function addToCartRental(): void
    {
        $this->validate([
            'customerId' => 'required|numeric',
            'dniNumber' => 'required|string',
            'dniPhotoUrl' => 'required|string',
            'rentalStartDate' => 'required|date',
            'rentalReturnDate' => 'required|date|after:rentalStartDate',
            'rentalPrice' => 'required|numeric|min:0.01',
            'rentalQuantity' => 'required|numeric|min:1',
        ]);

        if (!$this->selectedProduct) {
            $this->dispatch('notify', type: 'error', message: 'Producto no seleccionado');
            return;
        }

        $rentalData = [
            'customer_id' => $this->customerId,
            'dni_number' => $this->dniNumber,
            'dni_photo_url' => $this->dniPhotoUrl,
            'additional_photo_url' => $this->additionalPhotoUrl,
            'guarantee_amount' => $this->guaranteeAmount,
            'rental_start_date' => $this->rentalStartDate,
            'rental_return_date' => $this->rentalReturnDate,
            'observations' => $this->observations,
        ];

        // Call CartManager addRentalItem method
        $this->dispatch('add-rental-item',
            productId: $this->selectedProduct->id,
            quantity: $this->rentalQuantity,
            unitPrice: $this->rentalPrice,
            rentalData: $rentalData
        );

        $this->showRentalModal = false;
        $this->selectedProduct = null;
    }

    public function getCustomers()
    {
        return Customer::where('account_id', $this->account->id)
            ->orderBy('name')
            ->get();
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
