<?php
declare(strict_types=1);

namespace App\Repository;

use App\Model\TipoAjuste;
use PDO;

/**
 * Consulta el catálogo de tipos de ajuste mediante PDO.
 */
final class TipoAjusteRepositoryPdo implements TipoAjusteRepository
{
    /**
     * Recibe la conexión PDO utilizada por el repositorio.
     */
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Obtiene el identificador de un tipo de ajuste por su valor.
     */
    public function idPorValor(string $valor): ?int
    {
        $sentencia = $this->pdo->prepare(
            'SELECT id_tipo_ajuste FROM tipo_ajuste WHERE nombre = :nombre'
        );
        $sentencia->execute(['nombre' => $valor]);
        $id = $sentencia->fetchColumn();

        return $id === false ? null : (int) $id;
    }

    /**
     * Indica si el tipo de ajuste existe en el catálogo.
     */
    public function existeValor(string $valor): bool
    {
        return $this->idPorValor($valor) !== null;
    }

    /**
     * Lista los tipos de ajuste existentes en el catálogo.
     *
     * @return TipoAjuste[]
     */
    public function listarTodos(): array
    {
        $sentencia = $this->pdo->prepare(
            'SELECT nombre FROM tipo_ajuste ORDER BY id_tipo_ajuste'
        );
        $sentencia->execute();

        return array_map(
            static fn (array $fila): TipoAjuste => TipoAjuste::from((string) $fila['nombre']),
            $sentencia->fetchAll()
        );
    }
}
