# PROMPTS

Registro de prompts usados en el proyecto "Ajustes razonables" (Examen Parcial · Unidad 2).

| # | Herramienta | Prompt (resumen) | Decisión | Observación |
|---|-------------|------------------|----------|-------------|
| 1 | Kiro | Steering con el contexto del caso (Oficina de Bienestar, ajustes razonables, stack PHP 8.2 + MySQL + PDO) | Aceptado | Sirve de contexto para la especificación |
| 2 | Kiro | Generar requirements.md a partir del caso | Corregido | Usó formato en inglés (WHEN/SHALL) e IDs "Requirement N"; inventó historial de estados, exportación de reporte y consulta del Docente; cambió los campos y los tipos de discapacidad |
| 3 | Kiro | Corregir requirements.md: criterios EARS en español, IDs RF/RNF, campos del caso, transiciones de estado, accesibilidad y .env en .gitignore | Aceptado | Se eliminó lo que el caso no pide |
| 4 | Manual | Cuatro retoques a requirements.md: número de acreditación (RF1), valores de Tipo_de_Ajuste y validación (RF2), motivo vacío (RF3), CSRF inválido (RNF4) y contraste 4.5:1 (RNF1) | Aplicado | Retoques hechos a mano por el equipo |
| 5 | Kiro | Refinamiento automático de requisitos (ejecutado por Kiro tras la corrección) | Aceptado | Agregó límites de longitud (nombre 100, carrera 50, motivo 10–500), mensaje de lista vacía (RF4) y error de configuración (RNF5); se verificó que no inventara funciones |
| 6 | Kiro | Generar design.md: capas, DDL, contratos tipados, pruebas en tests/run.php, fuera de alcance y correcciones del equipo | Corregido | Usó datos de prueba inventados, propuso property-based testing con librería externa e incluía un listado de estudiantes que el caso no pide |
| 7 | Kiro | Corregir design.md: datos oficiales, quitar property-based testing, DROP TABLE en schema.sql, base ajustes_test, quitar listar de estudiantes | Aceptado | |
| 8 | Kiro | Corregir design.md: reemplazar assert() por verificar(), agregar pruebas con casos inválidos, corregir tabla de cobertura, quitar totalSolicitudes() | Corregido | Kiro no logró guardar design.md (créditos y fallas de escritura); se completó el archivo manualmente con ayuda de un asistente externo (Claude.ai) |
| 9 | Agente del IDE | Prompt de reglas: leer requirements.md y design.md y resumir reglas y módulos sin generar código | (pendiente) | |
| 10 | Agente del IDE | Módulo 1 en modo plan: registro de estudiante y de solicitud | (pendiente) | |

## Decisiones del equipo

- "Pendiente" = solicitud cuyo estado no es IMPLEMENTADA ni RECHAZADA (una solicitud APROBADA sigue pendiente porque falta implementarla).
- Los límites de longitud generados por Kiro (nombre 100, carrera 50, motivo 10–500) se mantienen y coinciden con el DDL de design.md.
- Contingencia: Kiro se quedó sin créditos; el resto del trabajo se hace con el agente del IDE.
## Prompts de la fase de código (continuación)

| # | Herramienta | Prompt (resumen) | Decisión | Observación |
|---|-------------|------------------|----------|-------------|
| 9 | Copilot (VS Code) | Prompt de reglas: leer requirements.md y design.md y resumir sin generar código | Aceptado | RF5 queda opcional |
| 10 | Copilot (VS Code) | Módulo 1 en modo plan: registro de estudiante y de solicitud (RF1 y RF2) | Corregido | Se pidió no sobrescribir .env, .env.example ni .gitignore y usar DROP TABLE IF EXISTS en schema.sql |
| 11 | Copilot (VS Code) | Ejecutar el plan del Módulo 1 | Aceptado | 9 pruebas OK; POST sin CSRF rechazado (HTTP 400) |
| 12 | Copilot (VS Code) | Módulo 2 en modo plan: cambio de estado y listado de pendientes (RF3 y RF4) | Corregido | Se pidió filtro por documento o nombre, validación en servidor y pruebas de borde del motivo |
| 13 | Copilot (VS Code) | Ejecutar el plan del Módulo 2 | Aceptado | 15 pruebas OK, 0 fallidas |
| 14 | Copilot (VS Code) | Auditoría OWASP Top 10 sin modificar archivos | Corregido | Único hallazgo: falta de control de acceso (login); no se corrige por estar fuera de alcance y pasa a TECH_DEBT.md |

