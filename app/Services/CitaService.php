<?php

namespace App\Services;

use App\Exceptions\HorarioNoDisponibleException;
use App\Models\Cita;
use App\Models\Doctor;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CitaService
{
    public function crear(array $datos): Cita
    {
        return DB::transaction(function () use ($datos): Cita {
            $datos = $this->normalizarHoras($datos);
            $this->bloquearDoctor($datos['doctor_id']);
            $this->comprobarDisponibilidad($datos);

            return Cita::create($datos)->refresh()->load(['doctor', 'paciente']);
        });
    }

    public function actualizar(Cita $cita, array $datos): Cita
    {
        return DB::transaction(function () use ($cita, $datos): Cita {
            $datos = $this->normalizarHoras($datos);
            $valores = [...$cita->only([
                'paciente_id', 'doctor_id', 'fecha', 'hora_inicio', 'hora_fin', 'motivo', 'estado',
            ]), ...$datos];

            $doctorIds = array_unique([$cita->doctor_id, (int) $valores['doctor_id']]);
            sort($doctorIds);
            foreach ($doctorIds as $doctorId) {
                $this->bloquearDoctor($doctorId);
            }

            $this->comprobarDisponibilidad($valores, $cita->id);
            $cita->update(Arr::except($datos, 'estado'));

            return $cita->refresh()->load(['doctor', 'paciente']);
        });
    }

    public function actualizarEstado(Cita $cita, string $estado): Cita
    {
        return DB::transaction(function () use ($cita, $estado): Cita {
            $this->bloquearDoctor($cita->doctor_id);

            if ($estado !== 'cancelada') {
                $this->comprobarDisponibilidad([
                    ...$cita->only(['doctor_id', 'fecha', 'hora_inicio', 'hora_fin']),
                    'estado' => $estado,
                ], $cita->id);
            }

            $cita->update(['estado' => $estado]);

            return $cita->refresh()->load(['doctor', 'paciente']);
        });
    }

    private function bloquearDoctor(int $doctorId): void
    {
        Doctor::query()->whereKey($doctorId)->lockForUpdate()->firstOrFail();
    }

    private function comprobarDisponibilidad(array $datos, ?int $ignorarCitaId = null): void
    {
        if (($datos['estado'] ?? 'pendiente') === 'cancelada') {
            return;
        }

        $existeConflicto = Cita::query()
            ->where('doctor_id', $datos['doctor_id'])
            ->whereDate('fecha', $datos['fecha'])
            ->where('estado', '!=', 'cancelada')
            ->where('hora_inicio', '<', $datos['hora_fin'])
            ->where('hora_fin', '>', $datos['hora_inicio'])
            ->when($ignorarCitaId, fn ($query) => $query->whereKeyNot($ignorarCitaId))
            ->exists();

        if ($existeConflicto) {
            throw new HorarioNoDisponibleException;
        }
    }

    private function normalizarHoras(array $datos): array
    {
        foreach (['hora_inicio', 'hora_fin'] as $campo) {
            if (isset($datos[$campo]) && strlen($datos[$campo]) === 5) {
                $datos[$campo] .= ':00';
            }
        }

        return $datos;
    }
}
