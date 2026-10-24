SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS solicitud, estudiante, tipo_ajuste;
SET FOREIGN_KEY_CHECKS=1;

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
