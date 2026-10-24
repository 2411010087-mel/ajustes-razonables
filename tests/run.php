<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'src'
    . DIRECTORY_SEPARATOR . 'autoload.php';

use App\Config\Config;
use App\Exception\DuplicadoException;
use App\Exception\EstudianteNoEncontradoException;
use App\Exception\MotivoVacioException;
use App\Exception\TipoAjusteInvalidoException;
use App\Exception\TransicionInvalidaException;
use App\Exception\ValidacionException;
use App\Model\Estado;
use App\Repository\Conexion;
use App\Repository\EstudianteRepositoryPdo;
use App\Repository\SolicitudRepositoryPdo;
use App\Repository\TipoAjusteRepositoryPdo;
use App\Service\EstudianteService;
use App\Service\ReporteService;
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
$reporteService = new ReporteService($solicitudRepository);

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

probar('Rechaza una transición inválida y conserva el estado', static function () use (
    $solicitudService
): void {
    $solicitud = $solicitudService->registrar(
        '70000001',
        'Transición inválida',
        'AULA_ACCESIBLE'
    );
    esperarExcepcion(TransicionInvalidaException::class, static fn () =>
        $solicitudService->cambiarEstado($solicitud->idSolicitud, Estado::APROBADA)
    );
    $actual = $solicitudService->buscarPorId($solicitud->idSolicitud);
    verificar($actual?->estado === Estado::RECIBIDA, 'El estado cambió tras una transición inválida.');
});

probar('Un estado final rechazado no cambia ante otra transición inválida', static function () use (
    $solicitudService
): void {
    $solicitud = $solicitudService->registrar(
        '70000001',
        'Estado final sin cambios',
        'AULA_ACCESIBLE'
    );
    $solicitudService->cambiarEstado($solicitud->idSolicitud, Estado::EVALUADA);
    $solicitudService->cambiarEstado(
        $solicitud->idSolicitud,
        Estado::RECHAZADA,
        'Motivo válido de rechazo'
    );

    esperarExcepcion(TransicionInvalidaException::class, static fn () =>
        $solicitudService->cambiarEstado($solicitud->idSolicitud, Estado::IMPLEMENTADA)
    );
    verificar(
        $solicitudService->buscarPorId($solicitud->idSolicitud)?->estado === Estado::RECHAZADA,
        'El estado final RECHAZADA cambió tras una transición inválida.'
    );
});

probar('Permite transiciones RECIBIDA a EVALUADA a APROBADA a IMPLEMENTADA', static function () use (
    $solicitudService
): void {
    $solicitud = $solicitudService->registrar(
        '70000001',
        'Transición válida',
        'MATERIAL_ACCESIBLE'
    );
    $solicitud = $solicitudService->cambiarEstado($solicitud->idSolicitud, Estado::EVALUADA);
    verificar($solicitud->estado === Estado::EVALUADA, 'No pasó a EVALUADA.');
    $solicitud = $solicitudService->cambiarEstado($solicitud->idSolicitud, Estado::APROBADA);
    verificar($solicitud->estado === Estado::APROBADA, 'No pasó a APROBADA.');
    $solicitud = $solicitudService->cambiarEstado($solicitud->idSolicitud, Estado::IMPLEMENTADA);
    verificar($solicitud->estado === Estado::IMPLEMENTADA, 'No pasó a IMPLEMENTADA.');
});

