{{-- resources/views/accounts/select.blade.php --}}
<x-layouts::app :title="$title">
    <div class="min-h-screen flex items-center justify-center p-6">
        <div class="w-full max-w-lg">

            <flux:heading size="xl" class="mb-1 text-center">
                {{ __('¿A qué cuenta ingresas?') }}
            </flux:heading>
            <flux:subheading class="mb-6 text-center">
                {{ __('Elige la cuenta que deseas administrar en esta sesión.') }}
            </flux:subheading>

            {{-- Errores de acceso (bloqueado, eliminado, etc.) --}}
            @if($errors->any())
                <flux:callout variant="danger" icon="exclamation-circle" class="mb-6">
                    {{ $errors->first('account') }}
                </flux:callout>
            @endif

            @if(! $ownedAccount && $memberAccounts->isEmpty())
                <flux:callout variant="warning" icon="exclamation-triangle">
                    {{ __('No tienes cuentas disponibles. Contacta al administrador del sistema.') }}
                </flux:callout>
            @else
                <div class="space-y-3">

                    @if($ownedAccount)
                        <form method="POST" action="{{ route('accounts.set-active') }}">
                            @csrf
                            <input type="hidden" name="account_id" value="{{ $ownedAccount->id }}">
                            <button type="submit" class="w-full text-left">
                                <flux:card class="hover:border-zinc-400 transition-colors cursor-pointer">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <flux:icon name="building-storefront" class="size-5 text-zinc-500" />
                                            <div>
                                                <p class="font-medium text-sm">{{ $ownedAccount->name }}</p>
                                                @if($ownedAccount->description)
                                                    <p class="text-xs text-zinc-500 mt-0.5">
                                                        {{ $ownedAccount->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <flux:badge variant="pill" color="lime" size="sm">
                                            {{ __('Mi cuenta') }}
                                        </flux:badge>
                                    </div>
                                </flux:card>
                            </button>
                        </form>
                    @endif

                    @foreach($memberAccounts as $account)
                        <form method="POST" action="{{ route('accounts.set-active') }}">
                            @csrf
                            <input type="hidden" name="account_id" value="{{ $account->id }}">
                            <button type="submit" class="w-full text-left">
                                <flux:card class="hover:border-zinc-400 transition-colors cursor-pointer">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <flux:icon name="building-office" class="size-5 text-zinc-500" />
                                            <div>
                                                <p class="font-medium text-sm">{{ $account->name }}</p>
                                                @if($account->description)
                                                    <p class="text-xs text-zinc-500 mt-0.5">
                                                        {{ $account->description }}
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                        <flux:badge variant="pill" size="sm"
                                            color="{{ $account->pivot->role === 'admin' ? 'blue' : 'zinc' }}">
                                            {{ $account->pivot->role === 'admin'
                                                ? __('Administrador')
                                                : __('Vendedor') }}
                                        </flux:badge>
                                    </div>
                                </flux:card>
                            </button>
                        </form>
                    @endforeach

                </div>
            @endif

        </div>
    </div>
</x-layouts::app>