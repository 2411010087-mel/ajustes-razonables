<?php
declare(strict_types=1);

namespace App\Security;

/**
 * Proporciona el escape HTML utilizado por todas las vistas.
 */
final class Escape
{
    /**
     * Convierte un valor seguro a texto HTML escapado.
     */
    public static function html(mixed $valor): string
    {
        if (!is_scalar($valor) && $valor !== null) {
            return '';
        }

        return htmlspecialchars(
            (string) ($valor ?? ''),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}
