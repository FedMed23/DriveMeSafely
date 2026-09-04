'use strict';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('iscrizione-form');

    if (!form) {
        return;
    }

    const rules = {
        username: {
            pattern: /^[A-Za-z0-9_.-]{3,50}$/,
            message: 'Lo username deve avere 3-50 caratteri (lettere, numeri, trattini, punti o underscore).'
        },
        nome: {
            pattern: /^[\p{L}' -]{2,50}$/u,
            message: 'Il nome deve contenere da 2 a 50 caratteri.'
        },
        cognome: {
            pattern: /^[\p{L}' -]{2,50}$/u,
            message: 'Il cognome deve contenere da 2 a 50 caratteri.'
        },
        codiceFiscale: {
            pattern: /^[A-Z0-9]{16}$/,
            message: 'Il codice fiscale deve contenere 16 caratteri alfanumerici.'
        },
        luogoNascita: {
            pattern: /^[\p{L} .'-]{2,100}$/u,
            message: 'Inserisci un luogo di nascita valido.'
        },
        indirizzo: {
            pattern: /^[\p{L}\p{N} .,'\/-]{5,100}$/u,
            message: 'Inserisci un indirizzo valido.'
        },
        telefono: {
            pattern: /^\+?[0-9 ]{9,15}$/,
            message: 'Il numero deve contenere da 9 a 15 cifre.'
        },
        password: {
            pattern: /^(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*[^A-Za-z0-9]).{8,64}$/,
            message: 'La password deve avere 8-64 caratteri, maiuscola, minuscola, numero e simbolo.'
        }
    };

    const getErrorElement = (field) => {
        let error = field.parentElement.querySelector('.campo-errore');

        if (!error) {
            error = document.createElement('small');
            error.className = 'campo-errore';
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
            message = 'Questo campo e obbligatorio.';
        } else if (rule && value !== '' && !rule.pattern.test(field.name === 'codiceFiscale'
            ? value.toUpperCase()
            : value)) {
            message = rule.message;
        } else if (field.name === 'telefono' && value !== '') {
            const digits = value.replace(/[^0-9]/g, '');
            if (!rule.pattern.test(value) || digits.length < 9 || digits.length > 15) {
                message = rule.message;
            }
        } else if (field.name === 'email' && value !== ''
            && !field.validity.valid) {
            message = 'Inserisci un indirizzo email valido.';
        } else if (field.name === 'dataNascita' && value !== '') {
            const birthDate = new Date(`${value}T00:00:00`);
            const today = new Date();
            const limiteSecolare = new Date(
                today.getFullYear() - 120,
                today.getMonth(),
                today.getDate()
            );
            const tipoPatente = (form.dataset.tipoPatente || '').toUpperCase().trim();

            let etaMinima = 18;
            if (tipoPatente === 'AM') {
                etaMinima = 14;
            } else if (tipoPatente === 'A1' || tipoPatente === 'B1') {
                etaMinima = 16;
            } else if (['C', 'CE', 'D1', 'D1E'].includes(tipoPatente)) {
                etaMinima = 21;
            } else if (['A', 'D', 'DE'].includes(tipoPatente)) {
                etaMinima = 24;
            } else {
                etaMinima = 18;
            }

            const minimumDate = new Date(
                today.getFullYear() - etaMinima,
                today.getMonth(),
                today.getDate()
            );

            if (isNaN(birthDate.getTime()) || birthDate > today || birthDate < limiteSecolare) {
                message = 'Inserisci una data di nascita valida.';
            } else if (birthDate > minimumDate) {
                message = `Devi avere almeno ${etaMinima} anni per la patente ${tipoPatente || 'scelta'}.`;
            }
        }

        error.textContent = message;
        field.setCustomValidity(message);
        field.classList.toggle('campo-non-valido', message !== '');

        return message === '';
    };

    Object.keys(rules).concat(['email', 'dataNascita']).forEach((name) => {
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
                submitBtn.textContent = 'Iscrizione in corso...';
            }
        }
    });
});
