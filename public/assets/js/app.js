document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('form[data-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm');

            if (message && ! window.confirm(message)) {
                event.preventDefault();
            }
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
