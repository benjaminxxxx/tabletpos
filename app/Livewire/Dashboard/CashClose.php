<?php

namespace App\Livewire\Dashboard;

use App\Models\Account;
use App\Models\Movement;
use App\Models\CashClose as CashCloseModel;
use Livewire\Component;
use Livewire\Attributes\Validate;
use Carbon\Carbon;

class CashClose extends Component
{
    public Account $account;

    #[Validate('required|date|before_or_equal:today')]
    public string $closeDate = '';

    #[Validate('nullable|numeric|min:0')]
    public ?float $confirmedAmount = null;

    #[Validate('nullable|string')]
    public ?string $notes = null;

    public ?float $expectedAmount = null;
    public bool $showConfirmation = false;
    public string $successMessage = '';
    public string $errorMessage = '';
    public ?CashCloseModel $lastClose = null;

    public function mount(Account $account)
    {
        $this->account = $account;
        $this->closeDate = today()->format('Y-m-d');
        $this->calculateExpectedAmount();
        $this->loadLastClose();
    }

    public function calculateExpectedAmount()
    {
        $this->expectedAmount = Movement::where('account_id', $this->account->id)
            ->whereDate('created_at', $this->closeDate)
            ->where('direction', 'in')
            ->sum('amount')
            -
            Movement::where('account_id', $this->account->id)
                ->whereDate('created_at', $this->closeDate)
                ->where('direction', 'out')
                ->sum('amount');
    }

    public function loadLastClose()
    {
        $this->lastClose = CashCloseModel::where('account_id', $this->account->id)
            ->orderBy('close_date', 'desc')
            ->first();
    }

    public function updatedCloseDate()
    {
        $this->calculateExpectedAmount();
    }

    public function confirmClose()
    {
        $this->validate([
            'closeDate' => 'required|date|before_or_equal:today',
            'confirmedAmount' => 'nullable|numeric|min:0',
        ]);

        // Check if already closed
        $existing = CashCloseModel::where('account_id', $this->account->id)
            ->where('close_date', $this->closeDate)
            ->first();

        if ($existing) {
            $this->errorMessage = 'This day has already been closed';
            return;
        }

        $this->showConfirmation = true;
    }

    public function completeClose()
    {
        try {
            CashCloseModel::create([
                'account_id' => $this->account->id,
                'user_id' => auth()->id(),
                'close_date' => $this->closeDate,
                'expected_amount' => $this->expectedAmount ?? 0,
                'confirmed_amount' => $this->confirmedAmount,
                'notes' => $this->notes,
            ]);

            $this->successMessage = 'Cash close completed successfully!';
            $this->reset(['confirmedAmount', 'notes', 'showConfirmation']);
            $this->closeDate = today()->format('Y-m-d');
            $this->calculateExpectedAmount();
            $this->loadLastClose();
        } catch (\Exception $e) {
            $this->errorMessage = 'Error completing close: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.dashboard.cash-close');
    }
}
