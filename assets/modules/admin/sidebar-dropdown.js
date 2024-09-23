function initDropdown() {
    const sidebarDropdowns = document.querySelectorAll('.sidebar--dropdown-button')

    if (!sidebarDropdowns) {
        return;
    }

    sidebarDropdowns.forEach(dropdownButton => {
        if (dropdownButton.classList.contains('active')) {
            dropdownButton.querySelector('.sidebar--dropdown-button-icon').classList.add('-rotate-180');
            dropdownButton.nextElementSibling.classList.remove('hidden');
        }
        dropdownButton.addEventListener('click', () => {
            const dropdownIcon = dropdownButton.querySelector('.sidebar--dropdown-button-icon');
            dropdownIcon.classList.toggle('-rotate-180');
            const menu = dropdownButton.nextElementSibling;
            menu.classList.toggle('hidden');
        });
    });
}

document.addEventListener('DOMContentLoaded', initDropdown);