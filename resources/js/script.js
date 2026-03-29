import './bootstrap';

document.getElementById('nav-toggle')?.addEventListener('click', function () {
    const menu = document.getElementById('nav-menu');
    const isExpanded = this.getAttribute('aria-expanded') === 'true';
    this.setAttribute('aria-expanded', String(!isExpanded));
    menu?.classList.toggle('active');
});

const isDoubleConsentEnabled = Boolean(window.appSettings?.doubleConsentEnabled);

if (isDoubleConsentEnabled) {
    const modal = document.getElementById('consent-modal');
    const modalText = document.getElementById('consent-modal-text');
    const cancelButton = document.getElementById('consent-cancel');
    const confirmButton = document.getElementById('consent-confirm');
    let onConfirmAction = null;

    const closeModal = () => {
        if (!modal) {
            return;
        }

        modal.classList.remove('active');
        modal.setAttribute('aria-hidden', 'true');
        onConfirmAction = null;
    };

    const openModal = (message, callback) => {
        if (!modal || !modalText) {
            return;
        }

        modalText.textContent = message;
        onConfirmAction = callback;
        modal.classList.add('active');
        modal.setAttribute('aria-hidden', 'false');
        confirmButton?.focus();
    };

    modal?.addEventListener('click', (event) => {
        const target = event.target;
        if (target instanceof HTMLElement && target.dataset.consentClose === 'true') {
            closeModal();
        }
    });

    cancelButton?.addEventListener('click', () => {
        closeModal();
    });

    confirmButton?.addEventListener('click', () => {
        if (typeof onConfirmAction === 'function') {
            onConfirmAction();
        }
        closeModal();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal?.classList.contains('active')) {
            closeModal();
        }
    });

    document.querySelectorAll('form[data-requires-consent="true"] button[type="submit"], button[data-requires-consent="true"]').forEach((button) => {
        button.addEventListener('click', (event) => {
            const message = button.getAttribute('data-consent-message') || 'Confirmer cette action ?';
            const form = button.closest('form');

            if (!form) {
                return;
            }

            event.preventDefault();
            openModal(message, () => {
                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit(button);
                } else {
                    form.submit();
                }
            });
        });
    });
}

const userSearchInput = document.getElementById('user-search');
const userSelect = document.getElementById('user-select');

if (userSearchInput && userSelect) {
    const normalize = (value) => value
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');

    const baseOptions = Array.from(userSelect.querySelectorAll('option'))
        .filter((option) => option.value !== '')
        .map((option) => ({
            value: option.value,
            label: option.textContent.trim(),
            normalized: normalize(option.textContent.trim()),
        }));

    const renderOptions = (query) => {
        const normalizedQuery = normalize(query.trim());
        const filteredOptions = normalizedQuery === ''
            ? baseOptions
            : baseOptions.filter((option) => option.normalized.includes(normalizedQuery));

        const currentValue = userSelect.value;

        userSelect.innerHTML = '';

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = filteredOptions.length > 0
            ? '— Choisir un utilisateur —'
            : '— Aucun utilisateur trouvé —';
        userSelect.appendChild(placeholder);

        filteredOptions.forEach((option) => {
            const optionElement = document.createElement('option');
            optionElement.value = option.value;
            optionElement.textContent = option.label;
            userSelect.appendChild(optionElement);
        });

        if (filteredOptions.some((option) => option.value === currentValue)) {
            userSelect.value = currentValue;
        }
    };

    userSearchInput.addEventListener('input', (event) => {
        renderOptions(event.target.value);
    });

    renderOptions('');
}
