<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#0f766e">
        <title>Agenda medica | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="app-shell">
            <header class="app-header">
                <a class="brand" href="/" aria-label="Clinica Horizonte, agenda medica">
                    <span class="brand-mark" aria-hidden="true"><i data-lucide="cross"></i></span>
                    <span><strong>Clinica Horizonte</strong><small>Agenda medica</small></span>
                </a>
                <div class="header-date" id="header-date" aria-live="polite"></div>
            </header>

            <main class="workspace">
                <section class="page-heading" aria-labelledby="page-title">
                    <div><p class="eyebrow">Programacion clinica</p><h1 id="page-title">Citas medicas</h1></div>
                    <button class="button button-primary" id="new-appointment" type="button">
                        <i data-lucide="plus" aria-hidden="true"></i>Nueva cita
                    </button>
                </section>

                <section class="calendar-toolbar" aria-label="Filtros de agenda">
                    <label class="field field-filter" for="doctor-filter">
                        <span>Doctor</span>
                        <span class="select-control">
                            <i data-lucide="stethoscope" aria-hidden="true"></i>
                            <select id="doctor-filter"><option value="">Todos los doctores</option></select>
                            <i data-lucide="chevron-down" aria-hidden="true"></i>
                        </span>
                    </label>
                    <label class="field field-filter" for="patient-filter">
                        <span>Paciente</span>
                        <span class="select-control">
                            <i data-lucide="user-round" aria-hidden="true"></i>
                            <select id="patient-filter"><option value="">Todos los pacientes</option></select>
                            <i data-lucide="chevron-down" aria-hidden="true"></i>
                        </span>
                    </label>
                    <button class="icon-button refresh-button" id="refresh-calendar" type="button" title="Actualizar agenda" aria-label="Actualizar agenda">
                        <i data-lucide="refresh-cw" aria-hidden="true"></i>
                    </button>
                    <div class="status-legend" aria-label="Estados de las citas">
                        <span><i class="status-dot status-pending"></i>Pendiente</span>
                        <span><i class="status-dot status-confirmed"></i>Confirmada</span>
                        <span><i class="status-dot status-attended"></i>Atendida</span>
                        <span><i class="status-dot status-cancelled"></i>Cancelada</span>
                    </div>
                </section>

                <section class="calendar-surface" aria-label="Calendario de citas">
                    <div class="calendar-loading" id="calendar-loading" hidden>
                        <span class="spinner" aria-hidden="true"></span>Actualizando agenda
                    </div>
                    <div id="calendar"></div>
                </section>
            </main>
        </div>

        <dialog class="dialog" id="appointment-dialog" aria-labelledby="appointment-dialog-title">
            <form class="dialog-panel" id="appointment-form" novalidate>
                <header class="dialog-header">
                    <div><p class="eyebrow" id="appointment-dialog-mode">Nueva cita</p><h2 id="appointment-dialog-title">Programar atencion</h2></div>
                    <button class="icon-button" type="button" data-close-dialog="appointment-dialog" title="Cerrar" aria-label="Cerrar"><i data-lucide="x" aria-hidden="true"></i></button>
                </header>
                <div class="form-alert" id="appointment-errors" role="alert" hidden></div>
                <input id="appointment-id" name="id" type="hidden">
                <div class="form-grid">
                    <label class="field field-span-2" for="patient-input"><span>Paciente</span><select id="patient-input" name="paciente_id" required></select><small class="field-error" data-error-for="paciente_id"></small></label>
                    <label class="field field-span-2" for="doctor-input"><span>Doctor</span><select id="doctor-input" name="doctor_id" required></select><small class="field-error" data-error-for="doctor_id"></small></label>
                    <label class="field field-span-2" for="date-input"><span>Fecha</span><input id="date-input" name="fecha" type="date" required><small class="field-error" data-error-for="fecha"></small></label>
                    <label class="field" for="start-time-input"><span>Hora de inicio</span><input id="start-time-input" name="hora_inicio" type="time" step="300" required><small class="field-error" data-error-for="hora_inicio"></small></label>
                    <label class="field" for="end-time-input"><span>Hora de fin</span><input id="end-time-input" name="hora_fin" type="time" step="300" required><small class="field-error" data-error-for="hora_fin"></small></label>
                    <label class="field field-span-2" for="reason-input"><span>Motivo</span><textarea id="reason-input" name="motivo" rows="3" maxlength="1000" required></textarea><small class="field-error" data-error-for="motivo"></small></label>
                </div>
                <footer class="dialog-actions">
                    <button class="button button-secondary" type="button" data-close-dialog="appointment-dialog">Cancelar</button>
                    <button class="button button-primary" id="save-appointment" type="submit"><i data-lucide="check" aria-hidden="true"></i>Guardar cita</button>
                </footer>
            </form>
        </dialog>

        <dialog class="dialog" id="detail-dialog" aria-labelledby="detail-dialog-title">
            <section class="dialog-panel detail-panel">
                <header class="dialog-header">
                    <div><p class="eyebrow">Detalle de cita</p><h2 id="detail-dialog-title">Cita medica</h2></div>
                    <button class="icon-button" type="button" data-close-dialog="detail-dialog" title="Cerrar" aria-label="Cerrar"><i data-lucide="x" aria-hidden="true"></i></button>
                </header>
                <div class="detail-status-row"><span class="status-badge" id="detail-status"></span><span class="detail-id" id="detail-id"></span></div>
                <dl class="detail-list">
                    <div><dt><i data-lucide="user-round" aria-hidden="true"></i>Paciente</dt><dd id="detail-patient"></dd></div>
                    <div><dt><i data-lucide="stethoscope" aria-hidden="true"></i>Doctor</dt><dd id="detail-doctor"></dd></div>
                    <div><dt><i data-lucide="calendar-days" aria-hidden="true"></i>Fecha</dt><dd id="detail-date"></dd></div>
                    <div><dt><i data-lucide="clock-3" aria-hidden="true"></i>Horario</dt><dd id="detail-time"></dd></div>
                </dl>
                <div class="detail-reason"><span>Motivo</span><p id="detail-reason"></p></div>
                <label class="field" for="detail-state-input">
                    <span>Estado</span>
                    <select id="detail-state-input">
                        <option value="pendiente">Pendiente</option><option value="confirmada">Confirmada</option>
                        <option value="atendida">Atendida</option><option value="cancelada">Cancelada</option>
                    </select>
                </label>
                <div class="form-alert" id="detail-errors" role="alert" hidden></div>
                <footer class="dialog-actions detail-actions">
                    <button class="button button-secondary" id="edit-appointment" type="button"><i data-lucide="pencil" aria-hidden="true"></i>Editar</button>
                    <button class="button button-primary" id="save-state" type="button"><i data-lucide="check" aria-hidden="true"></i>Guardar estado</button>
                </footer>
            </section>
        </dialog>

        <div class="toast-region" id="toast-region" aria-live="polite" aria-atomic="true"></div>
    </body>
</html>
