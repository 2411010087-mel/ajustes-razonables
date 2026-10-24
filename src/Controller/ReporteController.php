<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\ReporteService;

/**
 * Presenta en pantalla los totales de solicitudes.
 */
final class ReporteController
{
    /**
     * Recibe el servicio que calcula los totales del reporte.
     */
    public function __construct(private readonly ReporteService $service)
    {
    }

    /**
     * Obtiene y presenta los totales por tipo, estado y el total general.
     */
    public function totales(): void
    {
        $totalesPorTipoAjuste = $this->service->totalesPorTipoAjuste();
        $totalesPorEstado = $this->service->totalesPorEstado();
        $totalSolicitudes = $this->service->totalSolicitudes();
        $tituloPagina = 'Reporte de totales';

        require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'reportes' . DIRECTORY_SEPARATOR . 'totales.php';
    }
}
