# Diseño — Sistema de Ajustes Razonables

## 1. Visión general

La Oficina de Bienestar Universitario recibe solicitudes de ajustes razonables por correo, WhatsApp y papel, por lo que se pierden, no se sabe cuáles están pendientes y no se genera el reporte semestral. Este sistema centraliza el registro de estudiantes, las solicitudes por asignatura, el cambio de estado y los totales del reporte, con accesibilidad (WCAG AA) y protección de datos personales (Ley N.° 29733).

Arquitectura por capas: **Model → Repository → Service → Controller → views**. PHP 8.2 orientado a objetos, sin frameworks, MySQL con PDO preparado.

| Decisión | Justificación |
|---|---|
| Máquina de estados en `Estado::puedeTransitarA()` | Las transiciones son regla de negocio; un solo punto de verdad y fácil de probar |
| Enums nativos (`Estado`, `TipoDiscapacidad`, `TipoDocumentoAcreditacion`, `TipoAjuste`) | Dominios cerrados validados por tipo |
| Interfaces de repositorio + implementación `*Pdo` | Todo el SQL queda en un solo lugar, con sentencias preparadas |
| Excepciones propias por error de negocio | El controlador responde con mensajes precisos |
| "Pendiente" = ni IMPLEMENTADA ni RECHAZADA | Decisión del equipo: una solicitud APROBADA sigue pendiente hasta implementarse |

## 2. Arquitectura

```
Navegador → public/index.php (router) → Controller → Service → Repository → MySQL (PDO) → Views
```

Convenciones: `declare(strict_types=1)`, una clase por archivo, namespaces PSR-4 (`App\Model`, `App\Repository`, `App\Service`, `App\Controller`, `App\Exception`, `App\Security`, `App\Config`), PSR-12, PHPDoc en español, tipos declarados, `Escape::html()` en toda salida, token CSRF en todo formulario POST.

### Máquina de estados (RF3)

Transiciones válidas: `RECIBIDA→EVALUADA`, `EVALUADA→APROBADA`, `EVALUADA→RECHAZADA` (con motivo de 10 a 500 caracteres), `APROBADA→IMPLEMENTADA`. Cualquier otra se rechaza y la solicitud conserva su estado.

### Estructura de carpetas

```
ajustes-razonables/
├── public/index.php
├── src/
│   ├── autoload.php
│   ├── Model/        Estudiante, Solicitud, Estado, TipoAjuste, TipoDiscapacidad, TipoDocumentoAcreditacion
│   ├── Repository/   EstudianteRepository, SolicitudRepository, TipoAjusteRepository (interfaces),
│   │                 Conexion, EstudianteRepositoryPdo, SolicitudRepositoryPdo, TipoAjusteRepositoryPdo
│   ├── Service/      EstudianteService, SolicitudService, ReporteService
│   ├── Controller/   EstudianteController, SolicitudController, ReporteController
│   ├── Exception/    ValidacionException, DuplicadoException, TransicionInvalidaException,
│   │                 TipoAjusteInvalidoException, MotivoVacioException,
│   │                 EstudianteNoEncontradoException, ConfiguracionException
│   ├── Security/     Csrf, Escape
│   └── Config/       Config
├── views/
│   ├── layout/       header.php, footer.php
│   ├── estudiantes/  registrar.php
│   ├── solicitudes/  registrar.php, listar.php, cambiar_estado.php
│   ├── reportes/     totales.php
│   └── errores/      error.php
├── database/         schema.sql, seed.sql
├── tests/run.php
├── .env.example
└── .gitignore
```

## 3. Componentes y contratos

### Enums

