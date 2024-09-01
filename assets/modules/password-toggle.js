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

document.addEventListener('DOMContentLoaded', function() {
    const incrementButton = document.getElementById('increment-button');
    const decrementButton = document.getElementById('decrement-button');
    const inputField = document.getElementById('quantity-input');

    incrementButton.addEventListener('click', function() {
        let currentValue = parseInt(inputField.value) || 0;
        let maxValue = parseInt(inputField.getAttribute('max'));
        if (currentValue < maxValue) {
            inputField.value = currentValue + 1;
        }
    });

    decrementButton.addEventListener('click', function() {
        let currentValue = parseInt(inputField.value) || 0;
        let minValue = parseInt(inputField.getAttribute('min'));
        if (currentValue > minValue) {
            inputField.value = currentValue - 1;
        }
    });
});