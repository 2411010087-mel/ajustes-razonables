<?php
declare(strict_types=1);

namespace App\Model;

/**
 * Datos personales mínimos necesarios para registrar a un estudiante.
 */
final class Estudiante
{
    /**
     * Crea la representación inmutable de un estudiante.
     */
    public function __construct(
        public readonly string $documento,
        public readonly string $nombre,
        public readonly string $carrera,
        public readonly TipoDiscapacidad $tipoDiscapacidad,
        public readonly TipoDocumentoAcreditacion $tipoDocumentoAcreditacion,
        public readonly string $numeroDocumentoAcreditacion
    ) {
    }
}
