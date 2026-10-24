<?php
declare(strict_types=1);

use App\Model\TipoAjuste;
use App\Security\Escape;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'header.php';
$errores = $errores ?? [];
$datos = $datos ?? [];
?>
<?php if ($mensajeExito !== null): ?>
    <p class="notice" role="status"><strong>Éxito:</strong> <?= Escape::html($mensajeExito) ?></p>
<?php endif; ?>
<?php if ($mensajeGeneral !== null): ?>
    <p class="form-error" role="alert"><strong>Error:</strong> <?= Escape::html($mensajeGeneral) ?></p>
<?php endif; ?>

<form class="form-card" method="post" action="?ruta=solicitudes/registrar">
    <input type="hidden" name="_csrf" value="<?= Escape::html($tokenCsrf) ?>">

    <label for="documento_estudiante">Documento del estudiante (8 dígitos)</label>
    <input id="documento_estudiante" name="documento_estudiante" type="text"
           inputmode="numeric" autocomplete="off" maxlength="8" pattern="[0-9]{8}" required
           value="<?= Escape::html($datos['documento_estudiante'] ?? '') ?>"
           aria-describedby="documento-estudiante-ayuda<?= isset($errores['documento_estudiante']) ? ' documento-estudiante-error' : '' ?>"
           <?= isset($errores['documento_estudiante']) ? 'aria-invalid="true"' : '' ?>>
    <small id="documento-estudiante-ayuda">Ingrese el documento de identidad del estudiante registrado.</small>
    <?php if (isset($errores['documento_estudiante'])): ?>
        <p class="field-error" id="documento-estudiante-error">
            <?= Escape::html($errores['documento_estudiante']) ?>
        </p>
    <?php endif; ?>

    <label for="asignatura">Asignatura</label>
    <input id="asignatura" name="asignatura" type="text" maxlength="100" required
           value="<?= Escape::html($datos['asignatura'] ?? '') ?>"
           aria-describedby="asignatura-ayuda<?= isset($errores['asignatura']) ? ' asignatura-error' : '' ?>"
           <?= isset($errores['asignatura']) ? 'aria-invalid="true"' : '' ?>>
    <small id="asignatura-ayuda">Ingrese la asignatura para la que solicita el ajuste.</small>
    <?php if (isset($errores['asignatura'])): ?>
        <p class="field-error" id="asignatura-error"><?= Escape::html($errores['asignatura']) ?></p>
    <?php endif; ?>

    <label for="tipo_ajuste">Tipo de ajuste</label>
    <select id="tipo_ajuste" name="tipo_ajuste" required
            aria-describedby="<?= isset($errores['tipo_ajuste']) ? 'tipo-ajuste-error' : 'tipo-ajuste-ayuda' ?>"
            <?= isset($errores['tipo_ajuste']) ? 'aria-invalid="true"' : '' ?>>
        <option value="">Seleccione una opción</option>
        <?php foreach (TipoAjuste::cases() as $tipo): ?>
            <option value="<?= Escape::html($tipo->value) ?>"
                <?= ($datos['tipo_ajuste'] ?? '') === $tipo->value ? 'selected' : '' ?>>
                <?= Escape::html($tipo->value) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <small id="tipo-ajuste-ayuda">Seleccione uno de los tipos de ajuste disponibles.</small>
    <?php if (isset($errores['tipo_ajuste'])): ?>
        <p class="field-error" id="tipo-ajuste-error"><?= Escape::html($errores['tipo_ajuste']) ?></p>
    <?php endif; ?>

    <div class="form-actions">
        <button class="button button--primary" type="submit">Registrar solicitud</button>
    </div>
</form>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
