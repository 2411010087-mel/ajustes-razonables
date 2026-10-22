<?php
declare(strict_types=1);

namespace App\Exception;

/**
 * Representa errores de validación y sus mensajes asociados a campos.
 */
class ValidacionException extends \Exception
{
    /**
     * Crea un error de validación con mensajes específicos para los campos.
     *
     * @param array<string, string> $errores Errores indexados por nombre de campo.
     */
    public function __construct(
        string $mensaje,
        public readonly array $errores = [],
        ?\Throwable $previous = null
    )
    {
        parent::__construct($mensaje, 0, $previous);
    }
}
