<?php
declare(strict_types=1);

use App\Security\Escape;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Escape::html(($tituloPagina ?? 'Ajustes razonables') . ' · Ajustes razonables') ?></title>
    <link rel="stylesheet" href="css/estilos.css">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='12' fill='%23123047'/%3E%3Cpath d='M32 10v44M10 32h44' stroke='white' stroke-width='8'/%3E%3C/svg%3E" type="image/svg+xml">
</head>
<body>
<a class="skip-link" href="#main-content">Saltar al contenido</a>
<header class="site-header">
    <div class="site-header__inner">
        <h1>Ajustes razonables</h1>
        <?php $rutaActual = $_GET['ruta'] ?? 'estudiantes/registrar'; ?>
        <nav class="main-nav" aria-label="Navegación principal">
            <a href="?ruta=estudiantes/registrar"
                <?= $rutaActual === 'estudiantes/registrar' ? 'aria-current="page"' : '' ?>>
                Registrar estudiante
            </a>
            <a href="?ruta=solicitudes/registrar"
                <?= $rutaActual === 'solicitudes/registrar' ? 'aria-current="page"' : '' ?>>
                Registrar solicitud
            </a>
            <a href="?ruta=solicitudes/pendientes"
                <?= in_array($rutaActual, ['solicitudes/pendientes', 'solicitudes/cambiar-estado'], true)
                    ? 'aria-current="page"' : '' ?>>
                Solicitudes pendientes
            </a>
            <a href="?ruta=reportes/totales"
                <?= $rutaActual === 'reportes/totales' ? 'aria-current="page"' : '' ?>>
                Reporte de totales
            </a>
        </nav>
    </div>
</header>
<main id="main-content" class="page-content">
    <h2 class="page-title"><?= Escape::html($tituloPagina ?? 'Ajustes razonables') ?></h2>
