
document.addEventListener('DOMContentLoaded', () => {

    document.querySelectorAll(
        'form[data-validate="true"]'
    ).forEach(form => {

        form.addEventListener('submit', event => {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        });

    });

    document.querySelectorAll(
        'input[type="file"][data-image-check]'
    ).forEach(input => {

        input.addEventListener('change', () => {
            const file = input.files[0];

            const feedback = input.parentElement
                .querySelector('.file-feedback');

            if (!file) {
                input.setCustomValidity('');
                return;
            }

            const allowed = [
                'image/jpeg',
                'image/png'
            ];

            if (!allowed.includes(file.type)) {
                input.setCustomValidity(
                    'Pilih fail JPG atau PNG sahaja.'
                );

                if (feedback) {
                    feedback.textContent =
                        'Fail mesti JPG atau PNG.';
                }

            } else if (file.size > 2 * 1024 * 1024) {
                input.setCustomValidity(
                    'Saiz maksimum ialah 2 MB.'
                );

                if (feedback) {
                    feedback.textContent =
                        'Saiz fail melebihi 2 MB.';
                }

            } else {
                input.setCustomValidity('');

                if (feedback) {
                    feedback.textContent =
                        'Fail diterima.';
                }
            }
        });

    });

});