probar('Valida motivo de rechazo tras recortarlo', static function () use (
    $solicitudService
): void {
    $crearEvaluada = static function (string $asignatura) use ($solicitudService) {
        $solicitud = $solicitudService->registrar(
            '70000001',
            $asignatura,
            'AULA_ACCESIBLE'
        );
        return $solicitudService->cambiarEstado($solicitud->idSolicitud, Estado::EVALUADA);
    };

    $motivoNueve = $crearEvaluada('Motivo de 9 caracteres');
    esperarExcepcion(MotivoVacioException::class, static fn () =>
        $solicitudService->cambiarEstado(
            $motivoNueve->idSolicitud,
            Estado::RECHAZADA,
            str_repeat('x', 9)
        )
    );
    verificar(
        $solicitudService->buscarPorId($motivoNueve->idSolicitud)?->estado === Estado::EVALUADA,
        'El estado cambió tras rechazar un motivo de 9 caracteres.'
    );

    $motivoDiez = $crearEvaluada('Motivo de 10 caracteres');
    $rechazoDiez = $solicitudService->cambiarEstado(
        $motivoDiez->idSolicitud,
        Estado::RECHAZADA,
        '  ' . str_repeat('x', 10) . '  '
    );
    verificar($rechazoDiez->motivoRechazo === str_repeat('x', 10), 'No se recortó el motivo de 10 caracteres.');

    $motivoQuinientos = $crearEvaluada('Motivo de 500 caracteres');
    $rechazoQuinientos = $solicitudService->cambiarEstado(
        $motivoQuinientos->idSolicitud,
        Estado::RECHAZADA,
        str_repeat('y', 500)
    );
    verificar(
        strlen((string) $rechazoQuinientos->motivoRechazo) === 500,
        'No se aceptó el motivo de 500 caracteres.'
    );

    $motivoQuinientosUno = $crearEvaluada('Motivo de 501 caracteres');
    esperarExcepcion(MotivoVacioException::class, static fn () =>
        $solicitudService->cambiarEstado(
            $motivoQuinientosUno->idSolicitud,
            Estado::RECHAZADA,
            str_repeat('z', 501)
        )
    );
    verificar(
        $solicitudService->buscarPorId($motivoQuinientosUno->idSolicitud)?->estado
            === Estado::EVALUADA,
        'El estado cambió tras rechazar un motivo de 501 caracteres.'
    );

    $motivoEspacios = $crearEvaluada('Motivo solo espacios');
    esperarExcepcion(MotivoVacioException::class, static fn () =>
        $solicitudService->cambiarEstado(
            $motivoEspacios->idSolicitud,
            Estado::RECHAZADA,
            '          '
        )
    );
    verificar(
        $solicitudService->buscarPorId($motivoEspacios->idSolicitud)?->estado
            === Estado::EVALUADA,
        'El estado cambió tras rechazar un motivo compuesto solo por espacios.'
    );

    $motivoAusente = $crearEvaluada('Motivo omitido');
    esperarExcepcion(MotivoVacioException::class, static fn () =>
        $solicitudService->cambiarEstado(
            $motivoAusente->idSolicitud,
            Estado::RECHAZADA
        )
    );
    verificar(
        $solicitudService->buscarPorId($motivoAusente->idSolicitud)?->estado === Estado::EVALUADA,
        'El estado cambió tras rechazar sin proporcionar un motivo.'
    );
});

probar('Filtra pendientes por documento exacto o nombre parcial', static function () use (
    $solicitudService
): void {
    $porDocumento = $solicitudService->filtrarPorEstudiante('70000001');
    verificar($porDocumento !== [], 'No se encontró el documento exacto del estudiante.');
    foreach ($porDocumento as $solicitud) {
        verificar(
            $solicitud->documentoEstudiante === '70000001',
            'El filtro por documento devolvió otro documento.'
        );
        verificar(
            $solicitud->nombreEstudiante === 'Estudiante Prueba Uno',
            'El nombre del estudiante no se cargó en el listado.'
        );
    }

    $porNombre = $solicitudService->filtrarPorEstudiante('Prueba Uno');
    verificar($porNombre !== [], 'No se encontró el nombre parcial del estudiante.');
    verificar(
        $solicitudService->filtrarPorEstudiante('Estudiante inexistente') === [],
        'Un filtro sin coincidencias debía devolver una lista vacía.'
    );
});

