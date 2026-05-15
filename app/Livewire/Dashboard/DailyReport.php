<?php

namespace App\Livewire\Dashboard;

use App\Models\Account;
use App\Models\Movement;
use App\Models\Sale;
use App\Models\Rental;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyReport extends Component
{
    public Account $account;
    public Carbon $selectedDate;

    public function mount(Account $account)
    {
        $this->account = $account;
        $this->selectedDate = today();
    }

    public function previousDay()
    {
        $this->selectedDate = $this->selectedDate->subDay();
    }

    public function nextDay()
    {
        $this->selectedDate = $this->selectedDate->addDay();
    }

    public function goToToday()
    {
        $this->selectedDate = today();
    }

    public function getIncome(): float
    {
        return Movement::where('account_id', $this->account->id)
            ->where('direction', 'in')
            ->whereDate('created_at', $this->selectedDate)
            ->sum('amount');
    }

    public function getOutflow(): float
    {
        return Movement::where('account_id', $this->account->id)
            ->where('direction', 'out')
            ->whereDate('created_at', $this->selectedDate)
            ->sum('amount');
    }

    public function getNet(): float
    {
        return $this->getIncome() - $this->getOutflow();
    }

    public function getSales(): Collection
    {
        return Sale::where('account_id', $this->account->id)
            ->where('status', 'completed')
            ->whereDate('created_at', $this->selectedDate)
            ->with('user', 'product')
            ->get();
    }

    public function getRentals(): Collection
    {
        return Rental::where('account_id', $this->account->id)
            ->where('status', 'active')
            ->whereDate('created_at', $this->selectedDate)
            ->with('user', 'customer')
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard.daily-report', [
            'income' => $this->getIncome(),
            'outflow' => $this->getOutflow(),
            'net' => $this->getNet(),
            'sales' => $this->getSales(),
            'rentals' => $this->getRentals(),
        ]);
    }
}
