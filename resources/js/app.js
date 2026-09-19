import './bootstrap';
import { Calendar } from '@fullcalendar/core';
import esLocale from '@fullcalendar/core/locales/es';
import dayGridPlugin from '@fullcalendar/daygrid';
import interactionPlugin from '@fullcalendar/interaction';
import timeGridPlugin from '@fullcalendar/timegrid';
import {
    appointmentEvent, calendarQuery, defaultTimes, eventChangePayload, localDate,
} from './agenda-utils';
import {
    CalendarDays, Check, ChevronDown, CircleAlert, CircleCheck, Clock3, createIcons,
    Cross, Pencil, Plus, RefreshCw, Stethoscope, UserRound, X,
} from 'lucide';

const ICONS = {
    CalendarDays, Check, ChevronDown, CircleAlert, CircleCheck, Clock3,
    Cross, Pencil, Plus, RefreshCw, Stethoscope, UserRound, X,
};

const renderIcons = () => createIcons({ icons: ICONS });

renderIcons();

const headerDate = document.querySelector('#header-date');

if (headerDate) {
    headerDate.textContent = new Intl.DateTimeFormat('es-GT', {
        weekday: 'long', day: 'numeric', month: 'long',
    }).format(new Date());
}

const calendarElement = document.querySelector('#calendar');