```php
namespace App\Model;

enum Estado: string
{
    case RECIBIDA = 'RECIBIDA';
    case EVALUADA = 'EVALUADA';
    case APROBADA = 'APROBADA';
    case RECHAZADA = 'RECHAZADA';
    case IMPLEMENTADA = 'IMPLEMENTADA';

    /** Indica si desde este estado se puede transitar al destino. */
    public function puedeTransitarA(Estado $destino): bool;

    /** Indica si la solicitud está pendiente (ni IMPLEMENTADA ni RECHAZADA). */
    public function esPendiente(): bool;
}

enum TipoDiscapacidad: string { case VISUAL = 'VISUAL'; case AUDITIVA = 'AUDITIVA'; case FISICA = 'FISICA'; case INTELECTUAL = 'INTELECTUAL'; }

enum TipoDocumentoAcreditacion: string { case CONADIS = 'CONADIS'; case CERT_MED = 'CERT-MED'; }

enum TipoAjuste: string
{
    case TIEMPO_ADICIONAL = 'TIEMPO_ADICIONAL';
    case MATERIAL_ACCESIBLE = 'MATERIAL_ACCESIBLE';
    case INTERPRETE_LSP = 'INTERPRETE_LSP';
    case AULA_ACCESIBLE = 'AULA_ACCESIBLE';
    case MOBILIARIO = 'MOBILIARIO';
    case ACOMPANAMIENTO = 'ACOMPANAMIENTO';

    /** @throws \App\Exception\TipoAjusteInvalidoException Si el valor no es válido. */
    public static function desdeValor(string $valor): self;
}
```

### Modelos

```php
namespace App\Model;

class Estudiante
{
    public function __construct(
        public readonly string $documento,
        public readonly string $nombre,
        public readonly string $carrera,
        public readonly TipoDiscapacidad $tipoDiscapacidad,
        public readonly TipoDocumentoAcreditacion $tipoDocumentoAcreditacion,
        public readonly string $numeroDocumentoAcreditacion
    ) {}
}

class Solicitud
{
    public function __construct(
        public readonly ?int $idSolicitud,
        public readonly string $documentoEstudiante,
        public readonly int $idTipoAjuste,
        public readonly TipoAjuste $tipoAjuste,
        public readonly string $asignatura,
        public Estado $estado,
        public ?string $motivoRechazo,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt
    ) {}
}
```

### Repositorios (interfaces)

```php
namespace App\Repository;

interface EstudianteRepository
{
    public function buscarPorDocumento(string $documento): ?Estudiante;
    public function existeDocumento(string $documento): bool;
    public function guardar(Estudiante $estudiante): void;
    /** @return Estudiante[] */
    public function buscar(string $criterio): array;
}

interface SolicitudRepository
{
    public function guardar(Solicitud $solicitud): int;
    public function buscarPorId(int $idSolicitud): ?Solicitud;
    public function actualizarEstado(int $idSolicitud, Estado $estado, ?string $motivoRechazo): void;
    /** @return Solicitud[] */
    public function listarPendientes(): array;
    /** @return Solicitud[] */
    public function filtrarPorEstudiante(string $criterio): array;
    /** @return Solicitud[] */
    public function filtrarPorAsignatura(string $asignatura): array;
    /** @return array<string,int> */
    public function contarPorTipoAjuste(): array;
    /** @return array<string,int> */
    public function contarPorEstado(): array;
}

interface TipoAjusteRepository
{
    public function idPorValor(string $valor): ?int;
    public function existeValor(string $valor): bool;
    /** @return TipoAjuste[] */
    public function listarTodos(): array;
}
```

### Servicios

