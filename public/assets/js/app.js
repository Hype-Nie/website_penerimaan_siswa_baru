document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.custom-file-input').forEach((input) => {
        input.addEventListener('change', () => {
            const label = input.nextElementSibling;

            if (label && input.files.length > 0) {
                label.textContent = input.files[0].name;
            }
        });
    });
});
