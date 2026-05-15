<?php

namespace App\Livewire\Settings;

use App\Models\Account;
use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Validate;

class UserManagement extends Component
{
    public Account $account;

    #[Validate('required|email')]
    public string $inviteEmail = '';

    #[Validate('required|in:admin,seller')]
    public string $inviteRole = 'seller';

    public array $users = [];
    public string $successMessage = '';
    public string $errorMessage = '';

    public function mount(Account $account)
    {
        $this->account = $account;
        $this->loadUsers();
    }

    public function loadUsers()
    {
        $this->users = $this->account->users()
            ->with('pivot')
            ->get()
            ->toArray();
    }

    public function inviteUser()
    {
        $this->validate();

        try {
            $user = User::where('email', $this->inviteEmail)->first();

            if (!$user) {
                // For now, we'll note that the user needs to register
                $this->errorMessage = 'User does not exist. They must register first.';
                return;
            }

            // Check if already in account
            if ($this->account->users()->where('user_id', $user->id)->exists()) {
                $this->errorMessage = 'User is already a member of this account';
                return;
            }

            $this->account->users()->attach($user->id, ['role' => $this->inviteRole]);

            $this->successMessage = "User invited as {$this->inviteRole}!";
            $this->reset(['inviteEmail', 'inviteRole']);
            $this->loadUsers();
        } catch (\Exception $e) {
            $this->errorMessage = 'Error inviting user: ' . $e->getMessage();
        }
    }

    public function updateRole($userId, $newRole)
    {
        try {
            $this->account->users()->updateExistingPivot($userId, ['role' => $newRole]);
            $this->successMessage = 'Role updated successfully!';
            $this->loadUsers();
        } catch (\Exception $e) {
            $this->errorMessage = 'Error updating role: ' . $e->getMessage();
        }
    }

    public function toggleBlock($userId)
    {
        try {
            $user = User::find($userId);
            $isBlocked = $user->isBlockedInAccount($this->account->id);
            
            $this->account->users()->updateExistingPivot($userId, ['is_blocked' => !$isBlocked]);
            $this->successMessage = !$isBlocked ? 'User blocked' : 'User unblocked';
            $this->loadUsers();
        } catch (\Exception $e) {
            $this->errorMessage = 'Error updating user status: ' . $e->getMessage();
        }
    }

    public function removeUser($userId)
    {
        try {
            $this->account->users()->detach($userId);
            $this->successMessage = 'User removed from account';
            $this->loadUsers();
        } catch (\Exception $e) {
            $this->errorMessage = 'Error removing user: ' . $e->getMessage();
        }
    }

    public function render()
    {
        return view('livewire.settings.user-management');
    }
}
