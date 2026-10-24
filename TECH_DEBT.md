# TECH_DEBT

| # | Qué | Por qué | Cuándo pagar |
|---|-----|---------|--------------|
| 1 | Sin autenticación ni roles | Fuera de alcance del examen; cualquiera con acceso a la URL puede registrar y cambiar estados | Antes de desplegar: login, roles y control de acceso por acción |
| 2 | Sin historial de cambios de estado | El caso solo pide el estado actual | Cuando Bienestar necesite auditoría: tabla historial_estado |
| 3 | Reporte de totales (RF5) sin interfaz | Se priorizaron los módulos 1 y 2 | Siguiente iteración: ReporteService y vista de totales |
| 4 | Reglas de validación como literales (8 dígitos, 100, 50, 10-500) | Rapidez de desarrollo | Al tocar validaciones: centralizar en constantes |
| 5 | Sin cabeceras de seguridad HTTP ni HTTPS | Entorno local de XAMPP | Antes de desplegar: CSP, X-Frame-Options, cookies seguras |
