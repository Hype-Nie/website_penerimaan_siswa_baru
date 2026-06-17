document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm');

            if (message && ! window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const loadingOverlay = document.querySelector('[data-loading-overlay]');
    const loadingTitle = loadingOverlay ? loadingOverlay.querySelector('[data-loading-title]') : null;
    const loadingText = loadingOverlay ? loadingOverlay.querySelector('[data-loading-text]') : null;

    const showLoadingOverlay = (form) => {
        if (! loadingOverlay) {
            return;
        }

        if (loadingTitle) {
            loadingTitle.textContent = form.getAttribute('data-loading-title') || 'Memproses Data';
        }

        if (loadingText) {
            loadingText.textContent = form.getAttribute('data-loading-message') || 'Mohon tunggu, sistem sedang memproses data.';
        }

        loadingOverlay.classList.add('is-visible');
        loadingOverlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('psb-loading-active');

        form.querySelectorAll('button[type="submit"], input[type="submit"]').forEach((submit) => {
            submit.disabled = true;

            if (submit.tagName === 'BUTTON') {
                submit.dataset.originalText = submit.dataset.originalText || submit.innerHTML;
                submit.innerHTML = submit.getAttribute('data-loading-button-text') || 'Memproses...';
            }
        });
    };

    document.querySelectorAll('form[data-loading]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            if (form.getAttribute('data-loading-submitted') === 'true') {
                event.preventDefault();
                return;
            }

            form.setAttribute('data-loading-submitted', 'true');
            showLoadingOverlay(form);
        });
    });

    document.querySelectorAll('.custom-file-input').forEach((input) => {
        input.addEventListener('change', () => {
            const label = input.nextElementSibling;

            if (label && input.files.length > 0) {
                label.textContent = input.files[0].name;
            }
        });
    });
});
