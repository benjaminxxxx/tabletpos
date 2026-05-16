<?php

namespace App\Enums;

enum SaleStatus: string
{
    case PENDIENTE = 'pendiente';
    case COMPLETADA = 'completada';
    case CANCELADA = 'cancelada';

    public function label(): string
    {
        return match($this) {
            self::PENDIENTE => 'Pendiente',
            self::COMPLETADA => 'Completada',
            self::CANCELADA => 'Cancelada',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDIENTE => 'yellow',
            self::COMPLETADA => 'green',
            self::CANCELADA => 'red',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::PENDIENTE => 'bg-yellow-100 text-yellow-800',
            self::COMPLETADA => 'bg-green-100 text-green-800',
            self::CANCELADA => 'bg-red-100 text-red-800',
        };
    }
}
