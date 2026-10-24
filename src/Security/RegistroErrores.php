<?php
declare(strict_types=1);

namespace App\Security;

use Throwable;

/**
 * Registra errores técnicos sin conservar valores enviados por el cliente.
 */
final class RegistroErrores
{
    /**
     * Recibe la ruta del archivo de log.
     *
     * @param string $rutaArchivo Ruta absoluta del registro de errores.
     */
    public function __construct(private readonly string $rutaArchivo)
    {
    }

    /**
     * Añade al log una línea con los datos técnicos permitidos.
     *
     * @param Throwable $error Excepción que se debe registrar.
     */
    public function registrar(Throwable $error): void
    {
        $directorio = dirname($this->rutaArchivo);
        if (!is_dir($directorio) && !@mkdir($directorio, 0770, true) && !is_dir($directorio)) {
            error_log('No se pudo crear el directorio del registro de errores.');
            return;
        }

        $entrada = [
            'fecha' => date(DATE_ATOM),
            'tipo' => $error::class,
            'mensaje' => $this->ocultarValoresDeEntrada($error->getMessage()),
            'archivo' => $error->getFile(),
            'linea' => $error->getLine(),
        ];
        $linea = json_encode(
            $entrada,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        );

        if ($linea === false
            || @file_put_contents($this->rutaArchivo, $linea . PHP_EOL, FILE_APPEND | LOCK_EX) === false
        ) {
            error_log('No se pudo escribir en el registro de errores de la aplicación.');
        }
    }

    /**
     * Sustituye valores recibidos en la petición antes de guardar el mensaje.
     */
    private function ocultarValoresDeEntrada(string $mensaje): string
    {
        $valores = [];
        foreach ([$_GET, $_POST, $_COOKIE, $_SESSION ?? []] as $datos) {
            $this->recopilarValores($datos, $valores);
        }
        usort($valores, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        return str_replace($valores, '[dato omitido]', $mensaje);
    }

    /**
     * Recopila valores escalares potencialmente sensibles de una entrada.
     *
     * @param array<mixed> $datos Valores de entrada.
     * @param list<string> $valores Lista de valores que se deben ocultar.
     */
    private function recopilarValores(array $datos, array &$valores): void
    {
        foreach ($datos as $valor) {
            if (is_array($valor)) {
                $this->recopilarValores($valor, $valores);
            } elseif (is_scalar($valor) && (string) $valor !== '') {
                $valores[] = (string) $valor;
            }
        }
    }
}
