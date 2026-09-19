<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class ApiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Los datos proporcionados no son validos.',
            'errors' => $validator->errors(),
        ], 400));
    }

    public function messages(): array
    {
        return [
            'required' => 'El campo :attribute es obligatorio.',
            'integer' => 'El campo :attribute debe ser un numero entero.',
            'exists' => 'El valor seleccionado para :attribute no es valido.',
            'date_format' => 'El campo :attribute debe tener el formato :format.',
            'after' => 'El campo :attribute debe ser posterior a :date.',
            'string' => 'El campo :attribute debe ser texto.',
            'max.string' => 'El campo :attribute no debe superar :max caracteres.',
            'enum' => 'El valor seleccionado para :attribute no es valido.',
            'in' => 'El valor seleccionado para :attribute no es valido.',
        ];
    }

    public function attributes(): array
    {
        return [
            'paciente_id' => 'paciente',
            'doctor_id' => 'doctor',
            'fecha' => 'fecha',
            'hora_inicio' => 'hora de inicio',
            'hora_fin' => 'hora de fin',
            'motivo' => 'motivo',
            'estado' => 'estado',
            'desde' => 'fecha desde',
            'hasta' => 'fecha hasta',
        ];
    }
}
