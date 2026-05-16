<?php

namespace App\Livewire\Sales;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\RentalDetail;
use App\Models\Product;
use App\Concerns\HasActiveAccount;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Validate;

class SalesIndex extends Component
{
    use HasActiveAccount, WithPagination;

    // Filters
    #[Validate('in:all,sales,rentals')]
    public string $filterType = 'all';

    #[Validate('nullable|date')]
    public ?string $filterDate = null;

    #[Validate('nullable|date')]
    public ?string $dateFrom = null;

    #[Validate('nullable|date')]
    public ?string $dateTo = null;

    public string $sortBy = 'transaction_date';
    public string $sortDir = 'desc';

    // Permissions
    public bool $canEditAll = false;
    public bool $canEditOwn = false;

    // Modals
    public bool $showEditModal = false;
    public ?int $editingDetailId = null;
    public string $editingType = ''; // 'sale' or 'rental'

    // Edit form data
    #[Validate('numeric|min:1')]
    public int $editQuantity = 1;

    #[Validate('numeric|min:0.01')]
    public float $editPrice = 0;

    #[Validate('nullable|numeric')]
    public ?int $editProductId = null;

    public function mount(): void
    {
        $this->bootActiveAccount();
        $this->canEditAll = auth()->user()->hasRole('admin') || auth()->user()->hasRole('owner');
        $this->canEditOwn = auth()->user()->hasRole('seller');
    }

