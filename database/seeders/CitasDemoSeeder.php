<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CitasDemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $doctores = collect([
                ['nombre' => 'Ana Lopez', 'especialidad' => 'Medicina general', 'email' => 'ana.lopez@example.test'],
                ['nombre' => 'Carlos Perez', 'especialidad' => 'Pediatria', 'email' => 'carlos.perez@example.test'],
                ['nombre' => 'Maria Garcia', 'especialidad' => 'Cardiologia', 'email' => 'maria.garcia@example.test'],
            ])->map(fn (array $datos) => Doctor::firstOrCreate(['email' => $datos['email']], $datos));

            $pacientes = collect([
                ['nombre' => 'Luis Ramirez', 'email' => 'luis.ramirez@example.test', 'fecha_nacimiento' => '1994-05-12'],
                ['nombre' => 'Sofia Morales', 'email' => 'sofia.morales@example.test', 'fecha_nacimiento' => '2016-08-20'],
                ['nombre' => 'Pedro Castillo', 'email' => 'pedro.castillo@example.test', 'fecha_nacimiento' => '1982-11-03'],
                ['nombre' => 'Elena Diaz', 'email' => 'elena.diaz@example.test', 'fecha_nacimiento' => '1990-02-14'],
                ['nombre' => 'Jorge Reyes', 'email' => 'jorge.reyes@example.test', 'fecha_nacimiento' => '1975-07-09'],
            ])->map(fn (array $datos) => Paciente::firstOrCreate(['email' => $datos['email']], $datos));

            $citas = [
                [0, 0, '2026-09-21', '09:00:00', '09:30:00', 'Consulta general de ejemplo', 'pendiente'],
                [0, 3, '2026-09-21', '09:30:00', '10:00:00', 'Control de seguimiento de ejemplo', 'confirmada'],
                [1, 1, '2026-09-22', '10:00:00', '10:30:00', 'Control pediatrico de ejemplo', 'confirmada'],
                [2, 2, '2026-09-23', '11:00:00', '11:45:00', 'Evaluacion cardiologica de ejemplo', 'cancelada'],
                [2, 4, '2026-09-18', '08:00:00', '08:45:00', 'Control cardiologico de ejemplo', 'atendida'],
            ];

            foreach ($citas as [$doctor, $paciente, $fecha, $inicio, $fin, $motivo, $estado]) {
                Cita::firstOrCreate([
                    'doctor_id' => $doctores[$doctor]->id,
                    'paciente_id' => $pacientes[$paciente]->id,
                    'fecha' => $fecha,
                    'hora_inicio' => $inicio,
                ], [
                    'hora_fin' => $fin,
                    'motivo' => $motivo,
                    'estado' => $estado,
                ]);
            }
        });
    }
}
