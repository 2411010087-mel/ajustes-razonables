<?php
declare(strict_types=1);

use App\Config\Config;
use App\Controller\EstudianteController;
use App\Controller\SolicitudController;
use App\Exception\ConfiguracionException;
use App\Repository\Conexion;
use App\Repository\EstudianteRepositoryPdo;
use App\Repository\SolicitudRepositoryPdo;
use App\Repository\TipoAjusteRepositoryPdo;
use App\Security\Escape;
use App\Service\EstudianteService;
use App\Service\SolicitudService;

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src'
    . DIRECTORY_SEPARATOR . 'autoload.php';

header('Content-Type: text/html; charset=UTF-8');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

try {
    $configuracion = Config::cargar();
    $pdo = Conexion::obtener($configuracion);
    $estudianteRepository = new EstudianteRepositoryPdo($pdo);
    $solicitudRepository = new SolicitudRepositoryPdo($pdo);
    $tipoAjusteRepository = new TipoAjusteRepositoryPdo($pdo);
    $estudianteController = new EstudianteController(
        new EstudianteService($estudianteRepository)
    );
    $solicitudController = new SolicitudController(
        new SolicitudService(
            $solicitudRepository,
            $estudianteRepository,
            $tipoAjusteRepository
        )
    );

    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (!in_array($metodo, ['GET', 'POST'], true)) {
        http_response_code(405);
        header('Allow: GET, POST');
        $tituloPagina = 'Método no permitido';
        $mensaje = 'Este método de petición no está permitido.';
        require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR . 'error.php';
        exit;
    }

    $ruta = $_GET['ruta'] ?? 'estudiantes/registrar';
    if (!is_string($ruta)) {
        $ruta = '';
    }
    if ($ruta === 'estudiantes/registrar') {
        $estudianteController->registrar();
    } elseif ($ruta === 'solicitudes/registrar') {
        $solicitudController->registrar();
    } else {
        http_response_code(404);
        $tituloPagina = 'Página no encontrada';
        $mensaje = 'La página solicitada no existe.';
        require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views'
            . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR . 'error.php';
    }
} catch (ConfiguracionException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    $tituloPagina = 'Error de configuración';
    $mensaje = 'No se pudo iniciar el sistema. Revise la configuración .env.';
    require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views'
        . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR . 'error.php';
} catch (Throwable $e) {
    error_log((string) $e);
    http_response_code(500);
    $tituloPagina = 'Error interno';
    $mensaje = 'Ocurrió un error al procesar la solicitud. Inténtelo nuevamente.';
    require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'views'
        . DIRECTORY_SEPARATOR . 'errores' . DIRECTORY_SEPARATOR . 'error.php';
}
