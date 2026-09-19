import './bootstrap';
import {
    CalendarDays, Check, ChevronDown, Clock3, createIcons, Cross,
    Pencil, Plus, RefreshCw, Stethoscope, UserRound, X,
} from 'lucide';

createIcons({
    icons: { CalendarDays, Check, ChevronDown, Clock3, Cross, Pencil, Plus, RefreshCw, Stethoscope, UserRound, X },
});

const headerDate = document.querySelector('#header-date');

if (headerDate) {
    headerDate.textContent = new Intl.DateTimeFormat('es-GT', {
        weekday: 'long', day: 'numeric', month: 'long',
    }).format(new Date());
}
