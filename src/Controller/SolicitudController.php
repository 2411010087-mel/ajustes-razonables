<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\ValidacionException;
use App\Security\Csrf;
use App\Service\SolicitudService;

/**
 * Presenta y procesa el registro de solicitudes de ajuste razonable.
 */
final class SolicitudController
{
    /**
     * Recibe el servicio que valida y registra solicitudes.
     */
    public function __construct(private readonly SolicitudService $service)
    {
    }

    /**
     * Muestra el formulario o procesa su envío con protección CSRF.
     */
    public function registrar(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->procesarRegistro();
            return;
        }

        $datos = [];
        $errores = [];
        $mensajeGeneral = null;
        $mensajeExito = $this->consumirMensajeExito();
        $tokenCsrf = Csrf::generar();
        $tituloPagina = 'Registrar solicitud';
        require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'solicitudes' . DIRECTORY_SEPARATOR . 'registrar.php';
    }

    /**
     * Valida CSRF, delega el registro y muestra los errores del formulario.
     */
    private function procesarRegistro(): void
    {
        if (!isset($_POST['_csrf']) || !is_string($_POST['_csrf'])
            || !Csrf::verificar($_POST['_csrf'])) {
            http_response_code(400);
            $tituloPagina = 'Solicitud rechazada';
            $mensaje = 'No se procesó el formulario porque el token de seguridad falta o no es válido.';
            require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR . 'error.php';
            return;
        }

        $datos = [
            'documento_estudiante' => $this->valorPost('documento_estudiante'),
            'asignatura' => $this->valorPost('asignatura'),
            'tipo_ajuste' => $this->valorPost('tipo_ajuste'),
        ];

        try {
            $this->service->registrar(
                $datos['documento_estudiante'],
                $datos['asignatura'],
                $datos['tipo_ajuste']
            );
            $_SESSION['_mensaje_exito'] = 'La solicitud se registró correctamente con estado RECIBIDA.';
            header('Location: ?ruta=solicitudes/registrar', true, 303);
            return;
        } catch (ValidacionException $e) {
            $errores = $e->errores;
            $mensajeGeneral = $e->getMessage();
        }

        $mensajeExito = null;
        $tokenCsrf = Csrf::generar();
        $tituloPagina = 'Registrar solicitud';
        require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'solicitudes' . DIRECTORY_SEPARATOR . 'registrar.php';
    }

    /**
     * Obtiene un campo escalar del formulario sin aceptar estructuras inesperadas.
     */
    private function valorPost(string $campo): string
    {
        $valor = $_POST[$campo] ?? '';
        return is_string($valor) ? $valor : '';
    }

    /**
     * Consume el mensaje de confirmación guardado tras un registro exitoso.
     */
    private function consumirMensajeExito(): ?string
    {
        $mensaje = $_SESSION['_mensaje_exito'] ?? null;
        unset($_SESSION['_mensaje_exito']);

        return is_string($mensaje) ? $mensaje : null;
    }
}
