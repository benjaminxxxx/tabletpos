{{-- resources/views/livewire/users/create-user.blade.php --}}
<div>
    <div class="max-w-xl">
        <flux:heading size="xl" class="mb-1">
            {{ $userId ? __('Editar usuario') : __('Agregar usuario') }}
        </flux:heading>
        <flux:subheading class="mb-6">
            {{ $userId
                ? __('Modifica los datos del usuario. Deja la contraseña en blanco para no cambiarla.')
                : __('El usuario podrá iniciar sesión con estas credenciales.')
            }}
        </flux:subheading>

        @if($saved)
            <flux:callout variant="success" icon="check-circle" class="mb-6">
                {{ $userId ? __('Usuario actualizado correctamente.') : __('Usuario agregado correctamente.') }}
            </flux:callout>
        @endif

        <form wire:submit="save" class="space-y-5">

            <flux:field>
                <flux:label>{{ __('Nombre completo') }}</flux:label>
                <flux:input
                    wire:model="name"
                    type="text"
                    :placeholder="__('Juan Pérez')"
                    autofocus
                />
                <flux:error name="name" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Correo electrónico') }}</flux:label>
                <flux:input
                    wire:model="email"
                    type="email"
                    :placeholder="__('juan@ejemplo.com')"
                    :disabled="(bool) $userId"  {{-- en edición el email puede o no editarse --}}
                />
                <flux:error name="email" />
            </flux:field>

            <flux:field>
                <flux:label>
                    {{ __('Contraseña') }}
                    @if($userId)
                        <span class="text-xs text-zinc-400 font-normal ml-1">
                            {{ __('(dejar en blanco para no cambiar)') }}
                        </span>
                    @endif
                </flux:label>
                <flux:input
                    wire:model="password"
                    type="password"
                    :placeholder="$userId ? __('••••••••') : __('Mínimo 8 caracteres')"
                />
                <flux:error name="password" />
            </flux:field>

            <flux:field>
                <flux:label>{{ __('Rol en esta cuenta') }}</flux:label>
                <flux:select wire:model="role">
                    <flux:select.option value="seller">{{ __('Vendedor') }}</flux:select.option>
                    <flux:select.option value="admin">{{ __('Administrador') }}</flux:select.option>
                </flux:select>
                <flux:error name="role" />
            </flux:field>

            <div class="flex gap-3 pt-2">
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove>
                        {{ $userId ? __('Guardar cambios') : __('Agregar usuario') }}
                    </span>
                    <span wire:loading>{{ __('Guardando...') }}</span>
                </flux:button>

                <flux:button
                    type="button"
                    variant="ghost"
                    :href="route('users.index')"
                    wire:navigate>
                    {{ __('Cancelar') }}
                </flux:button>
            </div>

        </form>
    </div>
</div>