    public function render()
    {
        // Build base queries
        $saleDetails = SaleDetail::with(['sale', 'product'])
            ->whereHas('sale', function ($q) {
                $q->where('account_id', $this->activeAccount->id);
            });

        $rentalDetails = RentalDetail::with(['sale', 'product', 'customer'])
            ->whereHas('sale', function ($q) {
                $q->where('account_id', $this->activeAccount->id);
            });

        // Filter by type
        if ($this->filterType === 'sales') {
            $rentalDetails = $rentalDetails->whereRaw('1=0'); // Empty
        } elseif ($this->filterType === 'rentals') {
            $saleDetails = $saleDetails->whereRaw('1=0'); // Empty
        }

        // Filter by seller (if seller, only their own)
        if ($this->canEditOwn && !$this->canEditAll) {
            $saleDetails->whereHas('sale', function ($q) {
                $q->where('user_id', auth()->id());
            });
            $rentalDetails->whereHas('sale', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        // Filter by specific date
        if ($this->filterDate) {
            $saleDetails->whereHas('sale', function ($q) {
                $q->whereDate('transaction_date', $this->filterDate);
            });
            $rentalDetails->whereHas('sale', function ($q) {
                $q->whereDate('transaction_date', $this->filterDate);
            });
        }

        // Filter by date range
        if ($this->dateFrom && $this->dateTo) {
            $saleDetails->whereHas('sale', function ($q) {
                $q->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo]);
            });
            $rentalDetails->whereHas('sale', function ($q) {
                $q->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo]);
            });
        }

        // Get counts before combining
        $saleCount = $saleDetails->count();
        $rentalCount = $rentalDetails->count();

        // Get paginated results
        $saleDetailsData = $saleDetails->orderBy('sale_details.' . $this->sortBy, $this->sortDir)
            ->paginate(25, ['*'], 'sales_page');

        $rentalDetailsData = $rentalDetails->orderBy('rental_details.' . $this->sortBy, $this->sortDir)
            ->paginate(25, ['*'], 'rentals_page');

        // Calculate totals
        $totals = $this->calculateTotals(
            $saleDetails->get(),
            $rentalDetails->get()
        );

        // Add type to items for easy identification
        $allItems = collect();
        foreach ($saleDetailsData as $item) {
            $item->line_type = 'sale';
            $item->user_name = $item->sale->user->name;
            $item->transaction_number = $item->sale->transaction_number;
            $item->transaction_date = $item->sale->transaction_date;
            $allItems->push($item);
        }
        foreach ($rentalDetailsData as $item) {
            $item->line_type = 'rental';
            $item->user_name = $item->sale->user->name;
            $item->transaction_number = $item->sale->transaction_number;
            $item->transaction_date = $item->sale->transaction_date;
            $allItems->push($item);
        }

        // Sort combined by transaction date
        $allItems = $allItems->sortBy('transaction_date')->reverse()->values();

        return view('livewire.sales.sales-index', [
            'allItems' => $allItems,
            'totals' => $totals,
            'saleCount' => $saleCount,
            'rentalCount' => $rentalCount,
        ]);
    }

    private function calculateTotals($saleDetails, $rentalDetails): array
    {
        $totalSales = $saleDetails->sum('subtotal');
        $totalRentals = $rentalDetails->sum('subtotal');
        $totalGuarantees = $rentalDetails->sum('guarantee_amount');
        $transactionCount = Sale::where('account_id', $this->activeAccount->id)->count();

        return [
            'total_sales' => $totalSales,
            'total_rentals' => $totalRentals,
            'total_guarantees' => $totalGuarantees,
            'transaction_count' => $transactionCount,
            'grand_total' => $totalSales + $totalRentals + $totalGuarantees,
        ];
    }

    public function editSaleDetail(int $detailId): void
    {
        $detail = SaleDetail::with('sale')->findOrFail($detailId);

        // Check permissions
        if (!$this->canEditAll && $detail->sale->user_id !== auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar esta venta');
            return;
        }

        $this->editingDetailId = $detailId;
        $this->editingType = 'sale';
        $this->editQuantity = $detail->quantity;
        $this->editPrice = $detail->unit_price;
        $this->editProductId = $detail->product_id;
        $this->showEditModal = true;
    }

    public function editRentalDetail(int $detailId): void
    {
        $detail = RentalDetail::with('sale')->findOrFail($detailId);

        // Check permissions
        if (!$this->canEditAll && $detail->sale->user_id !== auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso para editar este alquiler');
            return;
        }

        $this->editingDetailId = $detailId;
        $this->editingType = 'rental';
        $this->editQuantity = $detail->quantity;
        $this->editPrice = $detail->unit_rental_price;
        $this->editProductId = $detail->product_id;
        $this->showEditModal = true;
    }

    public function saveSaleDetail(): void
    {
        $this->validate();

        $detail = SaleDetail::findOrFail($this->editingDetailId);

        // Check permissions
        if (!$this->canEditAll && $detail->sale->user_id !== auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso');
            return;
        }

        // Handle product change
        if ($detail->product_id !== $this->editProductId) {
            $detail->product_id = $this->editProductId;
        }

        // Update quantity and price
        $detail->quantity = $this->editQuantity;
        $detail->unit_price = $this->editPrice;
        $detail->calculateSubtotal();
        $detail->save();

        // Update sale total
        $sale = $detail->sale;
        $sale->calculateTotalAmount();
        $sale->save();

        $this->dispatch('notify', message: 'Venta actualizada');
        $this->showEditModal = false;
        $this->editingDetailId = null;
    }

    public function saveRentalDetail(): void
    {
        $this->validate();

        $detail = RentalDetail::findOrFail($this->editingDetailId);

        // Check permissions
        if (!$this->canEditAll && $detail->sale->user_id !== auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso');
            return;
        }

        // Update quantity and price
        $detail->quantity = $this->editQuantity;
        $detail->unit_rental_price = $this->editPrice;
        $detail->calculateSubtotal();
        $detail->save();

        // Update sale total
        $sale = $detail->sale;
        $sale->calculateTotalAmount();
        $sale->save();

        $this->dispatch('notify', message: 'Alquiler actualizado');
        $this->showEditModal = false;
        $this->editingDetailId = null;
    }

    public function deleteSaleDetail(int $detailId): void
    {
        $detail = SaleDetail::with('sale')->findOrFail($detailId);

        // Check permissions
        if (!$this->canEditAll && $detail->sale->user_id !== auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso');
            return;
        }

        // Revert product status and stock
        $product = $detail->product;
        $product->increaseAvailableQuantity($detail->quantity);
        $product->update(['status' => 'disponible']);

        $detail->delete();

        // Update sale total
        $sale = $detail->sale;
        if ($sale->saleDetails()->count() === 0 && $sale->rentalDetails()->count() === 0) {
            $sale->delete();
            $this->dispatch('notify', message: 'Transacción eliminada (sin items)');
        } else {
            $sale->calculateTotalAmount();
            $sale->save();
            $this->dispatch('notify', message: 'Item eliminado');
        }
    }

    public function deleteRentalDetail(int $detailId): void
    {
        $detail = RentalDetail::with('sale')->findOrFail($detailId);

        // Check permissions
        if (!$this->canEditAll && $detail->sale->user_id !== auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'No tienes permiso');
            return;
        }

        // Revert product status and stock
        $product = $detail->product;
        $product->increaseAvailableQuantity($detail->quantity);
        $product->decreaseRentedOut($detail->quantity);
        $product->update(['status' => 'disponible']);

        $detail->delete();

        // Update sale total
        $sale = $detail->sale;
        if ($sale->saleDetails()->count() === 0 && $sale->rentalDetails()->count() === 0) {
            $sale->delete();
            $this->dispatch('notify', message: 'Transacción eliminada (sin items)');
        } else {
            $sale->calculateTotalAmount();
            $sale->save();
            $this->dispatch('notify', message: 'Alquiler eliminado');
        }
    }

    public function getAvailableProducts()
    {
        return Product::where('account_id', $this->activeAccount->id)
            ->where('quantity_available', '>', 0)
            ->orderBy('name')
            ->get();
    }
}