```php
namespace App\Service;

class EstudianteService
{
    public function __construct(private EstudianteRepository $repository) {}

    /** @throws ValidacionException|DuplicadoException */
    public function registrar(string $documento, string $nombre, string $carrera,
        string $tipoDiscapacidad, string $tipoDocumentoAcreditacion,
        string $numeroDocumentoAcreditacion): Estudiante;

    /** @return Estudiante[] */
    public function buscar(string $criterio): array;
}

class SolicitudService
{
    public function __construct(
        private SolicitudRepository $solicitudRepository,
        private EstudianteRepository $estudianteRepository,
        private TipoAjusteRepository $tipoAjusteRepository
    ) {}

    /** @throws ValidacionException|TipoAjusteInvalidoException|EstudianteNoEncontradoException */
    public function registrar(string $documentoEstudiante, string $asignatura, string $tipoAjusteValor): Solicitud;

    /** @throws TransicionInvalidaException|MotivoVacioException */
    public function cambiarEstado(int $idSolicitud, Estado $nuevoEstado, ?string $motivo = null): Solicitud;

    public function buscarPorId(int $idSolicitud): ?Solicitud;
    /** @return Solicitud[] */
    public function listarPendientes(): array;
    /** @return Solicitud[] */
    public function filtrarPorEstudiante(string $criterio): array;
    /** @return Solicitud[] */
    public function filtrarPorAsignatura(string $asignatura): array;
}

class ReporteService
{
    public function __construct(private SolicitudRepository $solicitudRepository) {}

    /** @return array<string,int> Incluye ceros. */
    public function totalesPorTipoAjuste(): array;
    /** @return array<string,int> Incluye ceros. */
    public function totalesPorEstado(): array;
}
```

### Controladores

```php
namespace App\Controller;

class EstudianteController
{
    public function __construct(private EstudianteService $service) {}
    public function mostrarFormularioRegistro(): void;
    public function registrar(): void;   // valida CSRF, delega y muestra errores
}

class SolicitudController
{
    public function __construct(private SolicitudService $service) {}
    public function mostrarFormularioRegistro(): void;
    public function registrar(): void;
    public function listarPendientes(): void;
    public function filtrarPorEstudiante(string $criterio): void;
    public function filtrarPorAsignatura(string $asignatura): void;
    public function mostrarCambioEstado(int $idSolicitud): void;
    public function cambiarEstado(): void;
}

class ReporteController
{
    public function __construct(private ReporteService $service) {}
    public function totales(): void;
}
```

### Seguridad, configuración y excepciones

```php
namespace App\Security;
class Csrf   { public static function generar(): string; public static function verificar(string $token): bool; }
class Escape { public static function html(mixed $valor): string; }

namespace App\Config;
class Config { /** @throws ConfiguracionException */ public static function cargar(): array; }

namespace App\Exception;
class ValidacionException extends \Exception {}
class DuplicadoException extends ValidacionException {}
class TransicionInvalidaException extends ValidacionException {}
class TipoAjusteInvalidoException extends ValidacionException {}
class MotivoVacioException extends ValidacionException {}
class EstudianteNoEncontradoException extends ValidacionException {}
class ConfiguracionException extends \Exception {}
```

## 4. Modelo de datos

### database/schema.sql

```sql
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS solicitud;
DROP TABLE IF EXISTS estudiante;
DROP TABLE IF EXISTS tipo_ajuste;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE estudiante (
    documento                     VARCHAR(8)   NOT NULL,
    nombre                        VARCHAR(100) NOT NULL,
    carrera                       VARCHAR(50)  NOT NULL,
    tipo_discapacidad             ENUM('VISUAL','AUDITIVA','FISICA','INTELECTUAL') NOT NULL,
    tipo_documento_acreditacion   ENUM('CONADIS','CERT-MED') NOT NULL,
    numero_documento_acreditacion VARCHAR(50)  NOT NULL,
    PRIMARY KEY (documento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tipo_ajuste (
    id_tipo_ajuste INT NOT NULL AUTO_INCREMENT,
    nombre ENUM('TIEMPO_ADICIONAL','MATERIAL_ACCESIBLE','INTERPRETE_LSP',
                'AULA_ACCESIBLE','MOBILIARIO','ACOMPANAMIENTO') NOT NULL,
    PRIMARY KEY (id_tipo_ajuste),
    UNIQUE KEY uq_tipo_ajuste_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE solicitud (
    id_solicitud         INT          NOT NULL AUTO_INCREMENT,
    documento_estudiante VARCHAR(8)   NOT NULL,
    id_tipo_ajuste       INT          NOT NULL,
    asignatura           VARCHAR(100) NOT NULL,
    estado ENUM('RECIBIDA','EVALUADA','APROBADA','RECHAZADA','IMPLEMENTADA')
           NOT NULL DEFAULT 'RECIBIDA',
    motivo_rechazo       VARCHAR(500) NULL,
    created_at           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at           TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
                                      ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_solicitud),
    KEY idx_solicitud_estado_created_at (estado, created_at),
    KEY idx_solicitud_asignatura (asignatura),
    FOREIGN KEY (documento_estudiante) REFERENCES estudiante(documento)
        ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (id_tipo_ajuste) REFERENCES tipo_ajuste(id_tipo_ajuste)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT chk_motivo_rechazo
        CHECK (motivo_rechazo IS NULL OR CHAR_LENGTH(motivo_rechazo) BETWEEN 10 AND 500)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### database/seed.sql

```sql
INSERT INTO tipo_ajuste (nombre) VALUES
    ('TIEMPO_ADICIONAL'), ('MATERIAL_ACCESIBLE'), ('INTERPRETE_LSP'),
    ('AULA_ACCESIBLE'), ('MOBILIARIO'), ('ACOMPANAMIENTO');

