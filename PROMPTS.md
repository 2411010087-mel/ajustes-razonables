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