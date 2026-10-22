<?php
declare(strict_types=1);

namespace App\Repository;

use App\Exception\DuplicadoException;
use App\Model\Estudiante;
use App\Model\TipoDiscapacidad;
use App\Model\TipoDocumentoAcreditacion;
use PDO;
use PDOException;

/**
 * Implementa la persistencia de estudiantes mediante consultas PDO preparadas.
 */
final class EstudianteRepositoryPdo implements EstudianteRepository
{
    /**
     * Recibe la conexión PDO utilizada por el repositorio.
     */
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Busca un estudiante por su documento.
     */
    public function buscarPorDocumento(string $documento): ?Estudiante
    {
        $sentencia = $this->pdo->prepare(
            'SELECT documento, nombre, carrera, tipo_discapacidad,
                    tipo_documento_acreditacion, numero_documento_acreditacion
             FROM estudiante
             WHERE documento = :documento'
        );
        $sentencia->execute(['documento' => $documento]);
        $fila = $sentencia->fetch();

        return $fila === false ? null : $this->hidratar($fila);
    }

    /**
     * Indica si ya existe un estudiante con el documento indicado.
     */
    public function existeDocumento(string $documento): bool
    {
        $sentencia = $this->pdo->prepare(
            'SELECT 1 FROM estudiante WHERE documento = :documento'
        );
        $sentencia->execute(['documento' => $documento]);

        return $sentencia->fetchColumn() !== false;
    }

    /**
     * Guarda un estudiante nuevo y transforma la clave duplicada en error de dominio.
     *
     * @throws DuplicadoException Si el documento ya está registrado.
     */
    public function guardar(Estudiante $estudiante): void
    {
        $sentencia = $this->pdo->prepare(
            'INSERT INTO estudiante (
                documento, nombre, carrera, tipo_discapacidad,
                tipo_documento_acreditacion, numero_documento_acreditacion
            ) VALUES (
                :documento, :nombre, :carrera, :tipo_discapacidad,
                :tipo_documento_acreditacion, :numero_documento_acreditacion
            )'
        );

        try {
            $sentencia->execute([
                'documento' => $estudiante->documento,
                'nombre' => $estudiante->nombre,
                'carrera' => $estudiante->carrera,
                'tipo_discapacidad' => $estudiante->tipoDiscapacidad->value,
                'tipo_documento_acreditacion' => $estudiante->tipoDocumentoAcreditacion->value,
                'numero_documento_acreditacion' => $estudiante->numeroDocumentoAcreditacion,
            ]);
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new DuplicadoException(
                    'Ya existe un estudiante registrado con ese documento.',
                    ['documento' => 'Ya existe un estudiante con este documento.'],
                    previous: $e
                );
            }

            throw $e;
        }
    }

    /**
     * Busca estudiantes por coincidencia de documento o nombre.
     *
     * @return Estudiante[]
     */
    public function buscar(string $criterio): array
    {
        $sentencia = $this->pdo->prepare(
            'SELECT documento, nombre, carrera, tipo_discapacidad,
                    tipo_documento_acreditacion, numero_documento_acreditacion
             FROM estudiante
             WHERE documento LIKE :documento OR nombre LIKE :nombre
             ORDER BY nombre, documento'
        );
        $coincidencia = '%' . $criterio . '%';
        $sentencia->execute([
            'documento' => $coincidencia,
            'nombre' => $coincidencia,
        ]);

        $estudiantes = [];
        foreach ($sentencia->fetchAll() as $fila) {
            $estudiantes[] = $this->hidratar($fila);
        }

        return $estudiantes;
    }

    /**
     * Convierte una fila de la base de datos a un modelo de estudiante.
     *
     * @param array<string, mixed> $fila
     */
    private function hidratar(array $fila): Estudiante
    {
        return new Estudiante(
            (string) $fila['documento'],
            (string) $fila['nombre'],
            (string) $fila['carrera'],
            TipoDiscapacidad::from((string) $fila['tipo_discapacidad']),
            TipoDocumentoAcreditacion::from((string) $fila['tipo_documento_acreditacion']),
            (string) $fila['numero_documento_acreditacion']
        );
    }
}
