<?php
declare(strict_types=1);

namespace App\Model;

/**
 * Representa una solicitud de ajuste razonable asociada a un estudiante.
 */
final class Solicitud
{
    /**
     * Crea la representación de una solicitud.
     */
    public function __construct(
        public readonly ?int $idSolicitud,
        public readonly string $documentoEstudiante,
        public readonly int $idTipoAjuste,
        public readonly TipoAjuste $tipoAjuste,
        public readonly string $asignatura,
        public Estado $estado,
        public ?string $motivoRechazo,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?string $nombreEstudiante = null
    ) {
    }
}
