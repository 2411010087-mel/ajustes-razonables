<?php
declare(strict_types=1);

namespace App\Config;

use App\Exception\ConfiguracionException;

/**
 * Carga y valida la configuración de la aplicación desde .env.
 */
final class Config
{
    /**
     * Lee los parámetros requeridos desde el archivo .env de la raíz.
     *
     * @return array<string, string>
     * @throws ConfiguracionException Si falta el archivo o algún parámetro.
     */
    public static function cargar(): array
    {
        $ruta = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
        if (!is_file($ruta) || !is_readable($ruta)) {
            throw new ConfiguracionException('No se encontró un archivo .env legible.');
        }

        $lineas = file($ruta, FILE_IGNORE_NEW_LINES);
        if ($lineas === false) {
            throw new ConfiguracionException('No se pudo leer el archivo .env.');
        }

        $configuracion = [];
        foreach ($lineas as $linea) {
            $linea = trim($linea);
            if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
                continue;
            }

            [$clave, $valor] = explode('=', $linea, 2);
            $clave = trim($clave);
            $valor = trim($valor);
            if (strlen($valor) >= 2 && (
                ($valor[0] === '"' && str_ends_with($valor, '"'))
                || ($valor[0] === "'" && str_ends_with($valor, "'"))
            )) {
                $valor = substr($valor, 1, -1);
            }
            $configuracion[$clave] = $valor;
        }

        $requeridos = ['DB_HOST', 'DB_NAME', 'DB_NAME_TEST', 'DB_USER', 'DB_PASS'];
        $faltantes = [];
        foreach ($requeridos as $clave) {
            if (!array_key_exists($clave, $configuracion)) {
                $faltantes[] = $clave;
            }
        }
        if ($faltantes !== []) {
            throw new ConfiguracionException(
                'Faltan parámetros requeridos en .env: ' . implode(', ', $faltantes) . '.'
            );
        }

        return $configuracion;
    }
}
