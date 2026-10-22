<?php
declare(strict_types=1);

namespace App\Model;

/**
 * Estados posibles de una solicitud de ajuste razonable.
 */
enum Estado: string
{
    case RECIBIDA = 'RECIBIDA';
    case EVALUADA = 'EVALUADA';
    case APROBADA = 'APROBADA';
    case RECHAZADA = 'RECHAZADA';
    case IMPLEMENTADA = 'IMPLEMENTADA';

    /**
     * Indica si desde este estado se permite transitar al estado de destino.
     */
    public function puedeTransitarA(Estado $destino): bool
    {
        return match ($this) {
            self::RECIBIDA => $destino === self::EVALUADA,
            self::EVALUADA => in_array($destino, [self::APROBADA, self::RECHAZADA], true),
            self::APROBADA => $destino === self::IMPLEMENTADA,
            self::RECHAZADA, self::IMPLEMENTADA => false,
        };
    }

    /**
     * Indica si la solicitud se considera pendiente.
     */
    public function esPendiente(): bool
    {
        return !in_array($this, [self::RECHAZADA, self::IMPLEMENTADA], true);
    }
}
