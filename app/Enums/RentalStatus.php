<?php

namespace App\Enums;

enum RentalStatus: string
{
    case PENDIENTE = 'pendiente';
    case ACTIVO = 'activo';
    case DEVUELTO = 'devuelto';
    case PERDIDO = 'perdido';
    case VENCIDO = 'vencido';

    public function label(): string
    {
        return match($this) {
            self::PENDIENTE => 'Pendiente',
            self::ACTIVO => 'Activo',
            self::DEVUELTO => 'Devuelto',
            self::PERDIDO => 'Perdido',
            self::VENCIDO => 'Vencido',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PENDIENTE => 'yellow',
            self::ACTIVO => 'blue',
            self::DEVUELTO => 'green',
            self::PERDIDO => 'red',
            self::VENCIDO => 'orange',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::PENDIENTE => 'bg-yellow-100 text-yellow-800',
            self::ACTIVO => 'bg-blue-100 text-blue-800',
            self::DEVUELTO => 'bg-green-100 text-green-800',
            self::PERDIDO => 'bg-red-100 text-red-800',
            self::VENCIDO => 'bg-orange-100 text-orange-800',
        };
    }
}
