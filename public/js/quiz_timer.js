document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('quiz-form');
    const timerDisplay = document.getElementById('timer-display');
    const datiQuiz = document.getElementById('dati-quiz');
    if (!form || !timerDisplay || !datiQuiz) return;

    let secondiRimasti = (parseInt(datiQuiz.dataset.tempo, 10) || 30) * 60;
    let inviatoAutomaticamente = false;

    const aggiornaTimer = () => {
        const minuti = Math.floor(secondiRimasti / 60);
        const secondi = secondiRimasti % 60;
        timerDisplay.textContent = `${String(minuti).padStart(2, '0')}:${String(secondi).padStart(2, '0')}`;

        if (secondiRimasti <= 120) timerDisplay.classList.add('timer-warning');
        if (secondiRimasti <= 0) {
            clearInterval(timer);
            if (!inviatoAutomaticamente) {
                inviatoAutomaticamente = true;
                form.submit();
            }
            return;
        }
        secondiRimasti -= 1;
    };

    const timer = setInterval(aggiornaTimer, 1000);
    aggiornaTimer();
    form.addEventListener('submit', () => clearInterval(timer), { once: true });
});
