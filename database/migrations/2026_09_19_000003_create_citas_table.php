<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paciente_id')->constrained('pacientes')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('doctores')->restrictOnDelete();
            $table->date('fecha');
            $table->time('hora_inicio');
            $table->time('hora_fin');
            $table->text('motivo');
            $table->enum('estado', ['pendiente', 'confirmada', 'cancelada', 'atendida'])
                ->default('pendiente');
            $table->timestamps();

            $table->index(['doctor_id', 'fecha', 'hora_inicio', 'hora_fin'], 'citas_doctor_horario_index');
            $table->index(['paciente_id', 'fecha']);
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