## Texto completo de los prompts

### Prompt 1 - Kiro - Steering (Aceptado)
Contexto del caso "Ajustes razonables" de la Oficina de Bienestar Universitario: stack PHP 8.2+ orientado a objetos, MySQL, PDO preparado, sin frameworks; fuera de alcance: chatbot, RAG, autenticación y despliegue público.

### Prompt 2 - Kiro - Generar requirements.md (Corregido)
Generar requirements.md a partir del caso.

### Prompt 3 - Kiro - Corregir requirements.md (Aceptado)
Corrige requirements.md con estos cambios:
1. Reescribe todos los criterios en español con el formato EARS: "CUANDO ... EL SISTEMA DEBERÁ ...". Quita las palabras WHEN, THE, SHALL.
2. Renumera como RF1...RFn (funcionales) y RNF1...RNFn (no funcionales: accesibilidad, datos personales Ley 29733, PDO preparado, CSRF y salida escapada, .env).
3. Elimina lo que el caso no pide: historial de cambios de estado, exportar reporte, consulta del Docente, y la fecha del cambio de estado.
4. Los campos del estudiante deben ser: documento (8 dígitos), nombre, carrera, tipo de discapacidad (VISUAL, AUDITIVA, FISICA, INTELECTUAL) y documento de acreditación (CONADIS o CERT-MED).
5. Define las transiciones válidas de estado: RECIBIDA>EVALUADA>APROBADA o RECHAZADA; APROBADA>IMPLEMENTADA.
6. En el RNF de accesibilidad incluye: label for en cada input, aria-describedby en los errores, orden lógico de tabulación, contraste suficiente y que nada dependa solo del color o del ratón.
7. El .env debe estar excluido del repositorio mediante .gitignore.

### Prompt 4 - Manual - Cuatro retoques (Aplicado)
(a) Número del documento de acreditación en RF1.1. (b) Valores de Tipo_de_Ajuste en el glosario y rechazo de tipo inválido o estudiante inexistente en RF2. (c) Rechazo con motivo vacío en RF3. (d) Rechazo por token CSRF inválido en RNF4 y contraste mínimo 4.5:1 (WCAG AA) en RNF1.

### Prompt 5 - Kiro - Refinamiento automático (Aceptado)
Ejecutado por Kiro tras la corrección. Agregó límites de longitud y mensajes de error.

### Prompt 6 - Kiro - Generar design.md (Corregido)
Genera design.md con: capas Model > Repository > Service > Controller > views y estructura de carpetas; tablas con DDL MySQL (estudiante, tipo_ajuste, solicitud con FK, estado y motivo_rechazo); contratos de clases con firmas tipadas (PHP 8.2, strict_types); pruebas mínimas en tests/run.php (4 o más, con casos inválidos); y lista de fuera de alcance (sin autenticación, sin adjuntos, sin historial, sin exportación). Usa los datos de prueba del enunciado. Al final agrega la sección "Correcciones del equipo a la generación de Kiro" con 3 correcciones.

### Prompt 7 - Kiro - Corregir design.md (Aceptado)
Corrige design.md: (1) datos de prueba oficiales del enunciado; (2) elimina property-based testing y librerías de Packagist; (3) agrega DROP TABLE IF EXISTS al inicio de schema.sql y usa la base ajustes_test; (4) corrige el docblock de buscarPorId; (5) quita estudiantes/listar.php y EstudianteController::listar(); (6) agrega las correcciones 4 y 5. No agregues login, adjuntos, historial, exportación ni campos nuevos.

### Prompt 8 - Kiro - Segunda corrección de design.md (Corregido)
(1) Reemplaza assert() por una función verificar() propia. (2) Agrega pruebas con casos inválidos (documento de 7 dígitos, tipo de ajuste inválido, estudiante inexistente, motivo vacío, motivo válido, RECHAZADA fuera de pendientes). (3) Corrige la tabla de cobertura: "Probado" solo lo que tiene prueba. (4) Elimina totalSolicitudes(). (5) Agrega la corrección 6. Observación: Kiro no persistió design.md; se completó manualmente.

