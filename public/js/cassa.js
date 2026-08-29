document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('dati-cassa');
    const canvas = document.getElementById('graficoCassa');
    const emptyState = document.getElementById('grafico-vuoto');

    if (!root || !canvas || !emptyState) {
        return;
    }

    const entrate = Number(root.dataset.entrate || 0);
    const uscite = Number(root.dataset.uscite || 0);

    const data = {
        labels: ['Entrate', 'Uscite'],
        datasets: [{
            label: 'Movimenti giornalieri',
            data: [entrate, uscite],
            backgroundColor: ['#2f855a', '#e53e3e'],
            borderColor: ['#2f855a', '#e53e3e'],
            borderWidth: 1
        }]
    };

    const hasData = entrate > 0 || uscite > 0;

    if (!hasData) {
        emptyState.style.display = 'flex';
        canvas.style.display = 'none';
        return;
    }

    emptyState.style.display = 'none';
    canvas.style.display = 'block';

    new Chart(canvas, {
        type: 'bar',
        data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                title: {
                    display: true,
                    text: 'Entrate e uscite giornaliere',
                    font: { size: 14 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: value => `€${Number(value).toFixed(2)}`
                    }
                }
            }
        }
    });
});
