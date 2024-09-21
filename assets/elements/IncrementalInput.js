export default class IncrementalInput extends HTMLElement {
    constructor() {
        super();

        const min = this.getAttribute('min') || 0;
        const max = this.getAttribute('max') || 99999;
        const value = this.getAttribute('value') || min;
        const step = this.getAttribute('step') || 1;
        const name = this.getAttribute('name') || '';

        this.innerHTML = `
            <div class="flex items-center space-x-1">
                <!-- Bouton de décrémentation -->
                <button type="button" aria-label="Réduire la quantité" class="
                    bg-primary-100 dark:bg-primary-700
                    hover:bg-primary-200 dark:hover:bg-primary-600
                    border border-primary-300 dark:border-primary-600
                    rounded-l p-3
                    focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-600 focus:outline-none
                    transition-all ease-in-out duration-150
                    decrement-button
                    disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-primary-100 dark:disabled:bg-primary-800
                    disabled:border-primary-300 dark:disabled:border-primary-600
                    disabled:hover:bg-primary-100 dark:disabled:hover:bg-primary-800
                    disabled:text-primary-400 dark:disabled:text-primary-500
                ">
                    <svg class="w-3 h-3 text-primary-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 2">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 1h16"/>
                    </svg>
                </button>

                <!-- Champ de saisie -->
                <input type="number" class="
                    rounded-none
                    bg-white dark:bg-primary-800
                    border-x-0 border-primary-300 dark:border-primary-600
                    text-center text-gray-900 dark:text-white
                    text-sm
                    focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-600
                    block w-16 py-2.5 md:py-2
                    transition-all duration-150 ease-in-out
                    input-field
                    hide-number-arrows
                " name="${name}" value="${value}" min="${min}" max="${max}" step="${step}" />

                <!-- Bouton d'incrémentation -->
                <button type="button" aria-label="Augmenter la quantité" class="
                    bg-primary-100 dark:bg-primary-700
                    hover:bg-primary-200 dark:hover:bg-primary-600
                    border border-primary-300 dark:border-primary-600
                    rounded-r p-3
                    focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-600 focus:outline-none
                    transition-all ease-in-out duration-150
                    increment-button
                    disabled:opacity-50 disabled:cursor-not-allowed disabled:bg-primary-100 dark:disabled:bg-primary-800
                    disabled:border-primary-300 dark:disabled:border-primary-600
                    disabled:hover:bg-primary-100 dark:disabled:hover:bg-primary-800
                    disabled:text-primary-400 dark:disabled:text-primary-500
                ">
                    <svg class="w-3 h-3 text-primary-900 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 18 18">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 1v16M1 9h16"/>
                    </svg>
                </button>
            </div>
        `;
    }

    connectedCallback() {
        this._decrementButton = this.querySelector('.decrement-button');
        this._incrementButton = this.querySelector('.increment-button');
        this._inputField = this.querySelector('.input-field');

        this._minValue = parseFloat(this.getAttribute('min')) || 0;
        this._maxValue = parseFloat(this.getAttribute('max')) || 99999;
        this._step = parseFloat(this.getAttribute('step')) || 1;

        this.updateButtonStates();

        this._incrementButton.addEventListener('click', () => this.increment());
        this._decrementButton.addEventListener('click', () => this.decrement());
        this._inputField.addEventListener('input', () => this.onInputChange());
    }

    increment() {
        let currentValue = parseFloat(this._inputField.value) || this._minValue;
        if (currentValue + this._step <= this._maxValue) {
            currentValue += this._step;
            this._inputField.value = currentValue;
            this.updateButtonStates();

            // Déclenchement manuel de l'événement 'input'
            this._inputField.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    decrement() {
        let currentValue = parseFloat(this._inputField.value) || this._minValue;
        if (currentValue - this._step >= this._minValue) {
            currentValue -= this._step;
            this._inputField.value = currentValue;
            this.updateButtonStates();

            // Déclenchement manuel de l'événement 'input'
            this._inputField.dispatchEvent(new Event('input', { bubbles: true }));
        }
    }

    onInputChange() {
        let currentValue = parseFloat(this._inputField.value) || this._minValue;
        if (currentValue < this._minValue) {
            currentValue = this._minValue;
        } else if (currentValue > this._maxValue) {
            currentValue = this._maxValue;
        }
        this._inputField.value = currentValue;
        this.updateButtonStates();
    }

    updateButtonStates() {
        const currentValue = parseFloat(this._inputField.value) || this._minValue;
        this._decrementButton.disabled = currentValue <= this._minValue;
        this._incrementButton.disabled = currentValue >= this._maxValue;
    }
}