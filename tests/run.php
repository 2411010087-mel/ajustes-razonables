<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src'
    . DIRECTORY_SEPARATOR . 'autoload.php';

use App\Config\Config;
use App\Exception\DuplicadoException;
use App\Exception\EstudianteNoEncontradoException;
use App\Exception\TipoAjusteInvalidoException;
use App\Exception\ValidacionException;
use App\Model\Estado;
use App\Repository\Conexion;
use App\Repository\EstudianteRepositoryPdo;
use App\Repository\SolicitudRepositoryPdo;
use App\Repository\TipoAjusteRepositoryPdo;
use App\Service\EstudianteService;
use App\Service\SolicitudService;

/**
 * Lanza una excepción si la condición indicada no se cumple.
 */
function verificar(bool $condicion, string $mensaje = 'Fallo'): void
{
    if (!$condicion) {
        throw new RuntimeException($mensaje);
    }
}

/**
 * Comprueba que una operación lance una excepción del tipo esperado.
 */
function esperarExcepcion(string $clase, callable $operacion): void
{
    try {
        $operacion();
    } catch (Throwable $e) {
        verificar(
            $e instanceof $clase,
            'Se esperaba ' . $clase . ' y se obtuvo ' . $e::class
        );
        return;
    }

    throw new RuntimeException('Se esperaba ' . $clase . ' pero no se lanzó ninguna excepción.');
}

/**
 * Ejecuta una prueba e informa su resultado sin detener las pruebas siguientes.
 */
function probar(string $nombre, callable $operacion): void
{
    global $correctas, $fallidas;

    try {
        $operacion();
        echo '[OK] ' . $nombre . PHP_EOL;
        $correctas++;
    } catch (Throwable $e) {
        echo '[FALLO] ' . $nombre . ' — ' . $e->getMessage() . PHP_EOL;
        $fallidas++;
    }
}

$correctas = 0;
$fallidas = 0;
$configuracion = Config::cargar();
$pdo = Conexion::obtener($configuracion, true);

$schema = file_get_contents(
    dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'schema.sql'
);
$seed = file_get_contents(
    dirname(__DIR__) . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seed.sql'
);
if ($schema === false || $seed === false) {
    throw new RuntimeException('No se pudieron leer los archivos SQL de prueba.');
}
foreach ([$schema, $seed] as $contenidoSql) {
    foreach (explode(';', $contenidoSql) as $consulta) {
        if (trim($consulta) !== '') {
            $pdo->exec($consulta);
        }
    }
}

$estudianteRepository = new EstudianteRepositoryPdo($pdo);
$solicitudRepository = new SolicitudRepositoryPdo($pdo);
$tipoAjusteRepository = new TipoAjusteRepositoryPdo($pdo);
$estudianteService = new EstudianteService($estudianteRepository);
$solicitudService = new SolicitudService(
    $solicitudRepository,
    $estudianteRepository,
    $tipoAjusteRepository
);

probar('Registra un estudiante válido', static function () use ($estudianteService): void {
    $estudiante = $estudianteService->registrar(
        '70000001',
        'Estudiante Prueba Uno',
        'Ingeniería de Sistemas',
        'VISUAL',
        'CONADIS',
        'CONADIS-00001'
    );
    verificar($estudiante->documento === '70000001', 'El documento registrado no coincide.');
});

probar('Rechaza un documento duplicado', static function () use ($estudianteService): void {
    esperarExcepcion(DuplicadoException::class, static fn () => $estudianteService->registrar(
        '70000001',
        'Estudiante Prueba Uno',
        'Ingeniería de Sistemas',
        'VISUAL',
        'CONADIS',
        'CONADIS-00001'
    ));
});

probar('Rechaza un documento que no tiene ocho dígitos', static function () use (
    $estudianteService
): void {
    esperarExcepcion(ValidacionException::class, static fn () => $estudianteService->registrar(
        '7000002',
        'Estudiante Prueba Dos',
        'Administración',
        'AUDITIVA',
        'CONADIS',
        'CONADIS-00002'
    ));
});

probar('Rechaza campos obligatorios vacíos', static function () use (
    $estudianteService
): void {
    esperarExcepcion(ValidacionException::class, static fn () => $estudianteService->registrar(
        '70000002',
        '',
        '',
        '',
        '',
        ''
    ));
});

probar('Rechaza nombre y carrera fuera de longitud', static function () use (
    $estudianteService
): void {
    esperarExcepcion(ValidacionException::class, static fn () => $estudianteService->registrar(
        '70000003',
        str_repeat('a', 101),
        str_repeat('b', 51),
        'FISICA',
        'CERT-MED',
        'CERT-MED-00003'
    ));
});

probar('Registra solicitud con estado inicial RECIBIDA', static function () use (
    $solicitudService
): void {
    $solicitud = $solicitudService->registrar(
        '70000001',
        'Programación I',
        'TIEMPO_ADICIONAL'
    );
    verificar($solicitud->estado === Estado::RECIBIDA, 'El estado inicial no es RECIBIDA.');
    verificar($solicitud->idSolicitud !== null, 'No se asignó identificador a la solicitud.');
});

probar('Rechaza una solicitud sin campos obligatorios', static function () use (
    $solicitudService
): void {
    esperarExcepcion(ValidacionException::class, static fn () => $solicitudService->registrar(
        '',
        '',
        ''
    ));
});

probar('Rechaza un tipo de ajuste inválido', static function () use (
    $solicitudService
): void {
    esperarExcepcion(TipoAjusteInvalidoException::class, static fn () =>
        $solicitudService->registrar('70000001', 'Programación I', 'NO_EXISTE')
    );
});

probar('Rechaza una solicitud para estudiante inexistente', static function () use (
    $solicitudService
): void {
    esperarExcepcion(EstudianteNoEncontradoException::class, static fn () =>
        $solicitudService->registrar('99999999', 'Programación I', 'TIEMPO_ADICIONAL')
    );
});

echo PHP_EOL . '--- Resumen ---' . PHP_EOL;
echo 'Correctas: ' . $correctas . PHP_EOL;
echo 'Fallidas: ' . $fallidas . PHP_EOL;
exit($fallidas > 0 ? 1 : 0);
