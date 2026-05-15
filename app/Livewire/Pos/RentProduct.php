<?php

namespace App\Livewire\Pos;

use App\Models\Account;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Rental;
use App\Models\Movement;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Carbon\Carbon;

class RentProduct extends Component
{
    public Account $account;

    #[Validate('required|string')]
    public string $productSearch = '';

    #[Validate('required|string')]
    public string $customerSearch = '';

    public ?Product $selectedProduct = null;
    public ?Customer $selectedCustomer = null;
    public bool $showCustomerForm = false;

    #[Validate('required|string')]
    public string $customerDni = '';

    #[Validate('required|string')]
    public string $customerName = '';

    #[Validate('nullable|string')]
    public string $customerPhone = '';

    #[Validate('nullable|numeric|min:0')]
    public float $rentalAmount = 0;

    #[Validate('nullable|numeric|min:0')]
    public ?float $depositAmount = null;

    #[Validate('required|date|after:today')]
    public string $returnDate = '';

    public bool $showConfirmation = false;
    public string $successMessage = '';
    public string $errorMessage = '';

    public function mount(Account $account)
    {
        $this->account = $account;
        $this->returnDate = today()->addDays(3)->format('Y-m-d');
    }

    public function searchProducts()
    {
        if (strlen($this->productSearch) < 2) {
            return [];
        }

        return Product::where('account_id', $this->account->id)
            ->where('status', 'available')
            ->where('can_rent', true)
            ->where(function ($query) {
                $query->where('public_code', 'like', '%' . $this->productSearch . '%')
                    ->orWhere('name', 'like', '%' . $this->productSearch . '%');
            })
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function searchCustomers()
    {
        if (strlen($this->customerSearch) < 2) {
            return [];
        }

        return Customer::where('account_id', $this->account->id)
            ->where(function ($query) {
                $query->where('dni', 'like', '%' . $this->customerSearch . '%')
                    ->orWhere('full_name', 'like', '%' . $this->customerSearch . '%');
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

    public function selectCustomer($customerId)
    {
        $this->selectedCustomer = Customer::find($customerId);
        $this->customerSearch = '';
        $this->showCustomerForm = false;
    }

    public function toggleCustomerForm()
    {
        $this->showCustomerForm = !$this->showCustomerForm;
        if (!$this->showCustomerForm) {
            $this->customerDni = '';
            $this->customerName = '';
            $this->customerPhone = '';
        }
    }

    public function createCustomer()
    {
        $this->validate([
            'customerDni' => 'required|string',
            'customerName' => 'required|string',
            'customerPhone' => 'nullable|string',
        ]);

        try {
            $customer = Customer::create([
                'account_id' => $this->account->id,
                'dni' => $this->customerDni,
                'full_name' => $this->customerName,
                'phone' => $this->customerPhone,
            ]);

            $this->selectedCustomer = $customer;
            $this->showCustomerForm = false;
            $this->customerDni = '';
            $this->customerName = '';
            $this->customerPhone = '';
        } catch (\Exception $e) {
            $this->errorMessage = 'Error creating customer: ' . $e->getMessage();
        }
    }

    public function confirmRental()
    {
        $this->validate();

        if (!$this->selectedProduct) {
            $this->errorMessage = 'Please select a product';
            return;
        }

        if (!$this->selectedCustomer) {
            $this->errorMessage = 'Please select or create a customer';
            return;
        }

        if ($this->rentalAmount <= 0) {
            $this->errorMessage = 'Rental amount must be greater than 0';
            return;
        }

        $this->showConfirmation = true;
    }

    public function completeRental()
    {
        try {
            $rental = Rental::create([
                'account_id' => $this->account->id,
                'user_id' => auth()->id(),
                'product_id' => $this->selectedProduct->id,
                'customer_id' => $this->selectedCustomer->id,
                'public_code_snapshot' => $this->selectedProduct->public_code,
                'description_snapshot' => $this->selectedProduct->description,
                'amount' => $this->rentalAmount,
                'deposit_amount' => $this->depositAmount,
                'return_date' => $this->returnDate,
                'status' => 'active',
            ]);

            // Update product status
            $this->selectedProduct->update(['status' => 'rented']);
            $this->selectedProduct->increment('rent_count');

            // Record movement for rental amount
            Movement::create([
                'account_id' => $this->account->id,
                'user_id' => auth()->id(),
                'type' => 'rental',
                'reference_id' => $rental->id,
                'reference_type' => 'Rental',
                'amount' => $this->rentalAmount,
                'direction' => 'in',
                'notes' => 'Rental: ' . $this->selectedProduct->public_code,
            ]);

            // Record deposit if applicable
            if ($this->depositAmount && $this->depositAmount > 0) {
                Movement::create([
                    'account_id' => $this->account->id,
                    'user_id' => auth()->id(),
                    'type' => 'rental',
                    'reference_id' => $rental->id,
                    'reference_type' => 'Rental',
                    'amount' => $this->depositAmount,
                    'direction' => 'in',
                    'notes' => 'Deposit: ' . $this->selectedProduct->public_code,
                ]);
            }

            $this->successMessage = 'Rental completed! Reference: #' . $rental->id;
            $this->reset(['selectedProduct', 'selectedCustomer', 'rentalAmount', 'depositAmount', 'returnDate', 'showConfirmation']);
            $this->returnDate = today()->addDays(3)->format('Y-m-d');
        } catch (\Exception $e) {
            $this->errorMessage = 'Error completing rental: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.pos.rent-product', [
            'searchResults' => $this->searchProducts(),
            'customerResults' => $this->searchCustomers(),
        ]);
    }
}
