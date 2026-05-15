{{-- resources/views/livewire/users/user-list.blade.php --}}
<div>
    {{-- Cabecera --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <flux:heading size="xl" class="mb-1">{{ __('Usuarios') }}</flux:heading>
            <flux:subheading>
                {{ $activeAccount->name }}
            </flux:subheading>
        </div>
        <flux:button icon="user-plus" variant="primary" :href="route('users.create')" wire:navigate>
            {{ __('Agregar usuario') }}
        </flux:button>
    </div>

    {{-- Filtros --}}
    <div class="flex gap-3 mb-5">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass"
            :placeholder="__('Buscar por nombre o correo...')" class="max-w-sm" clearable />
        <flux:select wire:model.live="roleFilter" class="w-40">
            <flux:select.option value="">{{ __('Todos los roles') }}</flux:select.option>
            <flux:select.option value="admin">{{ __('Administradores') }}</flux:select.option>
            <flux:select.option value="seller">{{ __('Vendedores') }}</flux:select.option>
        </flux:select>
    </div>

    {{-- Tabla --}}
    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Usuario') }}</flux:table.column>
            <flux:table.column>{{ __('Rol') }}</flux:table.column>
            <flux:table.column>{{ __('Estado') }}</flux:table.column>
            <flux:table.column></flux:table.column>
        </flux:table.columns>

        <flux:table.rows>

            {{-- Fila del owner (siempre primera, sin acciones) --}}
            <flux:table.row>
                <flux:table.cell>
                    <div class="flex items-center gap-3">
                        <flux:avatar :name="$owner->name" size="sm" />
                        <div>
                            <p class="text-sm font-medium">{{ $owner->name }}</p>
                            <p class="text-xs text-zinc-500">{{ $owner->email }}</p>
                        </div>
                    </div>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge variant="pill" color="lime" size="sm">
                        {{ __('Propietario') }}
                    </flux:badge>
                </flux:table.cell>
                <flux:table.cell>
                    <flux:badge variant="pill" color="green" size="sm">
                        {{ __('Activo') }}
                    </flux:badge>
                </flux:table.cell>
                <flux:table.cell>
                    {{-- El owner no tiene acciones --}}
                </flux:table.cell>
            </flux:table.row>

            {{-- Miembros (admin / seller) --}}
            @foreach($members as $member)
                <flux:table.row :key="$member->id">
                    <flux:table.cell>
                        <div class="flex items-center gap-3">
                            <flux:avatar :name="$member->name" size="sm" />
                            <div>
                                <p class="text-sm font-medium">{{ $member->name }}</p>
                                <p class="text-xs text-zinc-500">{{ $member->email }}</p>
                            </div>
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        @php
                            $role = $member->pivot->role;
                            $color = $role === 'admin' ? 'blue' : 'zinc';
                            $label = $role === 'admin' ? __('Administrador') : __('Vendedor');
                        @endphp
                        <flux:badge variant="pill" :color="$color" size="sm">
                            {{ $label }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell>
                        @if($member->pivot->is_blocked)
                            <flux:badge variant="pill" color="red" size="sm">
                                {{ __('Bloqueado') }}
                            </flux:badge>
                        @else
                            <flux:badge variant="pill" color="green" size="sm">
                                {{ __('Activo') }}
                            </flux:badge>
                        @endif
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:dropdown>
                            <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                            <flux:menu>
                                @if($member->pivot->is_blocked)
                                    <flux:menu.item icon="lock-open" wire:click="confirmAction({{ $member->id }}, 'unblock')">
                                        {{ __('Desbloquear') }}
                                    </flux:menu.item>
                                @else
                                    <flux:menu.item icon="lock-closed" wire:click="confirmAction({{ $member->id }}, 'block')">
                                        {{ __('Bloquear') }}
                                    </flux:menu.item>
                                @endif
                                <flux:menu.separator />
                                {{-- Dentro del flux:menu de cada miembro --}}
                                <flux:menu.item icon="pencil-square" :href="route('users.edit', $member->id)" wire:navigate>
                                    {{ __('Editar') }}
                                </flux:menu.item>
                                <flux:menu.item icon="trash" variant="danger"
                                    wire:click="confirmAction({{ $member->id }}, 'remove')">
                                    {{ __('Quitar de la cuenta') }}
                                </flux:menu.item>
                            </flux:menu>
                        </flux:dropdown>
                    </flux:table.cell>
                </flux:table.row>
            @endforeach

        </flux:table.rows>
    </flux:table>

    {{-- Paginación --}}
    <div class="mt-4">
        {{ $members->links() }}
    </div>

    {{-- Modal de confirmación --}}
    {{-- Modal corregido en user-list.blade.php --}}
    <flux:modal wire:model="showConfirmModal">
        <div class="space-y-4 p-1">
            <flux:heading size="lg">
                @if($confirmingAction === 'remove') {{ __('¿Quitar usuario?') }}
                @elseif($confirmingAction === 'block') {{ __('¿Bloquear usuario?') }}
                @else {{ __('¿Desbloquear usuario?') }}
                @endif
            </flux:heading>

            <flux:text>{{ __($confirmingMessage) }}</flux:text>

            <div class="flex gap-3 justify-end">
                <flux:button variant="ghost" wire:click="cancelConfirm">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button variant="{{ $confirmingAction === 'remove' ? 'danger' : 'primary' }}"
                    wire:click="executeAction" wire:loading.attr="disabled">
                    {{ __('Confirmar') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    {{-- Toast de permiso denegado --}}
    <div x-data="{ show: false, message: '' }" x-on:permission-denied.window="
        message = $event.detail.message ?? 'No tienes permiso para realizar esta acción.';
        show = true;
        setTimeout(() => show = false, 4000)
    " x-show="show" x-transition class="fixed bottom-6 right-6 z-50">
        <flux:callout variant="danger" icon="exclamation-circle">
            <span x-text="message"></span>
        </flux:callout>
    </div>

</div>