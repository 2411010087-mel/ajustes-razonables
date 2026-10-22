<?php
declare(strict_types=1);

namespace App\Repository;

use App\Model\TipoAjuste;

/**
 * Contrato de consulta del catálogo de tipos de ajuste.
 */
interface TipoAjusteRepository
{
    /**
     * Obtiene el identificador de un tipo de ajuste por su valor.
     */
    public function idPorValor(string $valor): ?int;

    /**
     * Indica si el tipo de ajuste existe en el catálogo.
     */
    public function existeValor(string $valor): bool;

    /**
     * Lista los tipos de ajuste existentes en el catálogo.
     *
     * @return TipoAjuste[]
     */
    public function listarTodos(): array;
}
