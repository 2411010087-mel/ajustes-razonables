<?php
declare(strict_types=1);

namespace App\Model;

/**
 * Tipos de documento que acreditan la discapacidad.
 */
enum TipoDocumentoAcreditacion: string
{
    case CONADIS = 'CONADIS';
    case CERT_MED = 'CERT-MED';
}
