'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('pagamento-form') || document.querySelector('form[action*="pagamento"]');

    if (!form) {
        return;
    }

    const rules = {
        numeroCarta: {
            pattern: /^[\d\s-]{16,23}$/,
            message: 'Inserisci un numero di carta valido (16 cifre).'
        },
        nomeTitolare: {
            pattern: /^[\p{L}' -]{2,50}$/u,
            message: 'Il nome del titolare deve avere tra 2 e 50 caratteri.'
        },
        cognomeTitolare: {
            pattern: /^[\p{L}' -]{2,50}$/u,
            message: 'Il cognome del titolare deve avere tra 2 e 50 caratteri.'
        },
        cvv: {
            pattern: /^[0-9]{3,4}$/,
            message: 'Il codice CVV deve essere di 3 o 4 cifre.'
        }
    };

    const getErrorElement = (field) => {
        let error = field.parentElement.querySelector('.campo-errore');

        if (!error) {
            error = document.createElement('small');
            error.className = 'campo-errore';
            error.style.color = '#c0392b';
            error.style.display = 'block';
            error.style.marginTop = '4px';
            error.setAttribute('aria-live', 'polite');
            field.parentElement.appendChild(error);
        }

        return error;
    };

    const validateField = (field) => {
        const rule = rules[field.name];
        const error = getErrorElement(field);
        let message = '';
        const value = field.value.trim();

        if (field.required && value === '') {
            message = 'Questo campo è obbligatorio.';
        } else if (rule && value !== '' && !rule.pattern.test(value)) {
            message = rule.message;
        } else if (field.name === 'numeroCarta' && value !== '') {
            const digits = value.replace(/\D/g, '');
            if (digits.length !== 16) {
                message = 'Il numero di carta deve contenere esattamente 16 cifre.';
            }
        } else if (field.name === 'dataScadenza' && value !== '') {
            const expDate = new Date(`${value}T23:59:59`);
            const today = new Date();

            if (isNaN(expDate.getTime())) {
                message = 'Data di scadenza non valida.';
            } else {
                // Calcola fine mese selezionato
                const endOfMonth = new Date(expDate.getFullYear(), expDate.getMonth() + 1, 0, 23, 59, 59);
                if (endOfMonth < today) {
                    message = 'La carta risulta scaduta.';
                }
            }
        }

        error.textContent = message;
        field.setCustomValidity(message);
        field.classList.toggle('campo-non-valido', message !== '');

        return message === '';
    };

    // Auto-formattazione numero carta a blocchi di 4 cifre
    const numeroCartaField = form.elements['numeroCarta'];
    if (numeroCartaField) {
        numeroCartaField.addEventListener('input', (e) => {
            let cursor = e.target.selectionStart;
            let val = e.target.value.replace(/\D/g, '');
            let formatted = val.match(/.{1,4}/g)?.join(' ') || val;
            e.target.value = formatted.substring(0, 23);
        });
    }

    Object.keys(rules).concat(['dataScadenza']).forEach((name) => {
        const field = form.elements[name];

        if (field) {
            field.addEventListener('blur', () => validateField(field));
            field.addEventListener('input', () => {
                if (field.classList.contains('campo-non-valido')) {
                    validateField(field);
                }
            });
        }
    });

    form.addEventListener('submit', (event) => {
        const fields = Array.from(form.querySelectorAll('input:not([type="hidden"])'));
        const valid = fields.every(validateField);

        if (!valid) {
            event.preventDefault();
            const firstInvalid = form.querySelector('.campo-non-valido');

            if (firstInvalid) {
                firstInvalid.focus();
            }
        } else {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.textContent = 'Elaborazione pagamento in corso...';
            }
        }
    });
});
