<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CitaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'paciente_id' => $this->paciente_id,
            'doctor_id' => $this->doctor_id,
            'fecha' => $this->fecha->format('Y-m-d'),
            'hora_inicio' => substr($this->hora_inicio, 0, 5),
            'hora_fin' => substr($this->hora_fin, 0, 5),
            'motivo' => $this->motivo,
            'estado' => $this->estado,
            'doctor' => DoctorResource::make($this->whenLoaded('doctor')),
            'paciente' => PacienteResource::make($this->whenLoaded('paciente')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