INSERT INTO estudiante (
    documento, nombre, carrera, tipo_discapacidad,
    tipo_documento_acreditacion, numero_documento_acreditacion
) VALUES
    ('70000001', 'Estudiante Prueba Uno', 'Ingeniería de Sistemas',
     'VISUAL', 'CONADIS', 'CONADIS-00001'),
    ('70000002', 'Estudiante Prueba Dos', 'Administración',
     'AUDITIVA', 'CONADIS', 'CONADIS-00002'),
    ('70000003', 'Estudiante Prueba Tres', 'Agronomía',
     'FISICA', 'CERT-MED', 'CERT-MED-00003'),
    ('70000004', 'Estudiante Prueba Cuatro', 'Contabilidad',
     'INTELECTUAL', 'CONADIS', 'CONADIS-00004');
```

Las pruebas usan una base distinta (`ajustes_test`) definida en `.env`, y recrean el esquema en cada ejecución.

## 5. Propiedades de corrección

Describen el comportamiento esperado y guían las pruebas de la sección 7 (no se usa property-based testing).

1. **Validación de estudiante** (RF1.1–RF1.4, RF1.6): se acepta solo con documento de 8 dígitos, nombre ≤ 100, carrera ≤ 50, tipos válidos y sin campos obligatorios vacíos.
2. **Estudiante duplicado** (RF1.5): un documento ya registrado se rechaza sin cambiar el sistema.
3. **Solicitud y estado inicial** (RF2.1–RF2.4): con datos válidos queda en RECIBIDA; con tipo de ajuste inválido, estudiante inexistente o campos vacíos se rechaza.
4. **Transiciones** (RF3.1–RF3.4): solo las 4 válidas; una inválida se rechaza y el estado no cambia.
5. **Motivo de rechazo** (RF3.5–RF3.6): obligatorio, entre 10 y 500 caracteres.
6. **Pendientes** (RF4.1–RF4.4): solo RECIBIDA, EVALUADA o APROBADA; filtros por estudiante y asignatura; mensaje si no hay.
7. **Totales** (RF5.1–RF5.2): por cada tipo y estado, con cero cuando no hay solicitudes.

## 6. Manejo de errores

| Excepción | Criterios | Acción del controlador |
|---|---|---|
| `ValidacionException` | RF1.4, RF1.6, RF2.3 | Re-muestra el formulario con el error asociado al campo vía `aria-describedby` |
| `DuplicadoException` | RF1.5 | Indica que el estudiante ya existe |
| `TipoAjusteInvalidoException` | RF2.4 | Indica tipo de ajuste inválido |
| `EstudianteNoEncontradoException` | RF2.4 | Indica que el estudiante no existe |
| `TransicionInvalidaException` | RF3.4 | Indica la transición no permitida y mantiene el estado |
| `MotivoVacioException` | RF3.5, RF3.6 | Indica que el motivo es obligatorio (10–500) |
| `ConfiguracionException` | RNF5.3 | Impide el inicio y muestra error de configuración |
| CSRF ausente o inválido | RNF4.3 | Rechaza la petición sin procesarla |

Reglas transversales: salida escapada con `Escape::html()` (RNF4.2); token CSRF en formularios (RNF4.1); solo sentencias preparadas PDO (RNF3); errores asociados al campo con `aria-describedby`, sin depender solo del color y con contraste 4.5:1 (RNF1).

## 7. Estrategia de pruebas

Pruebas de ejemplo en PHP puro (`php tests/run.php`), sin PHPUnit, sin Composer y sin `assert()`. Se usa una función propia `verificar()` que lanza excepción si la condición es falsa.

```php
<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/autoload.php';

