<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

/**
 * Construye conexiones PDO para la base de datos de la aplicación o de pruebas.
 */
final class Conexion
{
    /**
     * Abre una conexión PDO usando la configuración ya validada.
     *
     * @param array<string, string> $configuracion Parámetros cargados desde .env.
     */
    public static function obtener(array $configuracion, bool $pruebas = false): PDO
    {
        $baseDatos = $pruebas ? $configuracion['DB_NAME_TEST'] : $configuracion['DB_NAME'];
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $configuracion['DB_HOST'],
            $baseDatos
        );

        return new PDO(
            $dsn,
            $configuracion['DB_USER'],
            $configuracion['DB_PASS'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]
        );
    }
}
