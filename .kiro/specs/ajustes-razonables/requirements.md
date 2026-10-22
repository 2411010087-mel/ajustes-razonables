# Requirements Document

## Introduction

La Oficina de Bienestar Universitario recibe las solicitudes de ajustes razonables por correo, WhatsApp y papel, y por eso se pierden, no se sabe cuáles están pendientes y no se puede generar el reporte semestral. Este sistema permite centralizar el registro de estudiantes con discapacidad, la solicitud de ajustes razonables por asignatura, el seguimiento del estado de cada solicitud y la generación de totales para el reporte semestral, cumpliendo con la accesibilidad y la protección de datos personales según la Ley 29733.

## Glossary

- **Sistema**: Aplicación web para la gestión de solicitudes de ajustes razonables de la Oficina de Bienestar Universitario.
- **Estudiante**: Estudiante con discapacidad que registra solicitudes de ajuste razonable.
- **Personal_de_Bienestar**: Personal de la Oficina de Bienestar Universitario que registra estudiantes, evalúa solicitudes y cambia su estado.
- **Solicitud**: Petición de ajuste razonable asociada a un estudiante y a una asignatura, con un tipo de ajuste.
- **Ajuste_Razonable**: Adaptación que se solicita para una asignatura debido a la discapacidad del estudiante.
- **Tipo_de_Ajuste**: Categoría del ajuste solicitado en una solicitud. Valores válidos: TIEMPO_ADICIONAL, MATERIAL_ACCESIBLE, INTERPRETE_LSP, AULA_ACCESIBLE, MOBILIARIO, ACOMPANAMIENTO.
- **Asignatura**: Curso para el cual se solicita el ajuste razonable.
- **Documento**: Número de documento de identidad del estudiante, compuesto por 8 dígitos.
- **Nombre**: Nombre completo del estudiante.
- **Carrera**: Programa de estudios del estudiante.
- **Tipo_de_Discapacidad**: Clasificación de la discapacidad del estudiante; valores válidos: VISUAL, AUDITIVA, FISICA, INTELECTUAL.
- **Documento_de_Acreditacion**: Documento que acredita la discapacidad del estudiante; valores válidos: CONADIS, CERT-MED.
- **CONADIS**: Certificado emitido por el Consejo Nacional para la Integración de la Persona con Discapacidad.
- **CERT-MED**: Certificado médico que acredita la discapacidad.
- **Estado**: Estado actual de una solicitud dentro del flujo de evaluación.
- **RECIBIDA**: Estado inicial de una solicitud recién registrada.
- **EVALUADA**: Estado de una solicitud que ha sido evaluada por el Personal_de_Bienestar.
- **APROBADA**: Estado de una solicitud evaluada que ha sido aprobada.
- **RECHAZADA**: Estado de una solicitud evaluada que ha sido rechazada, acompañada de un motivo.
- **IMPLEMENTADA**: Estado de una solicitud aprobada cuyo ajuste ha sido implementado.
- **Transiciones_validas**: Transiciones de estado permitidas: RECIBIDA→EVALUADA→APROBADA o RECHAZADA; APROBADA→IMPLEMENTADA.
- **Pendiente**: Solicitud cuyo estado no es IMPLEMENTADA ni RECHAZADA.

## Requirements

### RF1: Registro de Estudiantes

**User Story:** Como Personal de Bienestar, quiero registrar a un estudiante con discapacidad, para que pueda gestionar sus solicitudes de ajuste razonable.

#### Acceptance Criteria

1. CUANDO el Personal_de_Bienestar ingrese los datos de un estudiante, EL SISTEMA DEBERÁ registrar al estudiante con documento (8 dígitos), nombre (máximo 100 caracteres), carrera (máximo 50 caracteres), tipo de discapacidad, tipo de documento de acreditación y número de documento de acreditación.
2. CUANDO se seleccione el tipo de discapacidad, EL SISTEMA DEBERÁ restringir los valores a VISUAL, AUDITIVA, FISICA o INTELECTUAL.
3. CUANDO se seleccione el documento de acreditación, EL SISTEMA DEBERÁ restringir los valores a CONADIS o CERT-MED.
4. SI el documento no tiene 8 dígitos, o el nombre excede 100 caracteres, o la carrera excede 50 caracteres, ENTONCES EL SISTEMA DEBERÁ rechazar el registro e indicar el error al Personal_de_Bienestar.
5. SI un estudiante con el mismo documento ya está registrado, ENTONCES EL SISTEMA DEBERÁ rechazar el registro duplicado e indicar el error al Personal_de_Bienestar.
6. SI alguno de los campos obligatorios (nombre, carrera, tipo de discapacidad o documento de acreditación) está vacío, ENTONCES EL SISTEMA DEBERÁ rechazar el registro e indicar al Personal_de_Bienestar el campo faltante.