use App\Config\Config;
use App\Repository\Conexion;
use App\Repository\EstudianteRepositoryPdo;
use App\Repository\SolicitudRepositoryPdo;
use App\Repository\TipoAjusteRepositoryPdo;
use App\Service\EstudianteService;
use App\Service\SolicitudService;
use App\Service\ReporteService;
use App\Model\Estado;
use App\Exception\ValidacionException;
use App\Exception\DuplicadoException;
use App\Exception\TipoAjusteInvalidoException;
use App\Exception\EstudianteNoEncontradoException;
use App\Exception\TransicionInvalidaException;
use App\Exception\MotivoVacioException;

/** Lanza una excepción si la condición es falsa (no depende de zend.assertions). */
function verificar(bool $condicion, string $mensaje = 'Fallo'): void
{
    if (!$condicion) {
        throw new \Exception($mensaje);
    }
}

/** Verifica que la función lance la excepción esperada. */
function esperarExcepcion(string $clase, callable $fn): void
{
    try {
        $fn();
    } catch (\Throwable $e) {
        verificar($e instanceof $clase, "Se esperaba $clase y se obtuvo " . $e::class);
        return;
    }
    throw new \Exception("Se esperaba $clase pero no se lanzó ninguna excepción");
}

$ok = 0;
$fallos = 0;

function probar(string $nombre, callable $fn): void
{
    global $ok, $fallos;
    try {
        $fn();
        echo "[OK]    $nombre\n";
        $ok++;
    } catch (\Throwable $e) {
        echo "[FALLO] $nombre — {$e->getMessage()}\n";
        $fallos++;
    }
}

// Base de datos de prueba (ajustes_test, definida en .env)
$config = Config::cargar();
$pdo = Conexion::obtener($config);
$pdo->exec(file_get_contents(__DIR__ . '/../database/schema.sql'));
$pdo->exec(file_get_contents(__DIR__ . '/../database/seed.sql'));

$estudianteRepo = new EstudianteRepositoryPdo($pdo);
$solicitudRepo  = new SolicitudRepositoryPdo($pdo);
$tipoAjusteRepo = new TipoAjusteRepositoryPdo($pdo);

$estudianteService = new EstudianteService($estudianteRepo);
$solicitudService  = new SolicitudService($solicitudRepo, $estudianteRepo, $tipoAjusteRepo);
$reporteService    = new ReporteService($solicitudRepo);

// 1. Registro de estudiante válido
probar('1. Registro de estudiante válido (70000001)', function () use ($estudianteService) {
    $est = $estudianteService->registrar('70000001', 'Estudiante Prueba Uno',
        'Ingeniería de Sistemas', 'VISUAL', 'CONADIS', 'CONADIS-00001');
    verificar($est->documento === '70000001', 'documento incorrecto');
});

// 2. Estudiante duplicado
probar('2. Estudiante duplicado es rechazado', function () use ($estudianteService) {
    esperarExcepcion(DuplicadoException::class, fn () =>
        $estudianteService->registrar('70000001', 'Estudiante Prueba Uno',
            'Ingeniería de Sistemas', 'VISUAL', 'CONADIS', 'CONADIS-00001'));
});

