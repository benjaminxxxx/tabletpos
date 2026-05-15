<?php
// app/Livewire/Users/UserList.php

namespace App\Livewire\Users;

use App\Concerns\HasActiveAccount;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use HasActiveAccount, WithPagination;

    public string $search = '';
    public string $roleFilter = '';

    // Para el modal de confirmación de bloqueo/desbloqueo
    public ?int $confirmingUserId = null;
    public string $confirmingAction = '';  // 'block' | 'unblock' | 'remove'
    public bool $showConfirmModal = false;
    public string $confirmingMessage = '';

    public function mount(): void
    {
        $this->bootActiveAccount();
        $this->authorizeManage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    // ─── Acciones ─────────────────────────────────────────────

    public function confirmAction(int $userId, string $action): void
    {
        $this->confirmingUserId = $userId;
        $this->confirmingAction = $action;
        $this->confirmingMessage = match ($action) {
            'block' => 'El usuario no podrá realizar ninguna acción hasta que lo desbloquees.',
            'unblock' => 'El usuario recuperará acceso a esta cuenta.',
            'remove' => 'El usuario perderá acceso inmediatamente. Podrás volver a agregarlo.',
            default => '',
        };
        $this->showConfirmModal = true;
    }

    public function cancelConfirm(): void
    {
        $this->showConfirmModal = false;
        $this->confirmingUserId = null;
        $this->confirmingAction = '';
        $this->confirmingMessage = '';
    }

    public function executeAction(): void
    {
        // Re-verificar permisos
        $this->bootActiveAccount();

        if (!Gate::allows('manage-account-users')) {
            $this->cancelConfirm();
            $this->dispatch('permission-denied');
            return; // mensaje visible, no abort() silencioso
        }

        $userId = $this->confirmingUserId;

        if ($this->activeAccount->owner_id === $userId) {
            $this->cancelConfirm();
            $this->dispatch('permission-denied', message: 'No puedes modificar al propietario de la cuenta.');
            return;
        }

        match ($this->confirmingAction) {
            'block' => $this->activeAccount->members()
                ->updateExistingPivot($userId, ['is_blocked' => true]),
            'unblock' => $this->activeAccount->members()
                ->updateExistingPivot($userId, ['is_blocked' => false]),
            'remove' => $this->activeAccount->members()->detach($userId),
            default => null,
        };

        $this->cancelConfirm();
    }

    public function changeRole(int $userId, string $newRole): void
    {
        $this->bootActiveAccount();
        $this->authorizeManage();

        abort_if($this->activeAccount->owner_id === $userId, 403);
        abort_unless(in_array($newRole, ['admin', 'seller']), 422);

        $this->activeAccount->members()->updateExistingPivot($userId, [
            'role' => $newRole,
        ]);

        $this->dispatch('role-changed');
    }

    // ─── Privados ─────────────────────────────────────────────

    private function setBlocked(int $userId, bool $blocked): void
    {
        $this->activeAccount->members()->updateExistingPivot($userId, [
            'is_blocked' => $blocked,
        ]);
    }

    private function removeMember(int $userId): void
    {
        $this->activeAccount->members()->detach($userId);
    }

    private function authorizeManage(): void
    {
        abort_unless(Gate::allows('manage-account-users'), 403);
    }

    // ─── Render ───────────────────────────────────────────────

    public function render()
    {
        // El owner de la cuenta
        $owner = $this->activeAccount->owner;

        // Miembros filtrados
        $members = $this->activeAccount
            ->members()
            ->when(
                $this->search,
                fn($q) =>
                $q->where(function ($q) {
                    $q->where('users.name', 'like', "%{$this->search}%")
                        ->orWhere('users.email', 'like', "%{$this->search}%");
                })
            )
            ->when(
                $this->roleFilter,
                fn($q) => $q->where('account_users.role', $this->roleFilter) // ← tabla explícita
            )
            ->orderBy('users.name')
            ->paginate(15);

        return view('livewire.users.user-list', [
            'owner' => $owner,
            'members' => $members,
        ]);
    }
}