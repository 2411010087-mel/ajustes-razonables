# Ajustes razonables

Aplicación web interna para la Oficina de Bienestar Universitario: registro de estudiantes con discapacidad, solicitudes de ajustes razonables, cambio de estado y listado de pendientes.

Stack: PHP 8.2+ (POO, sin frameworks), MySQL/MariaDB, PDO preparado.

## Requisitos
- XAMPP con Apache y MySQL activos
- PHP 8.2 o superior

## Instalación
1. Clonar el repositorio en C:\xampp\htdocs\ajustes-razonables.
2. Copiar .env.example a .env y ajustar los valores.
3. En phpMyAdmin crear las bases ajustes_razonables y ajustes_test (utf8mb4_unicode_ci).
4. En ajustes_razonables importar database/schema.sql y luego database/seed.sql.

En un servidor real, configura `public/` como raíz web (document root) para que los
archivos de configuración, código fuente y documentación queden fuera del área pública.
Las URLs de XAMPP indicadas abajo siguen usando `/ajustes-razonables/public/`.

## Uso
- Registrar estudiante: http://localhost/ajustes-razonables/public/?ruta=estudiantes/registrar
- Registrar solicitud: http://localhost/ajustes-razonables/public/?ruta=solicitudes/registrar
- Pendientes y cambio de estado: http://localhost/ajustes-razonables/public/?ruta=solicitudes/pendientes

## Pruebas
php tests/run.php

Recrea las tablas en DB_NAME_TEST. No usar una base con datos reales.

## Documentación
D0.md, requirements.md, design.md, PROMPTS.md, TECH_DEBT.md

## Fuera de alcance
Autenticación, adjuntos, historial de estados, exportación de reportes, vista del Docente.
