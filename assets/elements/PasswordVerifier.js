export default class PasswordVerifier extends HTMLElement {
    constructor() {
        super();
        this.passwordInput = null;
        this.criteria = {};
        this.criteriaContainer = null;
    }

    connectedCallback() {
        const passwordInputId = this.getAttribute('password-input-id');
        this.passwordInput = document.getElementById(passwordInputId);

        // Créer la div pour les critères directement dans le Custom Element
        this.criteriaContainer = document.createElement('div');
        this.criteriaContainer.classList.add('password-criteria', 'col-span-full', 'grid', 'grid-cols-1', 'md:grid-cols-2', 'gap-3', 'mb-1', 'opacity-0', 'max-h-0', 'transition-all', 'duration-300', 'ease-in-out', 'overflow-hidden');

        this.innerHTML = ''; // Clean up the Custom Element content if any

        // Générer les critères de validation
        this.createCriteriaElements();

        this.passwordInput.addEventListener('focus', this.showCriteria.bind(this));
        this.passwordInput.addEventListener('blur', this.hideCriteria.bind(this));
        this.passwordInput.addEventListener('input', this.updateCriteria.bind(this));
    }

    createCriteriaElements() {
        // Définir les critères de validation
        this.criteria = {
            length: {
                text: '6 caractères minimum',
                check: password => password.length >= 6
            },
            uppercase: {
                text: '1 lettre majuscule',
                check: password => /[A-Z]/.test(password)
            },
            number: {
                text: '1 chiffre',
                check: password => /[0-9]/.test(password)
            },
            special: {
                text: '1 caractère spécial',
                check: password => /[!@#$%^&*(),.?":{}|<>]/.test(password)
            }
        };

        // Créer les éléments DOM pour chaque critère
        Object.keys(this.criteria).forEach(key => {
            const { text } = this.criteria[key];
            const criteriaElement = document.createElement('div');
            criteriaElement.classList.add('flex', 'gap-2', 'items-center', 'text-gray-500');
            criteriaElement.innerHTML = `
                <span class="password_verifier_icon_check hidden">${this.getIcon('check')}</span>
                <span class="password_verifier_icon_x">${this.getIcon('x')}</span>
                <span class="text-sm">${text}</span>
            `;
            this.criteria[key].element = criteriaElement;
            this.criteriaContainer.appendChild(criteriaElement);
        });

        // Ajouter le container au DOM
        this.appendChild(this.criteriaContainer);
    }

    getIcon(icon) {
        if (icon === 'check') {
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>';
        } else if (icon === 'x') {
            return '<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>';
        }
        return '';
    }

    showCriteria() {
        this.criteriaContainer.classList.remove('opacity-0', 'max-h-0');
        this.criteriaContainer.classList.add('opacity-100', 'max-h-[1000px]');
        this.updateCriteria(); // Initial update on focus
    }

    hideCriteria() {
        this.criteriaContainer.classList.remove('opacity-100', 'max-h-[1000px]');
        this.criteriaContainer.classList.add('opacity-0', 'max-h-0');
    }

    updateCriteria() {
        const password = this.passwordInput.value;

        Object.keys(this.criteria).forEach(key => {
            const { element, check } = this.criteria[key];
            const isValid = check(password);

            // Mettre à jour les classes et icônes
            element.querySelector('.password_verifier_icon_check').style.display = isValid ? 'inline-block' : 'none';
            element.querySelector('.password_verifier_icon_x').style.display = isValid ? 'none' : 'inline-block';
            element.classList.toggle('text-red-800', !isValid);
            element.classList.toggle('dark:text-red-400', !isValid);
            element.classList.toggle('text-green-700', isValid);
            element.classList.toggle('dark:text-green-400', isValid);
            element.classList.remove('text-gray-500');
        });
    }
}