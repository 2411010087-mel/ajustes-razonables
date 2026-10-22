<?php
declare(strict_types=1);

namespace App\Controller;

use App\Exception\ValidacionException;
use App\Model\Estado;
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
     * Muestra las solicitudes pendientes, opcionalmente filtradas.
     */
    public function listarPendientes(): void
    {
        $criterioEstudiante = $this->valorGet('buscar_estudiante');
        $asignatura = $this->valorGet('buscar_asignatura');

        if ($criterioEstudiante !== '') {
            $solicitudes = $this->service->filtrarPorEstudiante($criterioEstudiante);
        } elseif ($asignatura !== '') {
            $solicitudes = $this->service->filtrarPorAsignatura($asignatura);
        } else {
            $solicitudes = $this->service->listarPendientes();
        }

        $mensajeExito = $this->consumirMensajeExito();
        $tituloPagina = 'Solicitudes pendientes';
        require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'solicitudes' . DIRECTORY_SEPARATOR . 'listar.php';
    }

    /**
     * Muestra el formulario con las transiciones válidas para la solicitud.
     */
    public function mostrarCambioEstado(int $idSolicitud): void
    {
        $solicitud = $this->service->buscarPorId($idSolicitud);
        if ($solicitud === null) {
            http_response_code(404);
            $tituloPagina = 'Solicitud no encontrada';
            $mensaje = 'No se encontró la solicitud indicada.';
            require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR . 'error.php';
            return;
        }

        $errores = [];
        $mensajeGeneral = null;
        $estadoSeleccionado = '';
        $motivoRechazo = '';
        $tokenCsrf = Csrf::generar();
        $estadosPermitidos = $this->estadosPermitidos($solicitud->estado);
        $tituloPagina = 'Cambiar estado de solicitud';
        require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'solicitudes' . DIRECTORY_SEPARATOR . 'cambiar_estado.php';
    }

    /**
     * Procesa el cambio de estado tras verificar CSRF y los datos recibidos.
     */
    public function cambiarEstado(): void
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

        $idRecibido = $_POST['id_solicitud'] ?? null;
        $idSolicitud = is_string($idRecibido) ? filter_var($idRecibido, FILTER_VALIDATE_INT) : false;
        if (!is_int($idSolicitud) || $idSolicitud < 1) {
            http_response_code(400);
            $tituloPagina = 'Solicitud inválida';
            $mensaje = 'El identificador de solicitud no es válido.';
            require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR . 'error.php';
            return;
        }

        $solicitud = $this->service->buscarPorId($idSolicitud);
        if ($solicitud === null) {
            http_response_code(404);
            $tituloPagina = 'Solicitud no encontrada';
            $mensaje = 'No se encontró la solicitud indicada.';
            require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views'
                . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR . 'error.php';
            return;
        }

        $valorEstado = $this->valorPost('estado');
        $nuevoEstado = Estado::tryFrom($valorEstado);
        $motivo = $this->valorPost('motivo_rechazo');
        $estadoSeleccionado = $valorEstado;
        $motivoRechazo = $motivo;
        $errores = [];
        $mensajeGeneral = null;

        try {
            if ($nuevoEstado === null) {
                throw new ValidacionException(
                    'Seleccione un estado válido.',
                    ['estado' => 'Seleccione un estado válido.']
                );
            }
            $this->service->cambiarEstado($idSolicitud, $nuevoEstado, $motivo);
            $_SESSION['_mensaje_exito'] = 'El estado de la solicitud se actualizó correctamente.';
            header('Location: ?ruta=solicitudes/pendientes', true, 303);
            return;
        } catch (ValidacionException $e) {
            http_response_code(422);
            $errores = $e->errores;
            $mensajeGeneral = $e->getMessage();
        }

        $tokenCsrf = Csrf::generar();
        $estadosPermitidos = $this->estadosPermitidos($solicitud->estado);
        $tituloPagina = 'Cambiar estado de solicitud';
        require dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'solicitudes' . DIRECTORY_SEPARATOR . 'cambiar_estado.php';
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
     * Obtiene un parámetro escalar de la URL.
     */
    private function valorGet(string $campo): string
    {
        $valor = $_GET[$campo] ?? '';
        return is_string($valor) ? trim($valor) : '';
    }

    /**
     * Devuelve las transiciones válidas para el estado actual.
     *
     * @return Estado[]
     */
    private function estadosPermitidos(Estado $estadoActual): array
    {
        return match ($estadoActual) {
            Estado::RECIBIDA => [Estado::EVALUADA],
            Estado::EVALUADA => [Estado::APROBADA, Estado::RECHAZADA],
            Estado::APROBADA => [Estado::IMPLEMENTADA],
            Estado::RECHAZADA, Estado::IMPLEMENTADA => [],
        };
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
