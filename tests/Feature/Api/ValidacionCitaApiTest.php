<?php

namespace Tests\Feature\Api;

use App\Models\Cita;
use App\Models\Doctor;
use App\Models\Paciente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidacionCitaApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_required_fields_return_spanish_errors_and_do_not_persist(): void
    {
        $cantidadInicial = Cita::query()->count();

        $this->postJson('/api/citas', [])
            ->assertStatus(400)
            ->assertJsonPath('message', 'Los datos proporcionados no son validos.')
            ->assertJsonPath('errors.paciente_id.0', 'El campo paciente es obligatorio.')
            ->assertJsonPath('errors.doctor_id.0', 'El campo doctor es obligatorio.')
            ->assertJsonPath('errors.fecha.0', 'El campo fecha es obligatorio.')
            ->assertJsonPath('errors.hora_inicio.0', 'El campo hora de inicio es obligatorio.')
            ->assertJsonPath('errors.hora_fin.0', 'El campo hora de fin es obligatorio.')
            ->assertJsonPath('errors.motivo.0', 'El campo motivo es obligatorio.');

        $this->assertSame($cantidadInicial, Cita::query()->count());
    }

    public function test_rejects_missing_patient_and_inactive_doctor(): void
    {
        $doctor = Doctor::query()->firstOrFail();
        $doctor->update(['activo' => false]);

        $this->postJson('/api/citas', $this->appointmentData([
            'paciente_id' => 999999,
            'doctor_id' => $doctor->id,
        ]))->assertStatus(400)
            ->assertJsonPath('errors.paciente_id.0', 'El valor seleccionado para paciente no es valido.')
            ->assertJsonPath('errors.doctor_id.0', 'El valor seleccionado para doctor no es valido.');
    }

    public function test_rejects_invalid_date_time_order_and_long_reason(): void
    {
        $this->postJson('/api/citas', $this->appointmentData([
            'fecha' => '2026-02-30',
            'hora_inicio' => '24:00',
            'hora_fin' => '09:00',
            'motivo' => str_repeat('a', 1001),
        ]))->assertStatus(400)
            ->assertJsonValidationErrors(['fecha', 'hora_inicio', 'motivo']);

        $this->postJson('/api/citas', $this->appointmentData([
            'hora_inicio' => '10:00',
            'hora_fin' => '10:00',
        ]))->assertStatus(400)
            ->assertJsonValidationErrors('hora_fin');
    }

    public function test_rejects_partial_update_that_makes_start_equal_to_end(): void
    {
        $cita = Cita::query()->where('estado', 'pendiente')->firstOrFail();
        $horaOriginal = $cita->hora_inicio;

        $this->putJson("/api/citas/{$cita->id}", [
            'hora_inicio' => substr($cita->hora_fin, 0, 5),
        ])->assertStatus(400)
            ->assertJsonValidationErrors('hora_fin');

        $this->assertSame($horaOriginal, $cita->fresh()->hora_inicio);
    }

    public function test_rejects_each_kind_of_overlap_without_persisting(): void
    {
        $cita = Cita::query()->where('estado', 'pendiente')->firstOrFail();
        $cantidadInicial = Cita::query()->count();
        $horarios = [
            ['09:00', '09:30'],
            ['09:05', '09:20'],
            ['08:45', '09:15'],
            ['09:15', '09:45'],
            ['08:45', '09:45'],
        ];

        foreach ($horarios as [$inicio, $fin]) {
            $this->postJson('/api/citas', $this->appointmentData([
                'doctor_id' => $cita->doctor_id,
                'fecha' => $cita->fecha->format('Y-m-d'),
                'hora_inicio' => $inicio,
                'hora_fin' => $fin,
            ]))->assertStatus(409)
                ->assertExactJson([
                    'message' => 'El doctor ya tiene una cita activa en el horario solicitado.',
                ]);
        }

        $this->assertSame($cantidadInicial, Cita::query()->count());
    }

    public function test_same_time_is_available_for_a_different_doctor(): void
    {
        $cita = Cita::query()->where('estado', 'pendiente')->firstOrFail();
        $otroDoctor = Doctor::query()->whereKeyNot($cita->doctor_id)->firstOrFail();

        $this->postJson('/api/citas', $this->appointmentData([
            'doctor_id' => $otroDoctor->id,
            'fecha' => $cita->fecha->format('Y-m-d'),
            'hora_inicio' => substr($cita->hora_inicio, 0, 5),
            'hora_fin' => substr($cita->hora_fin, 0, 5),
        ]))->assertCreated();
    }

    public function test_rejects_unknown_state_and_preserves_current_state(): void
    {
        $cita = Cita::query()->where('estado', 'pendiente')->firstOrFail();

        $this->patchJson("/api/citas/{$cita->id}/estado", ['estado' => 'ausente'])
            ->assertStatus(400)
            ->assertJsonPath('message', 'Los datos proporcionados no son validos.')
            ->assertJsonValidationErrors('estado');

        $this->assertSame('pendiente', $cita->fresh()->estado);
    }

    public function test_malformed_json_returns_status_400_as_json(): void
    {
        $this->call(
            'POST',
            '/api/citas',
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            content: '{"fecha":',
        )->assertStatus(400)
            ->assertHeader('content-type', 'application/json');
    }

    private function appointmentData(array $overrides = []): array
    {
        return [
            'paciente_id' => Paciente::query()->firstOrFail()->id,
            'doctor_id' => Doctor::query()->firstOrFail()->id,
            'fecha' => '2026-09-29',
            'hora_inicio' => '13:00',
            'hora_fin' => '13:30',
            'motivo' => 'Validacion de cita',
            ...$overrides,
        ];
    }
}
