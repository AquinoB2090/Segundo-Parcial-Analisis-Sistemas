import assert from 'node:assert/strict';
import test from 'node:test';
import {
    appointmentEvent, calendarQuery, defaultTimes, eventChangePayload, localDate, localTime,
} from '../../resources/js/agenda-utils.js';

const appointment = {
    id: 8,
    fecha: '2026-09-21',
    hora_inicio: '09:00',
    hora_fin: '09:30',
    estado: 'confirmada',
    paciente: { nombre: 'Elena Diaz' },
    doctor: { nombre: 'Ana Lopez' },
};

test('formats local dates and times without UTC displacement', () => {
    const date = new Date(2026, 8, 21, 9, 5);

    assert.equal(localDate(date), '2026-09-21');
    assert.equal(localTime(date), '09:05');
});

test('maps API appointments to colored FullCalendar events', () => {
    const event = appointmentEvent(appointment);

    assert.equal(event.id, '8');
    assert.equal(event.title, 'Elena Diaz · Ana Lopez');
    assert.equal(event.start, '2026-09-21T09:00:00');
    assert.equal(event.end, '2026-09-21T09:30:00');
    assert.equal(event.editable, true);
    assert.equal(event.backgroundColor, '#16794b');
    assert.deepEqual(event.classNames, ['appointment-confirmada']);
});

test('locks cancelled and attended events against dragging', () => {
    assert.equal(appointmentEvent({ ...appointment, estado: 'cancelada' }).editable, false);
    assert.equal(appointmentEvent({ ...appointment, estado: 'atendida' }).editable, false);
});

test('builds an inclusive calendar range with optional filters', () => {
    const query = calendarQuery(
        new Date(2026, 7, 31),
        new Date(2026, 9, 12),
        { doctorId: '2', patientId: '4' },
    );

    assert.equal(query.get('desde'), '2026-08-31');
    assert.equal(query.get('hasta'), '2026-10-11');
    assert.equal(query.get('doctor_id'), '2');
    assert.equal(query.get('paciente_id'), '4');
});

test('creates stable half-hour defaults inside clinic hours', () => {
    assert.deepEqual(defaultTimes(new Date(2026, 8, 21, 10, 7)), { start: '10:30', end: '11:00' });
    assert.deepEqual(defaultTimes(new Date(2026, 8, 21, 20, 0)), { start: '09:00', end: '09:30' });
});

test('maps a dragged event to the API reprogramming contract', () => {
    assert.deepEqual(eventChangePayload({
        start: new Date(2026, 8, 24, 14, 15),
        end: new Date(2026, 8, 24, 15, 0),
    }), {
        fecha: '2026-09-24',
        hora_inicio: '14:15',
        hora_fin: '15:00',
    });
});
