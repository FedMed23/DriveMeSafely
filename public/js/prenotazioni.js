/** Gestisce il passaggio tra calendario/storico e moduli di prenotazione. */
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach((tab) => tab.classList.remove('active'));

    const tabDaMostrare = document.getElementById(tabId);
    if (!tabDaMostrare) return;

    tabDaMostrare.classList.add('active');
    if (tabId === 'tab-calendario' && window.currentCalendarInstance) {
        window.currentCalendarInstance.updateSize();
    }
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-tab-target]').forEach((button) => {
        button.addEventListener('click', () => switchTab(button.dataset.tabTarget));
    });
});
