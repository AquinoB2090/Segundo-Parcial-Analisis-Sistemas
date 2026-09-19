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
            $this->bloquearDoctor($datos['doctor_id']);
            $this->comprobarDisponibilidad($datos);

            return Cita::create($datos)->load(['doctor', 'paciente']);
        });
    }

    public function actualizar(Cita $cita, array $datos): Cita
    {
        return DB::transaction(function () use ($cita, $datos): Cita {
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
}