// 3. Documento de 7 dígitos
probar('3. Documento de 7 dígitos es rechazado', function () use ($estudianteService) {
    esperarExcepcion(ValidacionException::class, fn () =>
        $estudianteService->registrar('7000002', 'Estudiante Prueba Dos',
            'Administración', 'AUDITIVA', 'CONADIS', 'CONADIS-00002'));
});

// 4. Solicitud válida con estado inicial RECIBIDA
probar('4. Solicitud válida queda en RECIBIDA', function () use ($solicitudService) {
    $sol = $solicitudService->registrar('70000001', 'Programación I', 'TIEMPO_ADICIONAL');
    verificar($sol->estado === Estado::RECIBIDA, 'estado inicial distinto de RECIBIDA');
});

// 5. Tipo de ajuste inválido
probar('5. Tipo de ajuste inválido es rechazado', function () use ($solicitudService) {
    esperarExcepcion(TipoAjusteInvalidoException::class, fn () =>
        $solicitudService->registrar('70000001', 'Programación I', 'NO_EXISTE'));
});

// 6. Estudiante inexistente
probar('6. Estudiante inexistente es rechazado', function () use ($solicitudService) {
    esperarExcepcion(EstudianteNoEncontradoException::class, fn () =>
        $solicitudService->registrar('99999999', 'Programación I', 'TIEMPO_ADICIONAL'));
});

// 7. Transición válida
probar('7. Transición válida RECIBIDA → EVALUADA', function () use ($solicitudService) {
    $sol = $solicitudService->registrar('70000001', 'Matemática I', 'MATERIAL_ACCESIBLE');
    $act = $solicitudService->cambiarEstado($sol->idSolicitud, Estado::EVALUADA);
    verificar($act->estado === Estado::EVALUADA, 'no pasó a EVALUADA');
});

// 8. Transición inválida
probar('8. Transición inválida RECIBIDA → IMPLEMENTADA', function () use ($solicitudService) {
    $sol = $solicitudService->registrar('70000001', 'Física I', 'INTERPRETE_LSP');
    esperarExcepcion(TransicionInvalidaException::class, fn () =>
        $solicitudService->cambiarEstado($sol->idSolicitud, Estado::IMPLEMENTADA));
    verificar($solicitudService->buscarPorId($sol->idSolicitud)->estado === Estado::RECIBIDA,
        'el estado debía mantenerse RECIBIDA');
});

// 9. Rechazo sin motivo
probar('9. Rechazo sin motivo es rechazado', function () use ($solicitudService) {
    $sol = $solicitudService->registrar('70000001', 'Química I', 'AULA_ACCESIBLE');
    $solicitudService->cambiarEstado($sol->idSolicitud, Estado::EVALUADA);
    esperarExcepcion(MotivoVacioException::class, fn () =>
        $solicitudService->cambiarEstado($sol->idSolicitud, Estado::RECHAZADA, ''));
});

// 10. Rechazo con motivo válido y exclusión de pendientes
probar('10. Rechazo con motivo válido y no aparece en pendientes', function () use ($solicitudService) {
    $sol = $solicitudService->registrar('70000001', 'Biología I', 'MOBILIARIO');
    $solicitudService->cambiarEstado($sol->idSolicitud, Estado::EVALUADA);
    $rech = $solicitudService->cambiarEstado($sol->idSolicitud, Estado::RECHAZADA,
        'El documento de acreditación presentado está vencido.');
    verificar($rech->estado === Estado::RECHAZADA, 'no pasó a RECHAZADA');
    foreach ($solicitudService->listarPendientes() as $p) {
        verificar($p->idSolicitud !== $sol->idSolicitud, 'una RECHAZADA apareció en pendientes');
    }
});

