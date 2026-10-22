<?php
declare(strict_types=1);

use App\Security\Escape;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Escape::html($tituloPagina ?? 'Ajustes razonables') ?></title>
    <style>
        :root {
            color-scheme: light;
            font-family: Arial, Helvetica, sans-serif;
            color: #202124;
            background: #fff;
        }
        body { margin: 0 auto; max-width: 58rem; padding: 1rem; line-height: 1.5; }
        nav { display: flex; flex-wrap: wrap; gap: 1rem; padding: 1rem 0; }
        a { color: #064b82; }
        a:focus, button:focus, input:focus, select:focus {
            outline: 3px solid #704000;
            outline-offset: 2px;
        }
        label { display: block; font-weight: 700; margin-top: 1rem; }
        input, select, button { box-sizing: border-box; font: inherit; margin-top: .25rem; padding: .5rem; }
        input, select { display: block; max-width: 32rem; width: 100%; }
        button { cursor: pointer; margin-top: 1.25rem; }
        .error, [aria-invalid="true"] { color: #8b1e1e; }
        .notice { border-left: .3rem solid #176b37; padding: .5rem 1rem; }
        .form-error { border-left: .3rem solid #8b1e1e; padding: .5rem 1rem; }
        .field-error { color: #8b1e1e; font-weight: 700; }
        footer { border-top: 1px solid #767676; margin-top: 2rem; padding-top: 1rem; }
    </style>
</head>
<body>
<header>
    <h1>Ajustes razonables</h1>
    <nav aria-label="Navegación principal">
        <a href="?ruta=estudiantes/registrar">Registrar estudiante</a>
        <a href="?ruta=solicitudes/registrar">Registrar solicitud</a>
    </nav>
</header>
<main>
    <h2><?= Escape::html($tituloPagina ?? 'Ajustes razonables') ?></h2>
