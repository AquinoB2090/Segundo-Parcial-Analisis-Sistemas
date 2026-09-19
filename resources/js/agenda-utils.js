export const STATUS_STYLES = {
    pendiente: { backgroundColor: '#b7791f', borderColor: '#8f5c12', textColor: '#ffffff' },
    confirmada: { backgroundColor: '#16794b', borderColor: '#0f603a', textColor: '#ffffff' },
    atendida: { backgroundColor: '#2f62b5', borderColor: '#244d8e', textColor: '#ffffff' },
    cancelada: { backgroundColor: '#a53b3b', borderColor: '#843030', textColor: '#ffffff' },
};

export function localDate(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

export function localTime(date) {
    return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
}

export function appointmentEvent(appointment) {
    const style = STATUS_STYLES[appointment.estado];

    return {
        id: String(appointment.id),
        title: `${appointment.paciente.nombre} · ${appointment.doctor.nombre}`,
        start: `${appointment.fecha}T${appointment.hora_inicio}:00`,
        end: `${appointment.fecha}T${appointment.hora_fin}:00`,
        editable: appointment.estado !== 'cancelada' && appointment.estado !== 'atendida',
        classNames: [`appointment-${appointment.estado}`],
        extendedProps: { appointment },
        ...style,
    };
}

export function defaultTimes(date = new Date()) {
    const start = new Date(date);
    start.setMinutes(Math.ceil(start.getMinutes() / 30) * 30, 0, 0);
    if (start.getHours() < 8 || start.getHours() >= 18) start.setHours(9, 0, 0, 0);
    const end = new Date(start.getTime() + 30 * 60 * 1000);

    return { start: localTime(start), end: localTime(end) };
}

export function calendarQuery(start, end, filters = {}) {
    const inclusiveEnd = new Date(end);
    inclusiveEnd.setDate(inclusiveEnd.getDate() - 1);
    const parameters = new URLSearchParams({
        desde: localDate(start),
        hasta: localDate(inclusiveEnd),
    });

    if (filters.doctorId) parameters.set('doctor_id', filters.doctorId);
    if (filters.patientId) parameters.set('paciente_id', filters.patientId);

    return parameters;
}

export function eventChangePayload(event) {
    return {
        fecha: localDate(event.start),
        hora_inicio: localTime(event.start),
        hora_fin: localTime(event.end),
    };
}
