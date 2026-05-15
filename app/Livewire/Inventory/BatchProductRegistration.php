<?php

namespace App\Livewire\Inventory;

use App\Models\Account;
use App\Models\Product;
use App\Models\Location;
use Livewire\Component;
use Livewire\Attributes\Validate;

class BatchProductRegistration extends Component
{
    public Account $account;

    #[Validate('required|string')]
    public string $batchData = '';

    public array $parsedRows = [];
    public array $errors = [];
    public string $successMessage = '';
    public array $locations = [];

    public function mount(Account $account)
    {
        $this->account = $account;
        $this->locations = Location::where('account_id', $account->id)->get()->toArray();
    }

    public function parseBatchData()
    {
        $this->errors = [];
        $this->parsedRows = [];

        $lines = array_filter(explode("\n", trim($this->batchData)));

        foreach ($lines as $lineIndex => $line) {
            $parts = array_map('trim', explode("\t", $line));

            if (count($parts) < 5) {
                $this->errors[$lineIndex] = 'Invalid format. Expected: name, description, brand, origin, category_prefix, quantity';
                continue;
            }

            $this->parsedRows[$lineIndex] = [
                'name' => $parts[0],
                'description' => $parts[1] ?? '',
                'brand' => $parts[2] ?? '',
                'origin' => $parts[3] ?? '',
                'category_prefix' => strtoupper($parts[4]),
                'quantity' => (int) ($parts[5] ?? 1),
                'status' => 'pending',
            ];
        }
    }

    public function saveBatch()
    {
        if (empty($this->parsedRows)) {
            $this->errors[] = 'No valid rows to save';
            return;
        }

        try {
            foreach ($this->parsedRows as $row) {
                for ($i = 0; $i < $row['quantity']; $i++) {
                    $publicCode = Product::generatePublicCode($this->account->id, $row['category_prefix']);

                    Product::create([
                        'account_id' => $this->account->id,
                        'public_code' => $publicCode,
                        'name' => $row['name'],
                        'description' => $row['description'],
                        'brand' => $row['brand'],
                        'origin' => $row['origin'],
                        'category_prefix' => $row['category_prefix'],
                        'status' => 'available',
                        'can_sell' => true,
                        'can_rent' => true,
                    ]);
                }
            }

            $totalProducts = collect($this->parsedRows)->sum('quantity');
            $this->successMessage = "Successfully created $totalProducts products!";
            $this->reset(['batchData', 'parsedRows', 'errors']);
        } catch (\Exception $e) {
            $this->errors[] = 'Error saving batch: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.inventory.batch-product-registration');
    }
}