probar('Filtra por asignatura y excluye estados no pendientes', static function () use (
    $solicitudService
): void {
    $solicitud = $solicitudService->registrar(
        '70000001',
        'Asignatura de filtro pendiente',
        'INTERPRETE_LSP'
    );
    $resultado = $solicitudService->filtrarPorAsignatura('filtro pendiente');
    verificar(count($resultado) === 1, 'El filtro de asignatura no coincidió exactamente con la prueba.');
    verificar(
        $resultado[0]->idSolicitud === $solicitud->idSolicitud,
        'El filtro por asignatura devolvió una solicitud incorrecta.'
    );

    $pendientes = $solicitudService->listarPendientes();
    foreach ($pendientes as $pendiente) {
        verificar(
            $pendiente->estado->esPendiente(),
            'El listado incluyó una solicitud RECHAZADA o IMPLEMENTADA.'
        );
    }
});

probar('El reporte cuenta totales conocidos por tipo, estado y total general', static function () use (
    $reporteService,
    $solicitudService
): void {
    $tiposAntes = $reporteService->totalesPorTipoAjuste();
    $estadosAntes = $reporteService->totalesPorEstado();
    $totalAntes = $reporteService->totalSolicitudes();

    $solicitudRecibida = $solicitudService->registrar(
        '70000001',
        'Reporte conocido uno',
        'TIEMPO_ADICIONAL'
    );
    $solicitudAprobada = $solicitudService->registrar(
        '70000001',
        'Reporte conocido dos',
        'TIEMPO_ADICIONAL'
    );
    $solicitudRechazada = $solicitudService->registrar(
        '70000001',
        'Reporte conocido tres',
        'MATERIAL_ACCESIBLE'
    );

    $solicitudService->cambiarEstado($solicitudAprobada->idSolicitud, Estado::EVALUADA);
    $solicitudService->cambiarEstado($solicitudAprobada->idSolicitud, Estado::APROBADA);
    $solicitudService->cambiarEstado($solicitudRechazada->idSolicitud, Estado::EVALUADA);
    $solicitudService->cambiarEstado(
        $solicitudRechazada->idSolicitud,
        Estado::RECHAZADA,
        'Motivo de prueba para reporte'
    );

    $tiposDespues = $reporteService->totalesPorTipoAjuste();
    $estadosDespues = $reporteService->totalesPorEstado();
    verificar(
        $tiposDespues['TIEMPO_ADICIONAL'] === $tiposAntes['TIEMPO_ADICIONAL'] + 2,
        'El total por TIEMPO_ADICIONAL no aumentó en dos.'
    );
    verificar(
        $tiposDespues['MATERIAL_ACCESIBLE'] === $tiposAntes['MATERIAL_ACCESIBLE'] + 1,
        'El total por MATERIAL_ACCESIBLE no aumentó en uno.'
    );
    verificar(
        $estadosDespues[Estado::RECIBIDA->value] === $estadosAntes[Estado::RECIBIDA->value] + 1,
        'El total RECIBIDA no aumentó en uno.'
    );
    verificar(
        $estadosDespues[Estado::APROBADA->value] === $estadosAntes[Estado::APROBADA->value] + 1,
        'El total APROBADA no aumentó en uno.'
    );
    verificar(
        $estadosDespues[Estado::RECHAZADA->value] === $estadosAntes[Estado::RECHAZADA->value] + 1,
        'El total RECHAZADA no aumentó en uno.'
    );
    verificar(
        $reporteService->totalSolicitudes() === $totalAntes + 3,
        'El total general no aumentó en tres.'
    );
    verificar(
        $solicitudRecibida->estado === Estado::RECIBIDA,
        'La solicitud de referencia debía permanecer RECIBIDA.'
    );
});

echo PHP_EOL . '--- Resumen ---' . PHP_EOL;
echo 'Correctas: ' . $correctas . PHP_EOL;
echo 'Fallidas: ' . $fallidas . PHP_EOL;
exit($fallidas > 0 ? 1 : 0);
