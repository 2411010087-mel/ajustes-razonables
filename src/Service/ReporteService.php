<?php
declare(strict_types=1);

namespace App\Service;

use App\Repository\SolicitudRepository;

/**
 * Proporciona los totales de solicitudes para el reporte en pantalla.
 */
final class ReporteService
{
    /**
     * Recibe el repositorio que consulta las solicitudes.
     */
    public function __construct(private readonly SolicitudRepository $solicitudRepository)
    {
    }

    /**
     * Devuelve los totales por tipo de ajuste, incluidos los valores cero.
     *
     * @return array<string, int>
     */
    public function totalesPorTipoAjuste(): array
    {
        return $this->solicitudRepository->contarPorTipoAjuste();
    }

    /**
     * Devuelve los totales por estado, incluidos los valores cero.
     *
     * @return array<string, int>
     */
    public function totalesPorEstado(): array
    {
        return $this->solicitudRepository->contarPorEstado();
    }

    /**
     * Devuelve el número total de solicitudes registradas.
     */
    public function totalSolicitudes(): int
    {
        return $this->solicitudRepository->contarTotalSolicitudes();
    }
}
