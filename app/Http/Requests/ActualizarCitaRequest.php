<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ActualizarCitaRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'paciente_id' => ['sometimes', 'integer', 'exists:pacientes,id'],
            'doctor_id' => [
                'sometimes',
                'integer',
                Rule::exists('doctores', 'id')->where('activo', true),
            ],
            'fecha' => ['sometimes', 'date_format:Y-m-d'],
            'hora_inicio' => ['sometimes', 'date_format:H:i'],
            'hora_fin' => ['sometimes', 'date_format:H:i'],
            'motivo' => ['sometimes', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $cita = $this->route('cita');
            $inicio = $this->input('hora_inicio', $cita?->hora_inicio);
            $fin = $this->input('hora_fin', $cita?->hora_fin);

            if ($inicio !== null && $fin !== null && $fin <= $inicio) {
                $validator->errors()->add('hora_fin', 'La hora de fin debe ser posterior a la hora de inicio.');
            }
        }];
    }
}
