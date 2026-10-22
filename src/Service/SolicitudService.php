<?php
declare(strict_types=1);

namespace App\Service;

use App\Exception\EstudianteNoEncontradoException;
use App\Exception\MotivoVacioException;
use App\Exception\TipoAjusteInvalidoException;
use App\Exception\TransicionInvalidaException;
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

    /**
     * Busca una solicitud por su identificador.
     */
    public function buscarPorId(int $idSolicitud): ?Solicitud
    {
        return $this->solicitudRepository->buscarPorId($idSolicitud);
    }

    /**
     * Cambia el estado solo si la transición y el motivo de rechazo son válidos.
     *
     * @throws ValidacionException Si la solicitud no existe.
     * @throws TransicionInvalidaException Si el destino no está permitido.
     * @throws MotivoVacioException Si el motivo de rechazo no tiene entre 10 y 500 caracteres.
     */
    public function cambiarEstado(
        int $idSolicitud,
        Estado $nuevoEstado,
        ?string $motivo = null
    ): Solicitud {
        $solicitud = $this->solicitudRepository->buscarPorId($idSolicitud);
        if ($solicitud === null) {
            throw new ValidacionException('No se encontró la solicitud indicada.');
        }

        if (!$solicitud->estado->puedeTransitarA($nuevoEstado)) {
            throw new TransicionInvalidaException(
                'No se permite cambiar el estado de '
                    . $solicitud->estado->value . ' a ' . $nuevoEstado->value . '.',
                ['estado' => 'La transición de estado solicitada no está permitida.']
            );
        }

        $motivoNormalizado = null;
        if ($nuevoEstado === Estado::RECHAZADA) {
            $motivoNormalizado = trim($motivo ?? '');
            $longitud = preg_match_all('/./us', $motivoNormalizado, $coincidencias);
            if ($longitud === false || $longitud < 10 || $longitud > 500) {
                throw new MotivoVacioException(
                    'El motivo de rechazo debe contener entre 10 y 500 caracteres.',
                    ['motivo_rechazo' => 'Ingrese un motivo de rechazo de 10 a 500 caracteres.']
                );
            }
        }

        $this->solicitudRepository->actualizarEstado(
            $idSolicitud,
            $nuevoEstado,
            $motivoNormalizado
        );

        $solicitudActualizada = $this->solicitudRepository->buscarPorId($idSolicitud);
        if ($solicitudActualizada === null) {
            throw new ValidacionException('No se pudo recuperar la solicitud actualizada.');
        }

        return $solicitudActualizada;
    }

    /**
     * Devuelve todas las solicitudes que todavía están pendientes.
     *
     * @return Solicitud[]
     */
    public function listarPendientes(): array
    {
        return $this->solicitudRepository->listarPendientes();
    }

    /**
     * Busca solicitudes pendientes por documento exacto o nombre parcial.
     *
     * @return Solicitud[]
     */
    public function filtrarPorEstudiante(string $criterio): array
    {
        return $this->solicitudRepository->filtrarPorEstudiante(trim($criterio));
    }

    /**
     * Busca solicitudes pendientes por coincidencia parcial de asignatura.
     *
     * @return Solicitud[]
     */
    public function filtrarPorAsignatura(string $asignatura): array
    {
        return $this->solicitudRepository->filtrarPorAsignatura(trim($asignatura));
    }
}
