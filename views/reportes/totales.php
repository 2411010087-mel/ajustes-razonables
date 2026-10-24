<?php
declare(strict_types=1);

use App\Security\Escape;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'header.php';
?>
<section class="total-card" aria-label="Total general">
    <h3>Total general de solicitudes</h3>
    <p class="total-card__value"><?= Escape::html($totalSolicitudes) ?></p>
</section>

<p class="report-issue-date">Fecha de emisión para el reporte semestral:
    <time datetime="<?= Escape::html(date('Y-m-d')) ?>"><?= Escape::html(date('d/m/Y')) ?></time>
</p>

<?php if ($totalSolicitudes === 0): ?>
    <section class="empty-state notice" role="status">
        <h3>Aún no hay solicitudes registradas</h3>
        <p>Cuando registres solicitudes, aquí aparecerán los totales por tipo y estado.</p>
        <p><a href="?ruta=solicitudes/registrar">Registrar una solicitud</a></p>
    </section>
<?php endif; ?>
<div class="report-tables report-print-area">
<table class="data-table">
    <caption>Total de solicitudes por tipo de ajuste</caption>
    <thead>
    <tr>
        <th scope="col">Tipo de ajuste</th>
        <th scope="col" class="numeric-column">Total</th>
        <th scope="col">Porcentaje</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($totalesPorTipoAjuste as $tipoAjuste => $total): ?>
        <?php $porcentaje = $totalSolicitudes > 0 ? ($total / $totalSolicitudes) * 100 : 0.0; ?>
        <tr>
            <td><?= Escape::html(ucfirst(strtolower(str_replace('_', ' ', $tipoAjuste)))) ?></td>
            <td class="numeric"><?= Escape::html($total) ?></td>
            <td>
                <div class="proportion" aria-label="<?= Escape::html(number_format($porcentaje, 1)) ?> por ciento">
                    <span class="proportion__bar" aria-hidden="true"
                          style="--proporcion: <?= Escape::html(number_format($porcentaje, 2, '.', '')) ?>%"></span>
                    <span class="proportion__value"><?= Escape::html(number_format($porcentaje, 1)) ?>%</span>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<table class="data-table">
    <caption>Total de solicitudes por estado</caption>
    <thead>
    <tr>
        <th scope="col">Estado</th>
        <th scope="col" class="numeric-column">Total</th>
        <th scope="col">Porcentaje</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($totalesPorEstado as $estado => $total): ?>
        <?php $porcentaje = $totalSolicitudes > 0 ? ($total / $totalSolicitudes) * 100 : 0.0; ?>
        <tr>
            <td>
                <span class="status-badge status-badge--<?= Escape::html(strtolower($estado)) ?>">
                    <?= Escape::html(ucfirst(strtolower($estado))) ?>
                </span>
            </td>
            <td class="numeric"><?= Escape::html($total) ?></td>
            <td>
                <div class="proportion" aria-label="<?= Escape::html(number_format($porcentaje, 1)) ?> por ciento">
                    <span class="proportion__bar" aria-hidden="true"
                          style="--proporcion: <?= Escape::html(number_format($porcentaje, 2, '.', '')) ?>%"></span>
                    <span class="proportion__value"><?= Escape::html(number_format($porcentaje, 1)) ?>%</span>
                </div>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