### RF2: Registro de Solicitudes de Ajuste Razonable

**User Story:** Como Estudiante, quiero registrar una solicitud de ajuste razonable para una asignatura, para que la Oficina de Bienestar pueda evaluarla.

#### Acceptance Criteria

1. CUANDO un Estudiante registre una solicitud con los campos obligatorios estudiante, asignatura y tipo de ajuste, EL SISTEMA DEBERÁ almacenar la solicitud asociada al estudiante, a la asignatura y al tipo de ajuste, y mostrar un mensaje de confirmación de registro exitoso al Estudiante.
2. CUANDO se registre una solicitud, EL SISTEMA DEBERÁ asignarle el estado inicial RECIBIDA.
3. SI los campos obligatorios de la solicitud (estudiante, asignatura y tipo de ajuste) están vacíos o no han sido ingresados, ENTONCES EL SISTEMA DEBERÁ rechazar el registro e indicar los campos faltantes al Estudiante.
4. SI el tipo de ajuste no es uno de los valores válidos (TIEMPO_ADICIONAL, MATERIAL_ACCESIBLE, INTERPRETE_LSP, AULA_ACCESIBLE, MOBILIARIO, ACOMPANAMIENTO) o el estudiante no existe en el sistema, ENTONCES EL SISTEMA DEBERÁ rechazar la solicitud e indicar el error.

### RF3: Cambio de Estado de Solicitud

**User Story:** Como Personal de Bienestar, quiero cambiar el estado de una solicitud, para que se conozca su avance en el proceso de evaluación.

#### Acceptance Criteria

1. CUANDO el Personal_de_Bienestar cambie el estado de una solicitud RECIBIDA, EL SISTEMA DEBERÁ permitir el cambio únicamente a EVALUADA.
2. CUANDO el Personal_de_Bienestar cambie el estado de una solicitud EVALUADA, EL SISTEMA DEBERÁ permitir el cambio únicamente a APROBADA o RECHAZADA.
3. CUANDO el Personal_de_Bienestar cambie el estado de una solicitud APROBADA, EL SISTEMA DEBERÁ permitir el cambio únicamente a IMPLEMENTADA.
4. SI el Personal_de_Bienestar intenta un cambio de estado no incluido en las Transiciones_validas, ENTONCES EL SISTEMA DEBERÁ rechazar el cambio, indicar la transición no permitida y mantener el estado actual de la solicitud.
5. CUANDO una solicitud sea cambiada al estado RECHAZADA, EL SISTEMA DEBERÁ requerir un motivo de rechazo con una longitud entre 10 y 500 caracteres.
6. SI el motivo de rechazo está vacío, ENTONCES EL SISTEMA DEBERÁ rechazar el cambio e indicar que el motivo es obligatorio.

### RF4: Listado de Solicitudes Pendientes

**User Story:** Como Personal de Bienestar, quiero listar las solicitudes pendientes, para que pueda dar seguimiento a las que requieren atención.

#### Acceptance Criteria

1. CUANDO el Personal_de_Bienestar solicite las solicitudes pendientes, EL SISTEMA DEBERÁ listar las solicitudes cuyo estado no sea IMPLEMENTADA ni RECHAZADA (es decir, RECIBIDA, EVALUADA o APROBADA), mostrando para cada solicitud los campos estudiante, asignatura, tipo de ajuste y estado.
2. CUANDO el Personal_de_Bienestar filtre por estudiante, EL SISTEMA DEBERÁ mostrar las solicitudes pendientes asociadas a ese estudiante, utilizando como criterio de búsqueda el documento o el nombre del estudiante.
3. CUANDO el Personal_de_Bienestar filtre por asignatura, EL SISTEMA DEBERÁ mostrar las solicitudes Pendientes asociadas a esa asignatura.
4. SI no existen solicitudes pendientes para el filtro aplicado o sin filtro, ENTONCES EL SISTEMA DEBERÁ mostrar un mensaje indicando que no hay solicitudes pendientes.

### RF5: Reporte de Totales

**User Story:** Como Personal de Bienestar, quiero ver el total de solicitudes por tipo de ajuste y por estado, para que pueda elaborar el reporte semestral.

#### Acceptance Criteria

