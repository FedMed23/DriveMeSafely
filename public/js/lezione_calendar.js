document.addEventListener('DOMContentLoaded', function () {
    const element = document.getElementById('calendar');
    if (!element || typeof FullCalendar === 'undefined') return;
    const calendar = new FullCalendar.Calendar(element, {
        initialView: 'dayGridMonth', locale: 'it', firstDay: 1,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
        buttonText: { today: 'Oggi', month: 'Mese', week: 'Settimana', day: 'Giorno' },
        events: window.eventiScuolaGuida || []
    });
    window.currentCalendarInstance = calendar;
    calendar.render();
});
