<?php
declare(strict_types=1);

use App\Model\TipoDiscapacidad;
use App\Model\TipoDocumentoAcreditacion;
use App\Security\Escape;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'header.php';
$errores = $errores ?? [];
$datos = $datos ?? [];
?>
<?php if ($mensajeExito !== null): ?>
    <p class="notice" role="status"><?= Escape::html($mensajeExito) ?></p>
<?php endif; ?>
<?php if ($mensajeGeneral !== null): ?>
    <p class="form-error" role="alert"><?= Escape::html($mensajeGeneral) ?></p>
<?php endif; ?>

<form method="post" action="?ruta=estudiantes/registrar">
    <input type="hidden" name="_csrf" value="<?= Escape::html($tokenCsrf) ?>">

    <label for="documento">Documento de identidad (8 dígitos)</label>
    <input id="documento" name="documento" type="text" inputmode="numeric"
           autocomplete="off" maxlength="8" pattern="[0-9]{8}" required
           value="<?= Escape::html($datos['documento'] ?? '') ?>"
           aria-describedby="documento-ayuda<?= isset($errores['documento']) ? ' documento-error' : '' ?>"
           <?= isset($errores['documento']) ? 'aria-invalid="true"' : '' ?>>
    <small id="documento-ayuda">Ingrese exactamente 8 dígitos.</small>
    <?php if (isset($errores['documento'])): ?>
        <p class="field-error" id="documento-error"><?= Escape::html($errores['documento']) ?></p>
    <?php endif; ?>

    <label for="nombre">Nombre completo</label>
    <input id="nombre" name="nombre" type="text" maxlength="100" required
           value="<?= Escape::html($datos['nombre'] ?? '') ?>"
           aria-describedby="nombre-ayuda<?= isset($errores['nombre']) ? ' nombre-error' : '' ?>"
           <?= isset($errores['nombre']) ? 'aria-invalid="true"' : '' ?>>
    <small id="nombre-ayuda">Máximo 100 caracteres.</small>
    <?php if (isset($errores['nombre'])): ?>
        <p class="field-error" id="nombre-error"><?= Escape::html($errores['nombre']) ?></p>
    <?php endif; ?>

    <label for="carrera">Carrera</label>
    <input id="carrera" name="carrera" type="text" maxlength="50" required
           value="<?= Escape::html($datos['carrera'] ?? '') ?>"
           aria-describedby="carrera-ayuda<?= isset($errores['carrera']) ? ' carrera-error' : '' ?>"
           <?= isset($errores['carrera']) ? 'aria-invalid="true"' : '' ?>>
    <small id="carrera-ayuda">Máximo 50 caracteres.</small>
    <?php if (isset($errores['carrera'])): ?>
        <p class="field-error" id="carrera-error"><?= Escape::html($errores['carrera']) ?></p>
    <?php endif; ?>

    <label for="tipo_discapacidad">Tipo de discapacidad</label>
    <select id="tipo_discapacidad" name="tipo_discapacidad" required
            aria-describedby="<?= isset($errores['tipo_discapacidad']) ? 'discapacidad-error' : 'discapacidad-ayuda' ?>"
            <?= isset($errores['tipo_discapacidad']) ? 'aria-invalid="true"' : '' ?>>
        <option value="">Seleccione una opción</option>
        <?php foreach (TipoDiscapacidad::cases() as $tipo): ?>
            <option value="<?= Escape::html($tipo->value) ?>"
                <?= ($datos['tipo_discapacidad'] ?? '') === $tipo->value ? 'selected' : '' ?>>
                <?= Escape::html($tipo->value) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <small id="discapacidad-ayuda">Seleccione una de las opciones disponibles.</small>
    <?php if (isset($errores['tipo_discapacidad'])): ?>
        <p class="field-error" id="discapacidad-error">
            <?= Escape::html($errores['tipo_discapacidad']) ?>
        </p>
    <?php endif; ?>

    <label for="tipo_documento_acreditacion">Tipo de documento de acreditación</label>
    <select id="tipo_documento_acreditacion" name="tipo_documento_acreditacion" required
            aria-describedby="<?= isset($errores['tipo_documento_acreditacion']) ? 'acreditacion-tipo-error' : 'acreditacion-tipo-ayuda' ?>"
            <?= isset($errores['tipo_documento_acreditacion']) ? 'aria-invalid="true"' : '' ?>>
        <option value="">Seleccione una opción</option>
        <?php foreach (TipoDocumentoAcreditacion::cases() as $tipo): ?>
            <option value="<?= Escape::html($tipo->value) ?>"
                <?= ($datos['tipo_documento_acreditacion'] ?? '') === $tipo->value ? 'selected' : '' ?>>
                <?= Escape::html($tipo->value) ?>
            </option>
        <?php endforeach; ?>
    </select>
    <small id="acreditacion-tipo-ayuda">Seleccione CONADIS o CERT-MED.</small>
    <?php if (isset($errores['tipo_documento_acreditacion'])): ?>
        <p class="field-error" id="acreditacion-tipo-error">
            <?= Escape::html($errores['tipo_documento_acreditacion']) ?>
        </p>
    <?php endif; ?>

    <label for="numero_documento_acreditacion">Número del documento de acreditación</label>
    <input id="numero_documento_acreditacion" name="numero_documento_acreditacion"
           type="text" maxlength="50" required
           value="<?= Escape::html($datos['numero_documento_acreditacion'] ?? '') ?>"
           aria-describedby="acreditacion-numero-ayuda<?= isset($errores['numero_documento_acreditacion']) ? ' acreditacion-numero-error' : '' ?>"
           <?= isset($errores['numero_documento_acreditacion']) ? 'aria-invalid="true"' : '' ?>>
    <small id="acreditacion-numero-ayuda">Máximo 50 caracteres.</small>
    <?php if (isset($errores['numero_documento_acreditacion'])): ?>
        <p class="field-error" id="acreditacion-numero-error">
            <?= Escape::html($errores['numero_documento_acreditacion']) ?>
        </p>
    <?php endif; ?>

    <button type="submit">Registrar estudiante</button>
</form>
<?php require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'footer.php'; ?>
