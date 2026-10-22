<?php
declare(strict_types=1);

namespace App\Model;

/**
 * Tipos de discapacidad admitidos para registrar estudiantes.
 */
enum TipoDiscapacidad: string
{
    case VISUAL = 'VISUAL';
    case AUDITIVA = 'AUDITIVA';
    case FISICA = 'FISICA';
    case INTELECTUAL = 'INTELECTUAL';
}
