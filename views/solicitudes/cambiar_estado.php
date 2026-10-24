<?php
declare(strict_types=1);

use App\Security\Escape;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'header.php';
?>
<?php if ($mensajeGeneral !== null): ?>
    <p class="form-error" role="alert"><strong>Error:</strong> <?= Escape::html($mensajeGeneral) ?></p>
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
    <dd><span class="status-badge status-badge--<?= Escape::html(strtolower($solicitud->estado->value)) ?>">
        <?= Escape::html(ucfirst(strtolower($solicitud->estado->value))) ?>
    </span></dd>
</dl>

<?php if ($estadosPermitidos === []): ?>
    <p class="notice" role="status">Estado final: esta solicitud ya no admite más cambios.</p>
<?php else: ?>
    <form class="form-card" method="post" action="?ruta=solicitudes/cambiar-estado">
        <h3 class="form-card__title">Actualizar estado de la solicitud</h3>
        <input type="hidden" name="_csrf" value="<?= Escape::html($tokenCsrf) ?>">
        <input type="hidden" name="id_solicitud" value="<?= Escape::html($solicitud->idSolicitud) ?>">

        <div class="form-grid">
            <div class="form-field">
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
            </div>

            <div class="form-field">
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
            </div>
        </div>

        <div class="form-actions">
            <button class="button button--primary" type="submit">Guardar cambio de estado</button>
        </div>
    </form>
<?php endif; ?>
<p><a href="?ruta=solicitudes/pendientes">Volver a solicitudes pendientes</a></p>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
