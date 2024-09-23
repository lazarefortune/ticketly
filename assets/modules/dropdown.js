function initDropdowns() {
    const dropdownButtons = document.querySelectorAll('.dropdown-button');

    dropdownButtons.forEach(button => {
        const dropdown = button.nextElementSibling; // Suppose que le menu est le sibling suivant

        button.addEventListener('click', (event) => {
            event.stopPropagation(); // Empêche la propagation du clic au document

            // Ferme tous les autres menus déroulants
            document.querySelectorAll('.dropdown-menu.is-visible').forEach(menu => {
                if (menu !== dropdown) {
                    menu.classList.remove('is-visible');
                }
            });

            // Bascule la visibilité du menu déroulant associé
            dropdown.classList.toggle('is-visible');

            // Met à jour l'attribut aria-expanded pour l'accessibilité
            const isExpanded = button.getAttribute('aria-expanded') === 'true';
            button.setAttribute('aria-expanded', !isExpanded);
        });
    });

    // Ferme les menus déroulants si on clique en dehors
    document.addEventListener('click', () => {
        document.querySelectorAll('.dropdown-menu.is-visible').forEach(menu => {
            menu.classList.remove('is-visible');
            const button = menu.previousElementSibling;
            if (button && button.classList.contains('dropdown-button')) {
                button.setAttribute('aria-expanded', 'false');
            }
        });
    });

    // Gestion des événements clavier pour l'accessibilité
    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeAllDropdowns();
        }

        const activeMenu = document.querySelector('.dropdown-menu.is-visible');
        if (activeMenu) {
            const focusableElements = activeMenu.querySelectorAll('a, button, input, [tabindex]:not([tabindex="-1"])');
            let index = Array.prototype.indexOf.call(focusableElements, document.activeElement);

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                index = (index + 1) % focusableElements.length;
                focusableElements[index].focus();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                index = (index - 1 + focusableElements.length) % focusableElements.length;
                focusableElements[index].focus();
            }
        }
    });
}

function closeAllDropdowns() {
    document.querySelectorAll('.dropdown-menu.is-visible').forEach(menu => {
        menu.classList.remove('is-visible');
        const button = menu.previousElementSibling;
        if (button && button.classList.contains('dropdown-button')) {
            button.setAttribute('aria-expanded', 'false');
        }
    });
}

document.addEventListener('DOMContentLoaded', initDropdowns);

