<?php
declare(strict_types=1);

use App\Security\Escape;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'header.php';
?>
<?php if ($mensajeGeneral !== null): ?>
    <p class="form-error" role="alert"><?= Escape::html($mensajeGeneral) ?></p>
<?php endif; ?>

<dl>
    <dt>Estudiante</dt>
    <dd><?= Escape::html($solicitud->nombreEstudiante ?? '') ?>
        (<?= Escape::html($solicitud->documentoEstudiante) ?>)</dd>
    <dt>Asignatura</dt>
    <dd><?= Escape::html($solicitud->asignatura) ?></dd>
    <dt>Tipo de ajuste</dt>
    <dd><?= Escape::html($solicitud->tipoAjuste->value) ?></dd>
    <dt>Estado actual</dt>
    <dd><?= Escape::html($solicitud->estado->value) ?></dd>
</dl>

<?php if ($estadosPermitidos === []): ?>
    <p>Esta solicitud ya alcanzó un estado final y no admite más cambios.</p>
<?php else: ?>
    <form method="post" action="?ruta=solicitudes/cambiar-estado">
        <input type="hidden" name="_csrf" value="<?= Escape::html($tokenCsrf) ?>">
        <input type="hidden" name="id_solicitud" value="<?= Escape::html($solicitud->idSolicitud) ?>">

        <label for="estado">Nuevo estado</label>
        <select id="estado" name="estado" required
                aria-describedby="<?= isset($errores['estado']) ? 'estado-error' : 'estado-ayuda' ?>"
                <?= isset($errores['estado']) ? 'aria-invalid="true"' : '' ?>>
            <option value="">Seleccione un estado</option>
            <?php foreach ($estadosPermitidos as $estado): ?>
                <option value="<?= Escape::html($estado->value) ?>"
                    <?= $estadoSeleccionado === $estado->value ? 'selected' : '' ?>>
                    <?= Escape::html($estado->value) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <small id="estado-ayuda">Solo se muestran los estados permitidos para el estado actual.</small>
        <?php if (isset($errores['estado'])): ?>
            <p class="field-error" id="estado-error"><?= Escape::html($errores['estado']) ?></p>
        <?php endif; ?>

        <label for="motivo_rechazo">Motivo de rechazo</label>
        <textarea id="motivo_rechazo" name="motivo_rechazo" maxlength="500"
                  aria-describedby="motivo-ayuda<?= isset($errores['motivo_rechazo']) ? ' motivo-error' : '' ?>"
                  <?= isset($errores['motivo_rechazo']) ? 'aria-invalid="true"' : '' ?>><?= Escape::html($motivoRechazo) ?></textarea>
        <small id="motivo-ayuda">
            Obligatorio al seleccionar RECHAZADA; debe tener entre 10 y 500 caracteres después de quitar espacios externos.
        </small>
        <?php if (isset($errores['motivo_rechazo'])): ?>
            <p class="field-error" id="motivo-error">
                <?= Escape::html($errores['motivo_rechazo']) ?>
            </p>
        <?php endif; ?>

        <button type="submit">Guardar cambio de estado</button>
    </form>
<?php endif; ?>
<p><a href="?ruta=solicitudes/pendientes">Volver a solicitudes pendientes</a></p>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