if (calendarElement) {
    const elements = {
        appointmentDialog: document.querySelector('#appointment-dialog'),
        appointmentForm: document.querySelector('#appointment-form'),
        appointmentErrors: document.querySelector('#appointment-errors'),
        appointmentId: document.querySelector('#appointment-id'),
        appointmentMode: document.querySelector('#appointment-dialog-mode'),
        appointmentTitle: document.querySelector('#appointment-dialog-title'),
        calendarLoading: document.querySelector('#calendar-loading'),
        dateInput: document.querySelector('#date-input'),
        detailDate: document.querySelector('#detail-date'),
        detailDialog: document.querySelector('#detail-dialog'),
        detailDoctor: document.querySelector('#detail-doctor'),
        detailErrors: document.querySelector('#detail-errors'),
        detailId: document.querySelector('#detail-id'),
        detailPatient: document.querySelector('#detail-patient'),
        detailReason: document.querySelector('#detail-reason'),
        detailState: document.querySelector('#detail-state-input'),
        detailStatus: document.querySelector('#detail-status'),
        detailTime: document.querySelector('#detail-time'),
        doctorFilter: document.querySelector('#doctor-filter'),
        doctorInput: document.querySelector('#doctor-input'),
        editAppointment: document.querySelector('#edit-appointment'),
        endTimeInput: document.querySelector('#end-time-input'),
        newAppointment: document.querySelector('#new-appointment'),
        patientFilter: document.querySelector('#patient-filter'),
        patientInput: document.querySelector('#patient-input'),
        reasonInput: document.querySelector('#reason-input'),
        refreshCalendar: document.querySelector('#refresh-calendar'),
        saveAppointment: document.querySelector('#save-appointment'),
        saveState: document.querySelector('#save-state'),
        startTimeInput: document.querySelector('#start-time-input'),
        toastRegion: document.querySelector('#toast-region'),
    };

    let doctors = [];
    let patients = [];
    let selectedAppointment = null;

    class ApiError extends Error {
        constructor(status, payload) {
            super(payload?.message || 'No fue posible completar la solicitud.');
            this.status = status;
            this.errors = payload?.errors || {};
        }
    }

    async function apiRequest(path, options = {}) {
        const response = await fetch(path, {
            ...options,
            headers: {
                Accept: 'application/json',
                ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                ...options.headers,
            },
        });
        const payload = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new ApiError(response.status, payload);
        }

        return payload.data;
    }

    function populateSelect(select, records, placeholder, label) {
        const selected = select.value;
        select.replaceChildren();
        const emptyOption = document.createElement('option');
        emptyOption.value = '';
        emptyOption.textContent = placeholder;
        select.append(emptyOption);

        records.forEach((record) => {
            const option = document.createElement('option');
            option.value = String(record.id);
            option.textContent = label(record);
            select.append(option);
        });
        select.value = selected;
    }

    async function loadCatalogs() {
        [doctors, patients] = await Promise.all([
            apiRequest('/api/doctores'),
            apiRequest('/api/pacientes'),
        ]);

        populateSelect(elements.doctorFilter, doctors, 'Todos los doctores', (doctor) => `${doctor.nombre} · ${doctor.especialidad}`);
        populateSelect(elements.patientFilter, patients, 'Todos los pacientes', (patient) => patient.nombre);
        populateSelect(elements.doctorInput, doctors, 'Seleccione un doctor', (doctor) => `${doctor.nombre} · ${doctor.especialidad}`);
        populateSelect(elements.patientInput, patients, 'Seleccione un paciente', (patient) => patient.nombre);
    }

    function clearFormErrors() {
        elements.appointmentErrors.hidden = true;
        elements.appointmentErrors.textContent = '';
        document.querySelectorAll('[data-error-for]').forEach((field) => {
            field.textContent = '';
            field.closest('.field')?.classList.remove('has-error');
        });
    }

    function showFormError(error) {
        clearFormErrors();
        const entries = Object.entries(error.errors || {});

        if (entries.length) {
            entries.forEach(([field, messages]) => {
                const target = document.querySelector(`[data-error-for="${field}"]`);
                if (target) {
                    target.textContent = messages[0];
                    target.closest('.field')?.classList.add('has-error');
                }
            });
        }

        elements.appointmentErrors.textContent = error.message;
        elements.appointmentErrors.hidden = false;
    }

    function showToast(message, kind = 'success') {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.dataset.kind = kind;
        toast.setAttribute('role', kind === 'error' ? 'alert' : 'status');

        const icon = document.createElement('i');
        icon.dataset.lucide = kind === 'error' ? 'circle-alert' : 'circle-check';
        const text = document.createElement('span');
        text.textContent = message;
        toast.append(icon, text);
        elements.toastRegion.append(toast);
        renderIcons();

        window.setTimeout(() => toast.remove(), 4200);
    }

    function closeDialog(dialog) {
        if (dialog.open) dialog.close();
    }

    function openAppointmentDialog(appointment = null, prefillDate = null) {
        clearFormErrors();
        elements.appointmentForm.reset();
        elements.appointmentId.value = appointment?.id || '';
        elements.appointmentMode.textContent = appointment ? 'Editar cita' : 'Nueva cita';
        elements.appointmentTitle.textContent = appointment ? 'Actualizar atencion' : 'Programar atencion';

        if (appointment) {
            elements.patientInput.value = String(appointment.paciente_id);
            elements.doctorInput.value = String(appointment.doctor_id);
            elements.dateInput.value = appointment.fecha;
            elements.startTimeInput.value = appointment.hora_inicio;
            elements.endTimeInput.value = appointment.hora_fin;
            elements.reasonInput.value = appointment.motivo;
        } else {
            const date = prefillDate || new Date();
            const times = defaultTimes(date);
            elements.dateInput.value = localDate(date);
            elements.startTimeInput.value = times.start;
            elements.endTimeInput.value = times.end;
            elements.doctorInput.value = elements.doctorFilter.value;
            elements.patientInput.value = elements.patientFilter.value;
        }

        elements.appointmentDialog.showModal();
        window.setTimeout(() => elements.patientInput.focus(), 0);
    }

    function formatDisplayDate(value) {
        return new Intl.DateTimeFormat('es-GT', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric',
        }).format(new Date(`${value}T12:00:00`));
    }

    function renderDetail(appointment) {
        selectedAppointment = appointment;
        elements.detailId.textContent = `Cita #${appointment.id}`;
        elements.detailPatient.textContent = appointment.paciente.nombre;
        elements.detailDoctor.textContent = `${appointment.doctor.nombre} · ${appointment.doctor.especialidad}`;
        elements.detailDate.textContent = formatDisplayDate(appointment.fecha);
        elements.detailTime.textContent = `${appointment.hora_inicio} – ${appointment.hora_fin}`;
        elements.detailReason.textContent = appointment.motivo;
        elements.detailState.value = appointment.estado;
        elements.detailStatus.textContent = appointment.estado;
        elements.detailStatus.dataset.status = appointment.estado;
        elements.detailErrors.hidden = true;
        elements.detailErrors.textContent = '';
    }

    async function openDetailDialog(id) {
        try {
            const appointment = await apiRequest(`/api/citas/${id}`);
            renderDetail(appointment);
            elements.detailDialog.showModal();
        } catch (error) {
            showToast(error.message, 'error');
        }
    }

    async function persistEventChange(info) {
        const { event } = info;
        try {
            const appointment = await apiRequest(`/api/citas/${event.id}`, {
                method: 'PUT',
                body: JSON.stringify(eventChangePayload(event)),
            });
            event.setExtendedProp('appointment', appointment);
            showToast('La cita fue reprogramada.');
        } catch (error) {
            info.revert();
            showToast(error.message, 'error');
        }
    }

    const calendar = new Calendar(calendarElement, {
        plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
        locale: esLocale,
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek',
        },
        buttonText: { today: 'Hoy', month: 'Mes', week: 'Semana' },
        firstDay: 1,
        height: 'auto',
        dayMaxEvents: 3,
        nowIndicator: true,
        selectable: true,
        editable: true,
        eventDurationEditable: true,
        eventStartEditable: true,
        allDaySlot: false,
        slotMinTime: '07:00:00',
        slotMaxTime: '20:00:00',
        slotDuration: '00:30:00',
        snapDuration: '00:15:00',
        loading(isLoading) {
            elements.calendarLoading.hidden = !isLoading;
        },
        events: async (info, success, failure) => {
            const parameters = calendarQuery(info.start, info.end, {
                doctorId: elements.doctorFilter.value,
                patientId: elements.patientFilter.value,
            });

            try {
                const appointments = await apiRequest(`/api/citas?${parameters}`);
                success(appointments.map(appointmentEvent));
            } catch (error) {
                showToast(error.message, 'error');
                failure(error);
            }
        },
        dateClick(info) {
            openAppointmentDialog(null, info.date);
        },
        eventClick(info) {
            openDetailDialog(info.event.id);
        },
        eventDrop: persistEventChange,
        eventResize: persistEventChange,
        eventDidMount(info) {
            const appointment = info.event.extendedProps.appointment;
            info.el.title = `${appointment.paciente.nombre} · ${appointment.hora_inicio}-${appointment.hora_fin}`;
            info.el.setAttribute('aria-label', info.el.title);
        },
    });

    async function initialize() {
        try {
            elements.calendarLoading.hidden = false;
            await loadCatalogs();
            calendar.render();
        } catch (error) {
            elements.calendarLoading.hidden = true;
            showToast(error.message, 'error');
        }
    }

    elements.appointmentForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        clearFormErrors();
        const values = Object.fromEntries(new FormData(elements.appointmentForm));
        const id = values.id;
        delete values.id;
        elements.saveAppointment.disabled = true;

        try {
            await apiRequest(id ? `/api/citas/${id}` : '/api/citas', {
                method: id ? 'PUT' : 'POST',
                body: JSON.stringify(values),
            });
            closeDialog(elements.appointmentDialog);
            calendar.refetchEvents();
            showToast(id ? 'La cita fue actualizada.' : 'La cita fue creada.');
        } catch (error) {
            showFormError(error);
        } finally {
            elements.saveAppointment.disabled = false;
        }
    });

    elements.saveState.addEventListener('click', async () => {
        if (!selectedAppointment) return;
        elements.saveState.disabled = true;
        elements.detailErrors.hidden = true;

        try {
            const appointment = await apiRequest(`/api/citas/${selectedAppointment.id}/estado`, {
                method: 'PATCH',
                body: JSON.stringify({ estado: elements.detailState.value }),
            });
            renderDetail(appointment);
            calendar.refetchEvents();
            showToast('El estado fue actualizado.');
        } catch (error) {
            elements.detailErrors.textContent = error.message;
            elements.detailErrors.hidden = false;
        } finally {
            elements.saveState.disabled = false;
        }
    });

    elements.editAppointment.addEventListener('click', () => {
        if (!selectedAppointment) return;
        closeDialog(elements.detailDialog);
        openAppointmentDialog(selectedAppointment);
    });
    elements.newAppointment.addEventListener('click', () => openAppointmentDialog());
    elements.refreshCalendar.addEventListener('click', () => calendar.refetchEvents());
    elements.doctorFilter.addEventListener('change', () => calendar.refetchEvents());
    elements.patientFilter.addEventListener('change', () => calendar.refetchEvents());

    document.querySelectorAll('[data-close-dialog]').forEach((button) => {
        button.addEventListener('click', () => closeDialog(document.querySelector(`#${button.dataset.closeDialog}`)));
    });
    document.querySelectorAll('dialog').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) closeDialog(dialog);
        });
    });

    initialize();
}
