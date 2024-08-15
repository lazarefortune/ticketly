import axios from 'axios';

class AddressAutocomplete extends HTMLElement {
    constructor() {
        super();
        this.inputElement = null;
        this.suggestionsList = document.createElement('ul');
        this.suggestionsList.id = 'suggestions-list';
        this.suggestionsList.className = 'absolute border border-gray-300 bg-white dark:bg-gray-700 dark:border-gray-600 z-50 hidden';
        document.body.appendChild(this.suggestionsList);

        this.activeSuggestionIndex = -1;
        this.suggestions = [];
    }

    connectedCallback() {
        this.inputElement = document.querySelector(this.getAttribute('input-selector'));
        if (!this.inputElement) return;

        this.inputElement.addEventListener('input', this.handleInput.bind(this));
        this.inputElement.addEventListener('keydown', this.handleKeyDown.bind(this));
        document.addEventListener('click', this.handleClickOutside.bind(this));
    }

    disconnectedCallback() {
        if (!this.inputElement) return;

        this.inputElement.removeEventListener('input', this.handleInput.bind(this));
        this.inputElement.removeEventListener('keydown', this.handleKeyDown.bind(this));
        document.removeEventListener('click', this.handleClickOutside.bind(this));
    }

    async handleInput(event) {
        const query = event.target.value;
        if (query.length < 3) {
            this.suggestionsList.classList.add('hidden');
            return;
        }

        try {
            const response = await axios.get(`https://api-adresse.data.gouv.fr/search/?q=${query}`);
            this.suggestions = response.data.features.map(feature => feature.properties.label);
            this.renderSuggestions();
        } catch (error) {
            console.error('Error fetching address suggestions:', error);
        }
    }

    handleKeyDown(event) {
        if (this.suggestionsList.classList.contains('hidden')) return;

        const items = this.suggestionsList.querySelectorAll('li');
        if (event.key === 'ArrowDown') {
            event.preventDefault();
            this.activeSuggestionIndex = (this.activeSuggestionIndex + 1) % items.length;
            this.setActiveSuggestion(this.activeSuggestionIndex);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            this.activeSuggestionIndex = (this.activeSuggestionIndex - 1 + items.length) % items.length;
            this.setActiveSuggestion(this.activeSuggestionIndex);
        } else if (event.key === 'Enter' && this.activeSuggestionIndex > -1) {
            event.preventDefault();
            items[this.activeSuggestionIndex].click();
        }
    }

    handleClickOutside(event) {
        if (!this.suggestionsList.contains(event.target) && event.target !== this.inputElement) {
            this.suggestionsList.classList.add('hidden');
        }
    }

    setActiveSuggestion(index) {
        const items = this.suggestionsList.querySelectorAll('li');
        items.forEach((item, idx) => {
            if (idx === index) {
                item.classList.add('bg-gray-200', 'dark:bg-gray-600');
            } else {
                item.classList.remove('bg-gray-200', 'dark:bg-gray-600');
            }
        });
    }

    renderSuggestions() {
        this.suggestionsList.innerHTML = '';
        this.suggestions.forEach((suggestion, index) => {
            const item = document.createElement('li');
            item.textContent = suggestion;
            item.className = 'px-4 py-2 cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-600';
            item.dataset.index = index;
            item.addEventListener('click', () => {
                this.inputElement.value = suggestion;
                this.suggestionsList.classList.add('hidden');
            });
            item.addEventListener('mouseenter', () => {
                this.setActiveSuggestion(index);
            });
            this.suggestionsList.appendChild(item);
        });

        const rect = this.inputElement.getBoundingClientRect();
        this.suggestionsList.style.top = `${rect.bottom + window.scrollY}px`;
        this.suggestionsList.style.left = `${rect.left + window.scrollX}px`;
        this.suggestionsList.style.width = `${this.inputElement.offsetWidth}px`;
        this.suggestionsList.classList.remove('hidden');
    }
}

customElements.define('address-autocomplete', AddressAutocomplete);
