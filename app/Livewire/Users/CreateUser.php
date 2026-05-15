<?php
// app/Livewire/Users/CreateUser.php

namespace App\Livewire\Users;

use App\Concerns\HasActiveAccount;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CreateUser extends Component
{
    use HasActiveAccount;

    public ?int   $userId   = null; // null = crear, int = editar
    public string $name     = '';
    public string $email    = '';
    public string $password = '';
    public string $role     = 'seller';
    public bool   $saved    = false;

    public function mount(?int $userId = null): void
    {
        $this->bootActiveAccount();
        $this->authorizeManage();

        if ($userId) {
            $this->loadUser($userId);
        }
    }

    private function loadUser(int $userId): void
    {
        // Verificar que el usuario pertenece a esta cuenta
        $member = $this->activeAccount->members()
            ->where('users.id', $userId)
            ->first();

        abort_if(! $member, 404);

        $this->userId = $userId;
        $this->name   = $member->name;
        $this->email  = $member->email;
        $this->role   = $member->pivot->role;
        // password intencionalmente vacío
    }

    public function save(): void
    {
        $this->bootActiveAccount();
        $this->authorizeManage();

        $this->validate($this->rules());

        if ($this->userId) {
            $this->update();
        } else {
            $this->create();
        }

        $this->saved = true;
        $this->reset(['name', 'email', 'password']);
        $this->dispatch('user-saved');
    }

    private function rules(): array
    {
        return [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255'],
            'password' => $this->userId
                ? ['nullable', 'string', 'min:8']        // editar: opcional
                : ['required', 'string', 'min:8'],        // crear: obligatorio
            'role'     => ['required', Rule::in(['admin', 'seller'])],
        ];
    }

    private function create(): void
    {
        // Verificar owner
        $isOwner = User::where('email', $this->email)
            ->where('id', $this->activeAccount->owner_id)
            ->exists();

        if ($isOwner) {
            $this->addError('email', 'Este correo es el propietario de la cuenta.');
            return;
        }

        // Verificar miembro duplicado
        $alreadyMember = User::where('email', $this->email)
            ->whereHas('memberAccounts', fn ($q) =>
                $q->where('account_id', $this->activeAccount->id)
            )
            ->exists();

        if ($alreadyMember) {
            $this->addError('email', 'Este usuario ya pertenece a esta cuenta.');
            return;
        }

        $user = User::firstOrCreate(
            ['email' => $this->email],
            [
                'name'     => $this->name,
                'password' => Hash::make($this->password),
            ]
        );

        $this->activeAccount->members()->syncWithoutDetaching([
            $user->id => [
                'role'       => $this->role,
                'is_blocked' => false,
            ],
        ]);
    }

    private function update(): void
    {
        $user = User::findOrFail($this->userId);

        $user->name  = $this->name;
        $user->email = $this->email;

        // Solo actualizar password si se escribió algo
        if (filled($this->password)) {
            $user->password = Hash::make($this->password);
        }

        $user->save();

        // Actualizar rol en el pivot
        $this->activeAccount->members()->updateExistingPivot($this->userId, [
            'role' => $this->role,
        ]);
    }

    private function authorizeManage(): void
    {
        abort_unless(Gate::allows('manage-account-users'), 403);
    }

    public function render()
    {
        return view('livewire.users.create-user');
    }
}