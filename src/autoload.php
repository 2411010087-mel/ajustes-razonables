<?php
declare(strict_types=1);

spl_autoload_register(static function (string $clase): void {
    $prefijo = 'App\\';
    if (!str_starts_with($clase, $prefijo)) {
        return;
    }

    $ruta = __DIR__ . DIRECTORY_SEPARATOR
        . str_replace('\\', DIRECTORY_SEPARATOR, substr($clase, strlen($prefijo)))
        . '.php';
    if (is_file($ruta)) {
        require_once $ruta;
    }
});
