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
