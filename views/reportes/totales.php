<?php
declare(strict_types=1);

use App\Security\Escape;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'header.php';
?>
<section class="total-card" aria-label="Total general">
    <h3>Total general de solicitudes</h3>
    <p class="total-card__value"><?= Escape::html($totalSolicitudes) ?></p>
</section>

<div class="report-tables">
<table class="data-table">
    <caption>Total de solicitudes por tipo de ajuste</caption>
    <thead>
    <tr>
        <th scope="col">Tipo de ajuste</th>
        <th scope="col">Total de solicitudes</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($totalesPorTipoAjuste as $tipoAjuste => $total): ?>
        <tr>
            <td><?= Escape::html($tipoAjuste) ?></td>
            <td class="numeric"><?= Escape::html($total) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<table class="data-table">
    <caption>Total de solicitudes por estado</caption>
    <thead>
    <tr>
        <th scope="col">Estado</th>
        <th scope="col">Total de solicitudes</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($totalesPorEstado as $estado => $total): ?>
        <tr>
            <td><?= Escape::html($estado) ?></td>
            <td class="numeric"><?= Escape::html($total) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
