<?php
declare(strict_types=1);

namespace App\Service;

use App\Exception\EstudianteNoEncontradoException;
use App\Exception\TipoAjusteInvalidoException;
use App\Exception\ValidacionException;
use App\Model\Estado;
use App\Model\Solicitud;
use App\Model\TipoAjuste;
use App\Repository\EstudianteRepository;
use App\Repository\SolicitudRepository;
use App\Repository\TipoAjusteRepository;

/**
 * Aplica las reglas de registro de solicitudes de ajuste razonable.
 */
final class SolicitudService
{
    /**
     * Recibe los repositorios necesarios para validar y guardar solicitudes.
     */
    public function __construct(
        private readonly SolicitudRepository $solicitudRepository,
        private readonly EstudianteRepository $estudianteRepository,
        private readonly TipoAjusteRepository $tipoAjusteRepository
    ) {
    }

    /**
     * Valida y registra la solicitud en el estado inicial RECIBIDA.
     *
     * @throws ValidacionException Si falta un campo obligatorio.
     * @throws TipoAjusteInvalidoException Si el tipo de ajuste no es válido.
     * @throws EstudianteNoEncontradoException Si el estudiante no existe.
     */
    public function registrar(
        string $documentoEstudiante,
        string $asignatura,
        string $tipoAjusteValor
    ): Solicitud {
        $documentoEstudiante = trim($documentoEstudiante);
        $asignatura = trim($asignatura);
        $tipoAjusteValor = trim($tipoAjusteValor);

        $errores = [];
        if ($documentoEstudiante === '') {
            $errores['documento_estudiante'] = 'Ingrese el documento del estudiante.';
        } elseif (preg_match('/^[0-9]{8}$/D', $documentoEstudiante) !== 1) {
            $errores['documento_estudiante'] = 'El documento debe tener exactamente 8 dígitos.';
        }
        if ($asignatura === '') {
            $errores['asignatura'] = 'La asignatura es obligatoria.';
        } elseif (preg_match_all('/./us', $asignatura, $coincidencias) === false) {
            $errores['asignatura'] = 'Ingrese la asignatura con texto válido.';
        } elseif (count($coincidencias[0]) > 100) {
            $errores['asignatura'] = 'La asignatura no puede exceder 100 caracteres.';
        }
        if ($tipoAjusteValor === '') {
            $errores['tipo_ajuste'] = 'Seleccione un tipo de ajuste.';
        }
        if ($errores !== []) {
            throw new ValidacionException('Complete los campos obligatorios.', $errores);
        }

        $tipoAjuste = TipoAjuste::desdeValor($tipoAjusteValor);
        $idTipoAjuste = $this->tipoAjusteRepository->idPorValor($tipoAjuste->value);
        if ($idTipoAjuste === null) {
            throw new TipoAjusteInvalidoException(
                'El tipo de ajuste seleccionado no está disponible.',
                ['tipo_ajuste' => 'Seleccione un tipo de ajuste disponible.']
            );
        }
        if (!$this->estudianteRepository->existeDocumento($documentoEstudiante)) {
            throw new EstudianteNoEncontradoException(
                'No existe un estudiante registrado con ese documento.',
                ['documento_estudiante' => 'No se encontró un estudiante con este documento.']
            );
        }

        $solicitud = new Solicitud(
            null,
            $documentoEstudiante,
            $idTipoAjuste,
            $tipoAjuste,
            $asignatura,
            Estado::RECIBIDA,
            null,
            null,
            null
        );
        $idSolicitud = $this->solicitudRepository->guardar($solicitud);

        return new Solicitud(
            $idSolicitud,
            $solicitud->documentoEstudiante,
            $solicitud->idTipoAjuste,
            $solicitud->tipoAjuste,
            $solicitud->asignatura,
            $solicitud->estado,
            $solicitud->motivoRechazo,
            $solicitud->createdAt,
            $solicitud->updatedAt
        );
    }
}
