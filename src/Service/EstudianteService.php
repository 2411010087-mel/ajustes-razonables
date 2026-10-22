<?php
declare(strict_types=1);

namespace App\Service;

use App\Exception\DuplicadoException;
use App\Exception\ValidacionException;
use App\Model\Estudiante;
use App\Model\TipoDiscapacidad;
use App\Model\TipoDocumentoAcreditacion;
use App\Repository\EstudianteRepository;

/**
 * Aplica las reglas de registro de estudiantes antes de persistirlos.
 */
final class EstudianteService
{
    /**
     * Recibe el repositorio que persiste los estudiantes.
     */
    public function __construct(private readonly EstudianteRepository $repository)
    {
    }

    /**
     * Valida y registra un estudiante con sus datos de acreditación.
     *
     * @throws ValidacionException Si faltan datos o un valor no es válido.
     * @throws DuplicadoException Si el documento ya está registrado.
     */
    public function registrar(
        string $documento,
        string $nombre,
        string $carrera,
        string $tipoDiscapacidad,
        string $tipoDocumentoAcreditacion,
        string $numeroDocumentoAcreditacion
    ): Estudiante {
        $documento = trim($documento);
        $nombre = trim($nombre);
        $carrera = trim($carrera);
        $tipoDiscapacidad = trim($tipoDiscapacidad);
        $tipoDocumentoAcreditacion = trim($tipoDocumentoAcreditacion);
        $numeroDocumentoAcreditacion = trim($numeroDocumentoAcreditacion);

        $errores = [];
        if ($documento === '' || preg_match('/^[0-9]{8}$/D', $documento) !== 1) {
            $errores['documento'] = 'Ingrese un documento de identidad de exactamente 8 dígitos.';
        }
        if ($nombre === '') {
            $errores['nombre'] = 'El nombre es obligatorio.';
        } elseif ($this->longitud($nombre) > 100) {
            $errores['nombre'] = 'El nombre no puede exceder 100 caracteres.';
        }
        if ($carrera === '') {
            $errores['carrera'] = 'La carrera es obligatoria.';
        } elseif ($this->longitud($carrera) > 50) {
            $errores['carrera'] = 'La carrera no puede exceder 50 caracteres.';
        }
        if ($tipoDiscapacidad === '') {
            $errores['tipo_discapacidad'] = 'Seleccione el tipo de discapacidad.';
        } elseif (TipoDiscapacidad::tryFrom($tipoDiscapacidad) === null) {
            $errores['tipo_discapacidad'] = 'Seleccione un tipo de discapacidad válido.';
        }
        if ($tipoDocumentoAcreditacion === '') {
            $errores['tipo_documento_acreditacion'] = 'Seleccione el documento de acreditación.';
        } elseif (TipoDocumentoAcreditacion::tryFrom($tipoDocumentoAcreditacion) === null) {
            $errores['tipo_documento_acreditacion'] = 'Seleccione un documento de acreditación válido.';
        }
        if ($numeroDocumentoAcreditacion === '') {
            $errores['numero_documento_acreditacion'] = 'El número de acreditación es obligatorio.';
        } elseif ($this->longitud($numeroDocumentoAcreditacion) > 50) {
            $errores['numero_documento_acreditacion'] =
                'El número de acreditación no puede exceder 50 caracteres.';
        }
        if ($errores !== []) {
            throw new ValidacionException('Revise los datos del estudiante.', $errores);
        }

        if ($this->repository->existeDocumento($documento)) {
            throw new DuplicadoException(
                'Ya existe un estudiante registrado con ese documento.',
                ['documento' => 'Ya existe un estudiante con este documento.']
            );
        }

        $estudiante = new Estudiante(
            $documento,
            $nombre,
            $carrera,
            TipoDiscapacidad::from($tipoDiscapacidad),
            TipoDocumentoAcreditacion::from($tipoDocumentoAcreditacion),
            $numeroDocumentoAcreditacion
        );
        $this->repository->guardar($estudiante);

        return $estudiante;
    }

    /**
     * Cuenta caracteres Unicode sin depender de una extensión opcional.
     */
    private function longitud(string $valor): int
    {
        $cantidad = preg_match_all('/./us', $valor, $coincidencias);
        if ($cantidad === false) {
            throw new ValidacionException(
                'Uno de los campos contiene texto con codificación no válida.'
            );
        }

        return $cantidad;
    }
}
