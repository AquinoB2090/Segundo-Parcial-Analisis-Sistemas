<?php

namespace Tests\Feature\Api;

use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CitaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_lists_doctors_and_patients_as_json(): void
    {
        $this->getJson('/api/doctores')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure(['data' => [['id', 'nombre', 'especialidad', 'email', 'activo']]]);

        $this->getJson('/api/pacientes')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure(['data' => [['id', 'nombre', 'email', 'telefono', 'fecha_nacimiento']]]);
    }

    public function test_lists_appointments_with_relations_in_calendar_order(): void
    {
        $response = $this->getJson('/api/citas')
            ->assertOk()
            ->assertJsonCount(5, 'data')
            ->assertJsonStructure(['data' => [[
                'id', 'paciente_id', 'doctor_id', 'fecha', 'hora_inicio', 'hora_fin',
                'motivo', 'estado', 'doctor', 'paciente', 'created_at', 'updated_at',
            ]]]);

        $fechas = collect($response->json('data'))->pluck('fecha')->all();
        $ordenadas = $fechas;
        sort($ordenadas);
        $this->assertSame($ordenadas, $fechas);
    }

    public function test_filters_appointments_by_doctor_patient_and_date_range(): void
    {
        $doctor = Doctor::query()->where('email', 'ana.lopez@example.test')->firstOrFail();
        $paciente = Paciente::query()->where('email', 'elena.diaz@example.test')->firstOrFail();

        $this->getJson("/api/citas?doctor_id={$doctor->id}&desde=2026-09-21&hasta=2026-09-21")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.doctor_id', $doctor->id);

        $this->getJson("/api/citas?paciente_id={$paciente->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.paciente_id', $paciente->id);

        $this->getJson('/api/citas?hasta=2026-09-18')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_rejects_invalid_filters_with_status_400(): void
    {
        $this->getJson('/api/citas?doctor_id=999999')
            ->assertStatus(400)
            ->assertJsonValidationErrors('doctor_id');

        $this->getJson('/api/citas?desde=2026-09-22&hasta=2026-09-21')
            ->assertStatus(400)
            ->assertJsonValidationErrors('hasta');
    }

    public function test_creates_an_appointment_with_status_201(): void
    {
        $datos = $this->appointmentData([
            'fecha' => '2026-09-25',
            'hora_inicio' => '14:00',
            'hora_fin' => '14:30',
        ]);

        $this->postJson('/api/citas', $datos)
            ->assertCreated()
            ->assertJsonPath('data.estado', 'pendiente')
            ->assertJsonPath('data.fecha', '2026-09-25')
            ->assertJsonPath('data.hora_inicio', '14:00')
            ->assertJsonPath('data.doctor.id', $datos['doctor_id'])
            ->assertJsonPath('data.paciente.id', $datos['paciente_id']);

        $this->assertDatabaseHas('citas', [
            'doctor_id' => $datos['doctor_id'],
            'fecha' => '2026-09-25',
            'hora_inicio' => '14:00:00',
            'estado' => 'pendiente',
        ]);
    }

    public function test_rejects_invalid_appointment_data_with_status_400(): void
    {
        $this->postJson('/api/citas', [
            'paciente_id' => 999999,
            'doctor_id' => 999999,
            'fecha' => '25/09/2026',
            'hora_inicio' => '11:00',
            'hora_fin' => '10:00',
        ])->assertStatus(400)
            ->assertJsonValidationErrors([
                'paciente_id', 'doctor_id', 'fecha', 'hora_fin', 'motivo',
            ]);
    }

    public function test_rejects_overlapping_active_appointment_with_status_409(): void
    {
        $citaExistente = Cita::query()->where('estado', 'pendiente')->firstOrFail();

        $this->postJson('/api/citas', $this->appointmentData([
            'doctor_id' => $citaExistente->doctor_id,
            'fecha' => $citaExistente->fecha->format('Y-m-d'),
            'hora_inicio' => '09:15',
            'hora_fin' => '09:45',
        ]))->assertStatus(409)
            ->assertJsonPath('message', 'El doctor ya tiene una cita activa en el horario solicitado.');
    }

    public function test_allows_adjacent_appointments_and_overlap_with_cancelled_appointments(): void
    {
        $doctor = Doctor::query()->where('email', 'ana.lopez@example.test')->firstOrFail();

        $this->postJson('/api/citas', $this->appointmentData([
            'doctor_id' => $doctor->id,
            'fecha' => '2026-09-21',
            'hora_inicio' => '10:00',
            'hora_fin' => '10:30',
        ]))->assertCreated();

        Cita::create($this->appointmentData([
            'doctor_id' => $doctor->id,
            'fecha' => '2026-09-26',
            'hora_inicio' => '08:00',
            'hora_fin' => '09:00',
            'estado' => 'cancelada',
        ]));

        $this->postJson('/api/citas', $this->appointmentData([
            'doctor_id' => $doctor->id,
            'fecha' => '2026-09-26',
            'hora_inicio' => '08:15',
            'hora_fin' => '08:45',
        ]))->assertCreated();
    }

    public function test_returns_appointment_detail_and_json_404(): void
    {
        $cita = Cita::query()->firstOrFail();

        $this->getJson("/api/citas/{$cita->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $cita->id)
            ->assertJsonPath('data.doctor.id', $cita->doctor_id)
            ->assertJsonPath('data.paciente.id', $cita->paciente_id);

        $this->getJson('/api/citas/999999')
            ->assertNotFound()
            ->assertExactJson(['message' => 'Recurso no encontrado.']);
    }

    public function test_reprograms_and_edits_an_appointment(): void
    {
        $cita = Cita::query()->where('estado', 'pendiente')->firstOrFail();

        $this->putJson("/api/citas/{$cita->id}", [
            'fecha' => '2026-09-27',
            'hora_inicio' => '15:00',
            'hora_fin' => '15:45',
            'motivo' => 'Consulta reprogramada',
        ])->assertOk()
            ->assertJsonPath('data.fecha', '2026-09-27')
            ->assertJsonPath('data.hora_inicio', '15:00')
            ->assertJsonPath('data.motivo', 'Consulta reprogramada');

        $cita->refresh();
        $this->assertSame('2026-09-27', $cita->fecha->format('Y-m-d'));
        $this->assertSame('15:00', substr($cita->hora_inicio, 0, 5));
    }

    public function test_rejects_empty_or_conflicting_reprogramming(): void
    {
        $citas = Cita::query()
            ->where('doctor_id', Doctor::query()->where('email', 'ana.lopez@example.test')->value('id'))
            ->whereDate('fecha', '2026-09-21')
            ->orderBy('hora_inicio')
            ->get();

        $this->putJson("/api/citas/{$citas[0]->id}", [])
            ->assertStatus(400)
            ->assertJsonValidationErrors('cita');

        $this->putJson("/api/citas/{$citas[0]->id}", [
            'hora_inicio' => '09:45',
            'hora_fin' => '10:15',
        ])->assertStatus(409);
    }

    public function test_changes_status_without_deleting_history(): void
    {
        $cita = Cita::query()->where('estado', 'pendiente')->firstOrFail();

        $this->patchJson("/api/citas/{$cita->id}/estado", ['estado' => 'cancelada'])
            ->assertOk()
            ->assertJsonPath('data.estado', 'cancelada');

        $this->assertDatabaseHas('citas', ['id' => $cita->id, 'estado' => 'cancelada']);

        $this->patchJson("/api/citas/{$cita->id}/estado", ['estado' => 'desconocido'])
            ->assertStatus(400)
            ->assertJsonValidationErrors('estado');
    }

    public function test_rejects_reactivating_a_cancelled_appointment_that_now_overlaps(): void
    {
        $activa = Cita::query()->where('estado', 'pendiente')->firstOrFail();
        $cancelada = Cita::create($this->appointmentData([
            'doctor_id' => $activa->doctor_id,
            'fecha' => $activa->fecha->format('Y-m-d'),
            'hora_inicio' => '09:10',
            'hora_fin' => '09:20',
            'estado' => 'cancelada',
        ]));

        $this->patchJson("/api/citas/{$cancelada->id}/estado", ['estado' => 'confirmada'])
            ->assertStatus(409);

        $this->assertDatabaseHas('citas', ['id' => $cancelada->id, 'estado' => 'cancelada']);
    }

    private function appointmentData(array $overrides = []): array
    {
        return [
            'paciente_id' => Paciente::query()->firstOrFail()->id,
            'doctor_id' => Doctor::query()->firstOrFail()->id,
            'fecha' => '2026-09-25',
            'hora_inicio' => '14:00',
            'hora_fin' => '14:30',
            'motivo' => 'Consulta desde pruebas de API',
            ...$overrides,
        ];
    }
}
