<?php
declare(strict_types=1);

use App\Security\Escape;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'header.php';
?>
<p><strong>Total general de solicitudes:</strong>
    <?= Escape::html($totalSolicitudes) ?>
</p>

<table>
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
            <td><?= Escape::html($total) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<table>
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
            <td><?= Escape::html($total) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