// 11. Reporte de totales
probar('11. Reporte incluye todos los tipos y estados (con ceros)', function () use ($reporteService) {
    verificar(count($reporteService->totalesPorTipoAjuste()) === 6, 'debe haber 6 tipos');
    verificar(count($reporteService->totalesPorEstado()) === 5, 'debe haber 5 estados');
});

echo "\n--- Resumen ---\nOK:     $ok\nFALLOS: $fallos\n";
exit($fallos > 0 ? 1 : 0);
```

### Cobertura

| Criterio | Cobertura |
|---|---|
| RF1.1 registro válido | Probado (1) |
| RF1.4 validación de longitud y dígitos | Probado (3, documento de 7 dígitos) |
| RF1.5 duplicado | Probado (2) |
| RF1.6 campos obligatorios vacíos | Verificación manual en el formulario |
| RF2.1, RF2.2 solicitud y estado RECIBIDA | Probado (4) |
| RF2.4 tipo inválido / estudiante inexistente | Probado (5, 6) |
| RF3.1 RECIBIDA→EVALUADA | Probado (7) |
| RF3.4 transición no permitida | Probado (8) |
| RF3.5, RF3.6 motivo de rechazo | Probado (9, 10) |
| RF4.1 pendientes excluyen RECHAZADA | Probado (10) |
| RF4.2, RF4.3 filtros | Verificación manual |
| RF5.1, RF5.2 totales con ceros | Probado (11) |
| RNF1 accesibilidad | Verificación manual con teclado |
| RNF3 PDO preparado | Revisión de código |
| RNF4 CSRF y escape | Verificación manual |
| RNF5 .env | Verificación manual |

## 8. Datos de prueba

| documento | nombre | carrera | tipo_discapacidad | tipo_acreditación | número_acreditación |
|---|---|---|---|---|---|
| 70000001 | Estudiante Prueba Uno | Ingeniería de Sistemas | VISUAL | CONADIS | CONADIS-00001 |
| 70000002 | Estudiante Prueba Dos | Administración | AUDITIVA | CONADIS | CONADIS-00002 |
| 70000003 | Estudiante Prueba Tres | Agronomía | FISICA | CERT-MED | CERT-MED-00003 |
| 70000004 | Estudiante Prueba Cuatro | Contabilidad | INTELECTUAL | CONADIS | CONADIS-00004 |

## 9. Fuera de alcance

- Sin autenticación ni inicio de sesión.
- Sin archivos adjuntos (solo se guarda el número del documento de acreditación).
- Sin historial de cambios de estado (solo el estado actual).
- Sin exportación del reporte (solo en pantalla).
- Sin vista de consulta del Docente.
- Sin chatbot, RAG ni despliegue público.

## 10. Correcciones del equipo a la generación de Kiro

1. **Formato en inglés e IDs "Requirement N" → EARS en español con RF/RNF:** Kiro usó WHEN/THE/SHALL y numeración "Requirement N". Se corrigió a «CUANDO … EL SISTEMA DEBERÁ …» con RF1–RF5 y RNF1–RNF5.
2. **Inventó historial, exportación y vista del Docente → eliminados:** eran funciones que el caso no pide.
3. **Campos y tipos de discapacidad distintos al caso → corregidos:** usaba "código", "nombres", "apellidos" y "motora". Se cambió a documento, nombre, carrera y VISUAL/AUDITIVA/FISICA/INTELECTUAL.
4. **Datos de prueba inventados → datos oficiales del enunciado:** nombres, carreras y acreditaciones no coincidían; se reemplazaron por los 4 estudiantes oficiales.
5. **Propuso property-based testing con una librería de Packagist → eliminado:** no cabe en el stack sin PHPUnit ni Composer.
6. **Usó `assert()` en las pruebas → reemplazado por `verificar()`:** `assert()` puede desactivarse por configuración y marcar todo como OK.
7. **Tabla de cobertura marcaba todo como OK sin prueba → corregida:** ahora distingue "Probado" de "Verificación manual".
8. **Agregaba un listado de estudiantes y `totalSolicitudes()` que ningún requisito pide → eliminados.**