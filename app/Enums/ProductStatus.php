<?php

namespace App\Enums;

enum ProductStatus: string
{
    case DISPONIBLE = 'disponible';
    case VENDIDO = 'vendido';
    case BLOQUEADO = 'bloqueado';
    case ALQUILADO = 'alquilado';
    case PERDIDO = 'perdido';

    public function label(): string
    {
        return match($this) {
            self::DISPONIBLE => 'Disponible',
            self::VENDIDO => 'Vendido',
            self::BLOQUEADO => 'Bloqueado',
            self::ALQUILADO => 'Alquilado',
            self::PERDIDO => 'Perdido',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DISPONIBLE => 'green',
            self::VENDIDO => 'blue',
            self::BLOQUEADO => 'gray',
            self::ALQUILADO => 'purple',
            self::PERDIDO => 'red',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::DISPONIBLE => 'bg-green-100 text-green-800',
            self::VENDIDO => 'bg-blue-100 text-blue-800',
            self::BLOQUEADO => 'bg-gray-100 text-gray-800',
            self::ALQUILADO => 'bg-purple-100 text-purple-800',
            self::PERDIDO => 'bg-red-100 text-red-800',
        };
    }
}