1. CUANDO el Personal_de_Bienestar solicite el reporte de totales, EL SISTEMA DEBERÁ mostrar el número de solicitudes registradas en el Sistema para cada Tipo_de_Ajuste, mostrando valor cero en los tipos que no tengan solicitudes.
2. CUANDO el Personal_de_Bienestar solicite el reporte de totales, EL SISTEMA DEBERÁ mostrar el número de solicitudes registradas en el Sistema para cada Estado, mostrando valor cero en los estados que no tengan solicitudes.

### RNF1: Accesibilidad

**User Story:** Como estudiante con discapacidad, quiero que los formularios sean accesibles, para que pueda usarlos solo con el teclado y un lector de pantalla.

#### Acceptance Criteria

1. EL SISTEMA DEBERÁ incluir un label con atributo "for" asociado a cada input de los formularios.
2. CUANDO se muestre un mensaje de error en un campo de un formulario, EL SISTEMA DEBERÁ asociar el mensaje a su campo correspondiente mediante aria-describedby.
3. EL SISTEMA DEBERÁ mantener un orden de tabulación que coincida con la secuencia visual de los campos del formulario, de arriba hacia abajo y de izquierda a derecha.
4. EL SISTEMA DEBERÁ asegurar una relación de contraste mínima de 4.5:1 (WCAG AA) entre el texto y el fondo.
5. EL SISTEMA DEBERÁ transmitir toda la información de los formularios sin que el color sea el único medio, de modo que cada indicación basada en color incluya también texto o un icono.
6. EL SISTEMA DEBERÁ permitir que toda la operación de los formularios sea realizada mediante el teclado, sin requerir el uso del ratón.

### RNF2: Protección de Datos Personales (Ley 29733)

**User Story:** Como responsable de datos personales, quiero que el sistema cumpla con la Ley 29733, para que los datos personales se protejan adecuadamente.

#### Acceptance Criteria

1. EL SISTEMA DEBERÁ almacenar únicamente los datos personales necesarios para la gestión de ajustes razonables: documento (8 dígitos), nombre, carrera, tipo de discapacidad, tipo de documento de acreditación y número de documento de acreditación del estudiante.
2. EL SISTEMA DEBERÁ almacenar los datos del estudiante sin permitir el registro de archivos adjuntos.
3. EL SISTEMA DEBERÁ aplicar los principios de protección de datos de la Ley 29733, de modo que los datos personales almacenados se utilicen únicamente para las funciones de gestión de ajustes razonables del sistema: registro de estudiantes, registro de solicitudes, seguimiento de estado y reporte de totales.

### RNF3: Seguridad de Base de Datos (PDO Preparado)

**User Story:** Como desarrollador, quiero que las consultas a la base de datos usen sentencias preparadas, para que se evite la inyección SQL.

#### Acceptance Criteria

1. EL SISTEMA DEBERÁ ejecutar todas las consultas a la base de datos mediante sentencias preparadas con PDO, sin concatenar valores provenientes de la entrada del usuario en la cadena SQL.
2. SI se reciben valores provenientes de la entrada del usuario para una consulta, ENTONCES EL SISTEMA DEBERÁ vincularlos como parámetros de la sentencia preparada.

### RNF4: Seguridad de Formularios (CSRF y Salida Escapada)

**User Story:** Como desarrollador, quiero que los formularios protejan contra CSRF y escapen la salida, para que se eviten ataques de inyección y falsificación de petición.

#### Acceptance Criteria

1. EL SISTEMA DEBERÁ incluir un token CSRF en cada formulario y validarlo al recibir la petición.
2. CUANDO el Sistema muestre datos provenientes de la entrada del usuario o del almacenamiento en la interfaz, EL SISTEMA DEBERÁ escapar la salida para que el contenido se muestre como texto y no se ejecute como código.
3. SI el token CSRF está ausente o es inválido, ENTONCES EL SISTEMA DEBERÁ rechazar la petición, no procesarla e indicar el error al usuario.

### RNF5: Configuración .env con .gitignore

**User Story:** Como desarrollador, quiero que la configuración sensible se cargue desde un archivo .env, para que las credenciales no se expongan en el código fuente.

#### Acceptance Criteria

1. CUANDO el sistema se inicie, EL SISTEMA DEBERÁ cargar las credenciales y parámetros de configuración desde un archivo .env.
2. EL SISTEMA DEBERÁ excluir el archivo .env del repositorio mediante una entrada en el archivo .gitignore.
3. SI el archivo .env no existe o no contiene los parámetros de configuración necesarios al iniciar el sistema, ENTONCES EL SISTEMA DEBERÁ impedir el inicio e indicar un error de configuración al desarrollador.
4. EL SISTEMA DEBERÁ mantener las credenciales y parámetros de configuración únicamente en el archivo .env, sin almacenarlos escritos directamente en los archivos del código fuente.
