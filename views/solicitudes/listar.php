<?php
declare(strict_types=1);

use App\Security\Escape;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'header.php';
$etiquetasTipoAjuste = [
    'TIEMPO_ADICIONAL' => 'Tiempo adicional',
    'MATERIAL_ACCESIBLE' => 'Material accesible',
    'INTERPRETE_LSP' => 'Intérprete LSP',
    'AULA_ACCESIBLE' => 'Aula accesible',
    'MOBILIARIO' => 'Mobiliario',
    'ACOMPANAMIENTO' => 'Acompañamiento',
];
?>
<?php if ($mensajeExito !== null): ?>
    <p class="notice" role="status"><strong>Éxito:</strong> <?= Escape::html($mensajeExito) ?></p>
<?php endif; ?>

<section aria-labelledby="filtro-estudiante-titulo">
    <h3 id="filtro-estudiante-titulo">Filtrar por estudiante</h3>
    <form class="filter-card" method="get" action="">
        <input type="hidden" name="ruta" value="solicitudes/pendientes">
        <label for="buscar_estudiante">Documento exacto o nombre parcial</label>
        <input id="buscar_estudiante" name="buscar_estudiante" type="search"
               value="<?= Escape::html($criterioEstudiante) ?>">
        <button class="button button--secondary" type="submit">Buscar estudiante</button>
    </form>
</section>

<section aria-labelledby="filtro-asignatura-titulo">
    <h3 id="filtro-asignatura-titulo">Filtrar por asignatura</h3>
    <form class="filter-card" method="get" action="">
        <input type="hidden" name="ruta" value="solicitudes/pendientes">
        <label for="buscar_asignatura">Nombre de asignatura</label>
        <input id="buscar_asignatura" name="buscar_asignatura" type="search"
               value="<?= Escape::html($asignatura) ?>">
        <button class="button button--secondary" type="submit">Buscar asignatura</button>
    </form>
</section>

<p><a href="?ruta=solicitudes/pendientes">Mostrar todas las solicitudes pendientes</a></p>

<?php if ($solicitudes === []): ?>
    <section class="empty-state notice" role="status">
        <h3>No encontramos solicitudes pendientes</h3>
        <p>Prueba con otro criterio o vuelve al listado completo.</p>
        <p><a href="?ruta=solicitudes/pendientes">Ver todas las solicitudes pendientes</a></p>
        <p><a href="?ruta=solicitudes/registrar">Registrar una solicitud</a></p>
    </section>
<?php else: ?>
    <table>
        <caption>Solicitudes pendientes</caption>
        <thead>
        <tr>
            <th scope="col">Documento</th>
            <th scope="col">Estudiante</th>
            <th scope="col">Asignatura</th>
            <th scope="col">Tipo de ajuste</th>
            <th scope="col">Estado</th>
            <th scope="col">Acción</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($solicitudes as $solicitud): ?>
            <tr>
                <td><?= Escape::html($solicitud->documentoEstudiante) ?></td>
                <td><?= Escape::html($solicitud->nombreEstudiante ?? '') ?></td>
                <td><?= Escape::html($solicitud->asignatura) ?></td>
                <td><?= Escape::html($etiquetasTipoAjuste[$solicitud->tipoAjuste->value]) ?></td>
                <td>
                    <span class="status-badge status-badge--<?= Escape::html(strtolower($solicitud->estado->value)) ?>">
                        <?= Escape::html(ucfirst(strtolower($solicitud->estado->value))) ?>
                    </span>
                </td>
                <td>
                    <?php if ($solicitud->idSolicitud !== null): ?>
                        <a href="?ruta=solicitudes/cambiar-estado&amp;id=<?= Escape::html($solicitud->idSolicitud) ?>">
                            Cambiar estado de solicitud <?= Escape::html($solicitud->idSolicitud) ?>
                        </a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
