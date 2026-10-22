<?php
declare(strict_types=1);

namespace App\Repository;

use App\Model\Estudiante;

/**
 * Contrato de persistencia y búsqueda de estudiantes.
 */
interface EstudianteRepository
{
    /**
     * Busca un estudiante por su documento.
     */
    public function buscarPorDocumento(string $documento): ?Estudiante;

    /**
     * Indica si ya existe un estudiante con el documento indicado.
     */
    public function existeDocumento(string $documento): bool;

    /**
     * Guarda un estudiante nuevo.
     */
    public function guardar(Estudiante $estudiante): void;

    /**
     * Busca estudiantes por coincidencia de documento o nombre.
     *
     * @return Estudiante[]
     */
    public function buscar(string $criterio): array;
}
