document.addEventListener('DOMContentLoaded', () => {
    const togglePasswordButtons = document.querySelectorAll('.password-toggle-button');

    if (!togglePasswordButtons.length) {
        return
    }

    togglePasswordButtons.forEach(button => {
        button.addEventListener('click', (event) => {
            const passwordField = event.currentTarget.closest('.relative').querySelector('input[type="password"], input[type="text"]');
            const iconEye = event.currentTarget.querySelector('.icon-eye');
            const iconEyeOff = event.currentTarget.querySelector('.icon-eye-off');

            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                iconEye.classList.add('hidden');
                iconEyeOff.classList.remove('hidden');
            } else {
                passwordField.type = 'password';
                iconEye.classList.remove('hidden');
                iconEyeOff.classList.add('hidden');
            }
        });
    });
});