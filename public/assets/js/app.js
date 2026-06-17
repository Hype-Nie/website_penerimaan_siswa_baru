document.addEventListener('DOMContentLoaded', () => {
    const loadingOverlay = document.querySelector('[data-loading-overlay]');
    const loadingTitle = loadingOverlay ? loadingOverlay.querySelector('[data-loading-title]') : null;
    const loadingText = loadingOverlay ? loadingOverlay.querySelector('[data-loading-text]') : null;
    const confirmOverlay = document.querySelector('[data-confirm-overlay]');
    const confirmTitle = confirmOverlay ? confirmOverlay.querySelector('[data-confirm-title]') : null;
    const confirmText = confirmOverlay ? confirmOverlay.querySelector('[data-confirm-text]') : null;
    const confirmCancel = confirmOverlay ? confirmOverlay.querySelector('[data-confirm-cancel]') : null;
    const confirmAccept = confirmOverlay ? confirmOverlay.querySelector('[data-confirm-accept]') : null;
    let pendingConfirmForm = null;
    let pendingConfirmSubmitter = null;

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

    const shouldUseConfirmation = (form) => {
        if (form.hasAttribute('data-no-confirm')) {
            return false;
        }

        return form.hasAttribute('data-confirm') || form.method.toLowerCase() !== 'get';
    };

    const shouldUseLoading = (form) => {
        if (form.hasAttribute('data-no-loading')) {
            return false;
        }

        return form.hasAttribute('data-loading') || form.method.toLowerCase() !== 'get';
    };

    const formActionValue = (form, submitter) => {
        if (submitter && submitter.name === 'action') {
            return submitter.value.toLowerCase();
        }

        const actionInput = form.querySelector('[name="action"]');

        return actionInput ? String(actionInput.value).toLowerCase() : '';
    };

    const confirmationType = (form, submitter) => {
        const explicitType = (submitter && submitter.getAttribute('data-confirm-type')) || form.getAttribute('data-confirm-type');

        if (explicitType) {
            return explicitType;
        }

        const action = formActionValue(form, submitter);
        const submitterText = submitter ? submitter.textContent.trim().toLowerCase() : '';
        const enctype = form.enctype ? form.enctype.toLowerCase() : '';

        if (action === 'delete' || submitterText.includes('hapus')) {
            return 'delete';
        }

        if (action === 'update' || submitterText.includes('update') || submitterText.includes('edit')) {
            return 'update';
        }

        if (enctype.includes('multipart/form-data') || submitterText.includes('upload')) {
            return 'upload';
        }

        return 'save';
    };

    const confirmationDefaults = {
        delete: {
            title: 'Konfirmasi Hapus Data',
            message: 'Data yang dihapus tidak dapat dikembalikan. Apakah Anda yakin ingin menghapus data ini?',
            accept: 'Ya, Hapus',
        },
        update: {
            title: 'Konfirmasi Update Data',
            message: 'Pastikan perubahan data sudah benar sebelum disimpan.',
            accept: 'Ya, Update',
        },
        upload: {
            title: 'Konfirmasi Upload Berkas',
            message: 'Pastikan data dan berkas yang dipilih sudah benar sebelum dikirim.',
            accept: 'Ya, Kirim',
        },
        save: {
            title: 'Konfirmasi Data',
            message: 'Pastikan data yang Anda masukkan sudah benar sebelum melanjutkan.',
            accept: 'Ya, Lanjutkan',
        },
    };

    const showConfirmOverlay = (form, submitter) => {
        if (! confirmOverlay) {
            return false;
        }

        const type = confirmationType(form, submitter);
        const defaults = confirmationDefaults[type] || confirmationDefaults.save;
        const customTitle = (submitter && submitter.getAttribute('data-confirm-title')) || form.getAttribute('data-confirm-title');
        const customMessage = (submitter && submitter.getAttribute('data-confirm')) || form.getAttribute('data-confirm');
        const customAccept = (submitter && submitter.getAttribute('data-confirm-accept')) || form.getAttribute('data-confirm-accept');

        if (confirmTitle) {
            confirmTitle.textContent = customTitle || defaults.title;
        }

        if (confirmText) {
            confirmText.textContent = customMessage || defaults.message;
        }

        if (confirmAccept) {
            confirmAccept.textContent = customAccept || defaults.accept;
            confirmAccept.classList.toggle('btn-danger', type === 'delete');
            confirmAccept.classList.toggle('btn-primary', type !== 'delete');
        }

        pendingConfirmForm = form;
        pendingConfirmSubmitter = submitter || null;
        confirmOverlay.classList.add('is-visible');
        confirmOverlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('psb-confirm-active');

        return true;
    };

    const hideConfirmOverlay = () => {
        if (! confirmOverlay) {
            return;
        }

        confirmOverlay.classList.remove('is-visible');
        confirmOverlay.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('psb-confirm-active');
    };

    if (confirmCancel) {
        confirmCancel.addEventListener('click', () => {
            pendingConfirmForm = null;
            pendingConfirmSubmitter = null;
            hideConfirmOverlay();
        });
    }

    if (confirmAccept) {
        confirmAccept.addEventListener('click', () => {
            if (! pendingConfirmForm) {
                hideConfirmOverlay();
                return;
            }

            const form = pendingConfirmForm;
            const submitter = pendingConfirmSubmitter;

            pendingConfirmForm = null;
            pendingConfirmSubmitter = null;
            hideConfirmOverlay();
            form.setAttribute('data-confirmed', 'true');

            if (typeof form.requestSubmit === 'function') {
                if (submitter) {
                    form.requestSubmit(submitter);
                } else {
                    form.requestSubmit();
                }
            } else {
                if (shouldUseLoading(form)) {
                    showLoadingOverlay(form);
                }

                form.submit();
            }
        });
    }

    document.querySelectorAll('form').forEach((form) => {
        if (! shouldUseConfirmation(form) && ! shouldUseLoading(form)) {
            return;
        }

        form.addEventListener('submit', (event) => {
            if (event.defaultPrevented) {
                return;
            }

            if (shouldUseConfirmation(form) && form.getAttribute('data-confirmed') !== 'true') {
                if (showConfirmOverlay(form, event.submitter)) {
                    event.preventDefault();
                    return;
                }
            }

            form.removeAttribute('data-confirmed');

            if (! shouldUseLoading(form)) {
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
