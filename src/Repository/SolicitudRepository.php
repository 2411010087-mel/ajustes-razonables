<?php
declare(strict_types=1);

namespace App\Repository;

use App\Model\Estado;
use App\Model\Solicitud;

/**
 * Contrato de persistencia y consulta de solicitudes.
 */
interface SolicitudRepository
{
    /**
     * Guarda una solicitud y devuelve su identificador generado.
     */
    public function guardar(Solicitud $solicitud): int;

    /**
     * Busca una solicitud por su identificador.
     */
    public function buscarPorId(int $idSolicitud): ?Solicitud;

    /**
     * Actualiza el estado actual y el motivo de rechazo.
     */
    public function actualizarEstado(int $idSolicitud, Estado $estado, ?string $motivoRechazo): void;

    /**
     * Lista las solicitudes que continúan pendientes.
     *
     * @return Solicitud[]
     */
    public function listarPendientes(): array;

    /**
     * Filtra solicitudes pendientes por documento o nombre de estudiante.
     *
     * @return Solicitud[]
     */
    public function filtrarPorEstudiante(string $criterio): array;

    /**
     * Filtra solicitudes pendientes por asignatura.
     *
     * @return Solicitud[]
     */
    public function filtrarPorAsignatura(string $asignatura): array;

    /**
     * Cuenta solicitudes por cada tipo de ajuste.
     *
     * @return array<string, int>
     */
    public function contarPorTipoAjuste(): array;

    /**
     * Cuenta solicitudes por cada estado.
     *
     * @return array<string, int>
     */
    public function contarPorEstado(): array;

    /**
     * Cuenta todas las solicitudes registradas.
     */
    public function contarTotalSolicitudes(): int;
}