### Prompt 9 - Copilot - Reglas (Aceptado)
Eres el desarrollador de un proyecto PHP. Lee requirements.md y design.md de la raíz del proyecto y NO escribas código todavía.
REGLAS OBLIGATORIAS: 1. PHP 8.2+ orientado a objetos, sin frameworks, sin Composer, sin PHPUnit. 2. Una clase por archivo, namespaces PSR-4 y autoloader propio en src/autoload.php. 3. declare(strict_types=1) y tipos declarados. 4. PSR-12 y PHPDoc en español. 5. Capas Model, Repository, Service, Controller, views; el SQL solo en repositorios *Pdo. 6. PDO con sentencias preparadas. 7. Configuración desde .env; si falta, ConfiguracionException. 8. Token CSRF en todo formulario POST. 9. Salida escapada con Escape::html(). 10. Excepciones propias según design.md. 11. Pruebas en tests/run.php sin PHPUnit, con verificar() y casos inválidos. 12. Accesibilidad: label for, aria-describedby, orden de tabulación, contraste 4.5:1, nada solo por color o ratón, operable con teclado. 13. Seguir los contratos y la estructura de design.md.
PROHIBIDO: login, adjuntos, historial, exportación, vista del Docente y funciones fuera de requirements.md.
Responde SOLO con un resumen de máximo 15 líneas. No generes código todavía.

### Prompt 10 - Copilot - Módulo 1, plan (Corregido)
Aclaración: RF5 (totales en pantalla) es opcional y solo se hará si sobra tiempo después del Módulo 2. No se exporta nada.
Módulo 1: registro de estudiante y de solicitud (RF1 y RF2). Entra en modo plan: lista los archivos que crearás y el orden. Para cada archivo indica en una línea su responsabilidad. No generes código hasta que yo apruebe el plan.
Ajustes al aprobar: no sobrescribir .env, .env.example ni .gitignore; schema.sql con SET FOREIGN_KEY_CHECKS=0; DROP TABLE IF EXISTS solicitud, estudiante, tipo_ajuste; SET FOREIGN_KEY_CHECKS=1; y el DDL exacto de design.md.

### Prompt 11 - Copilot - Ejecución del Módulo 1 (Aceptado)
Ejecuta el plan. Al terminar, resume en máximo 8 líneas qué creaste y cómo probarlo en el navegador y con php tests/run.php. Resultado: 9 pruebas OK; POST sin CSRF rechazado (HTTP 400).

### Prompt 12 - Copilot - Módulo 2, plan (Corregido)
Módulo 2: cambio de estado y listado de pendientes (RF3 y RF4). Entra en modo plan: lista los archivos que crearás o modificarás y el orden. Reglas: transiciones válidas solo RECIBIDA>EVALUADA>APROBADA o RECHAZADA, y APROBADA>IMPLEMENTADA; motivo de rechazo obligatorio de 10 a 500 caracteres (MotivoVacioException); pendiente = ni IMPLEMENTADA ni RECHAZADA; filtros por estudiante (documento o nombre) y por asignatura; mensaje si no hay pendientes. Agrega pruebas a tests/run.php con casos inválidos. No generes código hasta que apruebe el plan.
Ajustes al aprobar: (1) filtro por documento exacto o nombre parcial con JOIN y LIKE preparado, mostrando documento y nombre; (2) validar transición y motivo en el servidor con trim; (3) pruebas de motivo con 9, 10, 500 y 501 caracteres y solo espacios.

### Prompt 13 - Copilot - Ejecución del Módulo 2 (Aceptado)
Ejecuta el plan. No toques el esquema, .env, .env.example ni .gitignore. Resume en máximo 8 líneas. Resultado: 15 pruebas OK, 0 fallidas.

### Prompt 14 - Copilot - Auditoría OWASP (Corregido)
Audita el proyecto contra OWASP Top 10 (inyección SQL, XSS, CSRF, configuración insegura, manejo de errores, exposición de datos). Lista los hallazgos con archivo y línea, sin modificar nada. No agregues funciones nuevas.
Seguimiento: No agregues autenticación ni login: está fuera de alcance y se documentó en TECH_DEBT.md. Muéstrame la lista completa de hallazgos separando los que se corrigen sin agregar funciones nuevas y los que son deuda técnica. No modifiques nada todavía.
Resultado: único hallazgo (falta de control de acceso), clasificado como deuda técnica.
