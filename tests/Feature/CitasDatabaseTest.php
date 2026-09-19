<?php

namespace Tests\Feature;

use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitasDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_has_relationships_and_all_appointment_states(): void
    {
        $this->seed();

        $this->assertDatabaseCount('doctores', 3);
        $this->assertDatabaseCount('pacientes', 5);
        $this->assertDatabaseCount('citas', 5);
        $this->assertEqualsCanonicalizing(
            ['pendiente', 'confirmada', 'cancelada', 'atendida'],
            Cita::query()->distinct()->pluck('estado')->all(),
        );

        foreach (Cita::all() as $cita) {
            $this->assertInstanceOf(Doctor::class, $cita->doctor);
            $this->assertInstanceOf(Paciente::class, $cita->paciente);
            $this->assertTrue($cita->doctor->citas->contains($cita));
            $this->assertTrue($cita->paciente->citas->contains($cita));
            $this->assertLessThan($cita->hora_fin, $cita->hora_inicio);
        }
    }

    public function test_reseeding_does_not_duplicate_data_or_reset_a_cancelled_appointment(): void
    {
        $this->seed();
        $cita = Cita::query()->where('estado', 'pendiente')->firstOrFail();
        $cita->update(['estado' => 'cancelada']);

        $this->seed();

        $this->assertDatabaseCount('doctores', 3);
        $this->assertDatabaseCount('pacientes', 5);
        $this->assertDatabaseCount('citas', 5);
        $this->assertSame('cancelada', $cita->fresh()->estado);
    }

    public function test_new_appointments_default_to_pending(): void
    {
        $this->seed();

        $cita = Cita::create($this->appointmentData());

        $this->assertSame('pendiente', $cita->fresh()->estado);
    }

    public function test_an_appointment_cannot_reference_a_missing_doctor(): void
    {
        $this->seed();
        $this->expectException(QueryException::class);

        Cita::create([...$this->appointmentData(), 'doctor_id' => 999999]);
    }

    public function test_an_appointment_cannot_reference_a_missing_patient(): void
    {
        $this->seed();
        $this->expectException(QueryException::class);

        Cita::create([...$this->appointmentData(), 'paciente_id' => 999999]);
    }

    public function test_a_doctor_with_appointments_cannot_be_deleted(): void
    {
        $this->seed();
        $doctor = Cita::query()->firstOrFail()->doctor;
        $this->expectException(QueryException::class);

        $doctor->delete();
    }

    public function test_a_patient_with_appointments_cannot_be_deleted(): void
    {
        $this->seed();
        $paciente = Cita::query()->firstOrFail()->paciente;
        $this->expectException(QueryException::class);

        $paciente->delete();
    }

    public function test_appointments_reject_unknown_states(): void
    {
        $this->seed();
        $this->expectException(QueryException::class);

        Cita::create([...$this->appointmentData(), 'estado' => 'desconocido']);
    }

    private function appointmentData(): array
    {
        return [
            'doctor_id' => Doctor::query()->firstOrFail()->id,
            'paciente_id' => Paciente::query()->firstOrFail()->id,
            'fecha' => '2026-09-24',
            'hora_inicio' => '14:00:00',
            'hora_fin' => '14:30:00',
            'motivo' => 'Consulta de prueba',
        ];
    }
}
