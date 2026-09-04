document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;

    const statistiche = window.statisticheQuiz || { totale: 0, superati: 0, nonSuperati: 0 };
    const coloreTesto = '#475569';
    const opzioni = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { labels: { color: coloreTesto, font: { family: 'Segoe UI' } } } }
    };

    const totale = document.getElementById('grafico-quiz-totali');
    if (totale) {
        new Chart(totale, {
            type: 'bar',
            data: {
                labels: ['Quiz svolti'],
                datasets: [{ label: 'Totale', data: [statistiche.totale], backgroundColor: '#2b6cb0', borderRadius: 8, maxBarThickness: 70 }]
            },
            options: { ...opzioni, scales: { y: { beginAtZero: true, ticks: { precision: 0, color: coloreTesto }, grid: { color: '#e2e8f0' } }, x: { ticks: { color: coloreTesto }, grid: { display: false } } } }
        });
    }

    const idoneita = document.getElementById('grafico-idoneita');
    if (idoneita) {
        new Chart(idoneita, {
            type: 'doughnut',
            data: {
                labels: ['Idonei', 'Da ripetere'],
                datasets: [{ data: [statistiche.superati, statistiche.nonSuperati], backgroundColor: ['#10b981', '#f59e0b'], borderWidth: 0, hoverOffset: 5 }]
            },
            options: { ...opzioni, cutout: '67%' }
        });
    }
});
