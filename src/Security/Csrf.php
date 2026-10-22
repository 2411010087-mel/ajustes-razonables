<?php
declare(strict_types=1);

namespace App\Security;

/**
 * Genera y verifica tokens de protección CSRF para la sesión actual.
 */
final class Csrf
{
    /**
     * Obtiene el token CSRF de la sesión o crea uno criptográficamente seguro.
     */
    public static function generar(): string
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new \RuntimeException('La sesión debe iniciarse antes de generar el token CSRF.');
        }

        if (!isset($_SESSION['_csrf_token']) || !is_string($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }

    /**
     * Comprueba que el token recibido coincide con el token de la sesión.
     */
    public static function verificar(string $token): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['_csrf_token'])) {
            return false;
        }

        $tokenSesion = $_SESSION['_csrf_token'];
        return is_string($tokenSesion) && hash_equals($tokenSesion, $token);
    }
}
