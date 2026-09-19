<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\HorarioNoDisponibleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\ActualizarCitaRequest;
use App\Http\Requests\ActualizarEstadoCitaRequest;
use App\Http\Requests\GuardarCitaRequest;
use App\Http\Requests\ListarCitasRequest;
use App\Http\Resources\CitaResource;
use App\Models\Cita;
use App\Services\CitaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CitaController extends Controller
{
    public function __construct(private readonly CitaService $citaService) {}

    public function index(ListarCitasRequest $request): AnonymousResourceCollection
    {
        $filtros = $request->validated();
        $citas = Cita::query()
            ->with(['doctor', 'paciente'])
            ->when($filtros['doctor_id'] ?? null, fn ($query, $id) => $query->where('doctor_id', $id))
            ->when($filtros['paciente_id'] ?? null, fn ($query, $id) => $query->where('paciente_id', $id))
            ->when($filtros['desde'] ?? null, fn ($query, $fecha) => $query->whereDate('fecha', '>=', $fecha))
            ->when($filtros['hasta'] ?? null, fn ($query, $fecha) => $query->whereDate('fecha', '<=', $fecha))
            ->orderBy('fecha')
            ->orderBy('hora_inicio')
            ->get();

        return CitaResource::collection($citas);
    }

    public function store(GuardarCitaRequest $request): JsonResponse|CitaResource
    {
        try {
            $cita = $this->citaService->crear($request->validated());
        } catch (HorarioNoDisponibleException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }

        return (new CitaResource($cita))->response()->setStatusCode(201);
    }

    public function show(Cita $cita): CitaResource
    {
        return new CitaResource($cita->load(['doctor', 'paciente']));
    }

    public function update(ActualizarCitaRequest $request, Cita $cita): JsonResponse|CitaResource
    {
        try {
            $cita = $this->citaService->actualizar($cita, $request->validated());
        } catch (HorarioNoDisponibleException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }

        return new CitaResource($cita);
    }

    public function updateEstado(ActualizarEstadoCitaRequest $request, Cita $cita): JsonResponse|CitaResource
    {
        try {
            $cita = $this->citaService->actualizarEstado($cita, $request->validated('estado'));
        } catch (HorarioNoDisponibleException $exception) {
            return response()->json(['message' => $exception->getMessage()], 409);
        }

        return new CitaResource($cita);
    }
}
