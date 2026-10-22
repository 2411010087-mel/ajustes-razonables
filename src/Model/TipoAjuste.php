<?php
declare(strict_types=1);

namespace App\Model;

use App\Exception\TipoAjusteInvalidoException;

/**
 * Catálogo cerrado de tipos de ajuste razonable.
 */
enum TipoAjuste: string
{
    case TIEMPO_ADICIONAL = 'TIEMPO_ADICIONAL';
    case MATERIAL_ACCESIBLE = 'MATERIAL_ACCESIBLE';
    case INTERPRETE_LSP = 'INTERPRETE_LSP';
    case AULA_ACCESIBLE = 'AULA_ACCESIBLE';
    case MOBILIARIO = 'MOBILIARIO';
    case ACOMPANAMIENTO = 'ACOMPANAMIENTO';

    /**
     * Convierte un valor del formulario al tipo de ajuste correspondiente.
     *
     * @throws TipoAjusteInvalidoException Si el valor no pertenece al catálogo.
     */
    public static function desdeValor(string $valor): self
    {
        return self::tryFrom($valor)
            ?? throw new TipoAjusteInvalidoException('El tipo de ajuste seleccionado no es válido.');
    }
}
