<?php
declare(strict_types=1);

namespace App\Exception;

/**
 * Indica que una solicitud intenta avanzar por una transición no permitida.
 */
class TransicionInvalidaException extends ValidacionException
{
}
