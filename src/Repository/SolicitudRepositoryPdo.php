<?php
declare(strict_types=1);

namespace App\Repository;

use App\Model\Estado;
use App\Model\Solicitud;
use App\Model\TipoAjuste;
use PDO;

/**
 * Implementa consultas preparadas para solicitudes de ajuste razonable.
 */
final class SolicitudRepositoryPdo implements SolicitudRepository
{
    /**
     * Recibe la conexión PDO utilizada por el repositorio.
     */
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Guarda una solicitud y devuelve su identificador generado.
     */
    public function guardar(Solicitud $solicitud): int
    {
        $sentencia = $this->pdo->prepare(
            'INSERT INTO solicitud (documento_estudiante, id_tipo_ajuste, asignatura)
             VALUES (:documento_estudiante, :id_tipo_ajuste, :asignatura)'
        );
        $sentencia->execute([
            'documento_estudiante' => $solicitud->documentoEstudiante,
            'id_tipo_ajuste' => $solicitud->idTipoAjuste,
            'asignatura' => $solicitud->asignatura,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Busca una solicitud por su identificador.
     */
    public function buscarPorId(int $idSolicitud): ?Solicitud
    {
        $sentencia = $this->pdo->prepare(
            'SELECT s.id_solicitud, s.documento_estudiante, s.id_tipo_ajuste,
                    t.nombre AS tipo_ajuste, s.asignatura, s.estado,
                    s.motivo_rechazo, s.created_at, s.updated_at,
                    e.nombre AS nombre_estudiante
             FROM solicitud s
             INNER JOIN tipo_ajuste t ON t.id_tipo_ajuste = s.id_tipo_ajuste
             INNER JOIN estudiante e ON e.documento = s.documento_estudiante
             WHERE s.id_solicitud = :id_solicitud'
        );
        $sentencia->execute(['id_solicitud' => $idSolicitud]);
        $fila = $sentencia->fetch();

        return $fila === false ? null : $this->hidratar($fila);
    }

    /**
     * Actualiza el estado actual y el motivo de rechazo.
     */
    public function actualizarEstado(
        int $idSolicitud,
        Estado $estado,
        ?string $motivoRechazo
    ): void {
        $sentencia = $this->pdo->prepare(
            'UPDATE solicitud
             SET estado = :estado, motivo_rechazo = :motivo_rechazo
             WHERE id_solicitud = :id_solicitud'
        );
        $sentencia->execute([
            'estado' => $estado->value,
            'motivo_rechazo' => $motivoRechazo,
            'id_solicitud' => $idSolicitud,
        ]);
    }

    /**
     * Lista las solicitudes que continúan pendientes.
     *
     * @return Solicitud[]
     */
    public function listarPendientes(): array
    {
        return $this->consultarSolicitudes(
            "s.estado NOT IN ('IMPLEMENTADA', 'RECHAZADA')",
            []
        );
    }

    /**
     * Filtra solicitudes pendientes por documento o nombre de estudiante.
     *
     * @return Solicitud[]
     */
    public function filtrarPorEstudiante(string $criterio): array
    {
        return $this->consultarSolicitudes(
            "s.estado NOT IN ('IMPLEMENTADA', 'RECHAZADA')
             AND (e.documento = :documento OR e.nombre LIKE :nombre)",
            [
                'documento' => $criterio,
                'nombre' => '%' . $criterio . '%',
            ]
        );
    }

    /**
     * Filtra solicitudes pendientes por asignatura.
     *
     * @return Solicitud[]
     */
    public function filtrarPorAsignatura(string $asignatura): array
    {
        return $this->consultarSolicitudes(
            "s.estado NOT IN ('IMPLEMENTADA', 'RECHAZADA')
             AND s.asignatura LIKE :asignatura",
            ['asignatura' => '%' . $asignatura . '%']
        );
    }

    /**
     * Cuenta solicitudes por cada tipo de ajuste, incluidos los tipos sin registros.
     *
     * @return array<string, int>
     */
    public function contarPorTipoAjuste(): array
    {
        $totales = [];
        foreach (TipoAjuste::cases() as $tipo) {
            $totales[$tipo->value] = 0;
        }

        $sentencia = $this->pdo->query(
            'SELECT t.nombre, COUNT(s.id_solicitud) AS total
             FROM tipo_ajuste t
             LEFT JOIN solicitud s ON s.id_tipo_ajuste = t.id_tipo_ajuste
             GROUP BY t.id_tipo_ajuste, t.nombre'
        );
        foreach ($sentencia->fetchAll() as $fila) {
            $totales[(string) $fila['nombre']] = (int) $fila['total'];
        }

        return $totales;
    }

    /**
     * Cuenta solicitudes por cada estado, incluidos los estados sin registros.
     *
     * @return array<string, int>
     */
    public function contarPorEstado(): array
    {
        $totales = [];
        foreach (Estado::cases() as $estado) {
            $totales[$estado->value] = 0;
        }

        $sentencia = $this->pdo->query(
            'SELECT estado, COUNT(*) AS total
             FROM solicitud
             GROUP BY estado'
        );
        foreach ($sentencia->fetchAll() as $fila) {
            $totales[(string) $fila['estado']] = (int) $fila['total'];
        }

        return $totales;
    }

    /**
     * Ejecuta una consulta de solicitudes pendientes con los parámetros preparados.
     *
     * @param array<string, string> $parametros Parámetros de búsqueda.
     * @return Solicitud[]
     */
    private function consultarSolicitudes(string $condicion, array $parametros): array
    {
        $sentencia = $this->pdo->prepare(
            'SELECT s.id_solicitud, s.documento_estudiante, s.id_tipo_ajuste,
                    t.nombre AS tipo_ajuste, s.asignatura, s.estado,
                    s.motivo_rechazo, s.created_at, s.updated_at,
                    e.nombre AS nombre_estudiante
             FROM solicitud s
             INNER JOIN tipo_ajuste t ON t.id_tipo_ajuste = s.id_tipo_ajuste
             INNER JOIN estudiante e ON e.documento = s.documento_estudiante
             WHERE ' . $condicion . '
             ORDER BY s.created_at DESC, s.id_solicitud DESC'
        );
        $sentencia->execute($parametros);

        $solicitudes = [];
        foreach ($sentencia->fetchAll() as $fila) {
            $solicitudes[] = $this->hidratar($fila);
        }

        return $solicitudes;
    }

    /**
     * Convierte una fila de la base de datos a un modelo de solicitud.
     *
     * @param array<string, mixed> $fila
     */
    private function hidratar(array $fila): Solicitud
    {
        return new Solicitud(
            (int) $fila['id_solicitud'],
            (string) $fila['documento_estudiante'],
            (int) $fila['id_tipo_ajuste'],
            TipoAjuste::from((string) $fila['tipo_ajuste']),
            (string) $fila['asignatura'],
            Estado::from((string) $fila['estado']),
            $fila['motivo_rechazo'] === null ? null : (string) $fila['motivo_rechazo'],
            (string) $fila['created_at'],
            (string) $fila['updated_at'],
            isset($fila['nombre_estudiante']) ? (string) $fila['nombre_estudiante'] : null
        );
    }
}
