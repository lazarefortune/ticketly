class AddressAutocomplete extends HTMLElement {
    constructor() {
        super();

        // Obtenir le sélecteur de l'input depuis l'attribut
        this.inputSelector = this.getAttribute('input-selector');

        // Créer la liste des suggestions
        this.suggestionsList = document.createElement('ul');
        this.suggestionsList.className = 'suggestions-list';
        this.suggestionsList.hidden = true;

        this.activeSuggestionIndex = -1;
        this.suggestions = [];

        // Lier les méthodes
        this.handleInput = this.handleInput.bind(this);
        this.handleKeyDown = this.handleKeyDown.bind(this);
        this.handleClickOutside = this.handleClickOutside.bind(this);
    }

    connectedCallback() {
        // Sélectionner l'élément d'entrée à partir du sélecteur
        this.inputElement = document.querySelector(this.inputSelector);
        if (!this.inputElement) {
            console.error(`Aucun élément trouvé pour le sélecteur "${this.inputSelector}"`);
            return;
        }

        // Ajouter les écouteurs d'événements
        this.inputElement.addEventListener('input', this.handleInput);
        this.inputElement.addEventListener('keydown', this.handleKeyDown);
        document.addEventListener('click', this.handleClickOutside);

        // Ajouter la classe de conteneur à l'élément parent de l'input pour le positionnement
        if (!this.inputElement.parentElement.classList.contains('autocomplete-container')) {
            this.inputElement.parentElement.classList.add('autocomplete-container');
        }

        // Déplacer la liste des suggestions dans le parent de l'input pour un positionnement correct
        this.inputElement.parentElement.appendChild(this.suggestionsList);
    }

    disconnectedCallback() {
        if (!this.inputElement) return;

        this.inputElement.removeEventListener('input', this.handleInput);
        this.inputElement.removeEventListener('keydown', this.handleKeyDown);
        document.removeEventListener('click', this.handleClickOutside);

        // Supprimer la liste des suggestions du DOM si nécessaire
        if (this.suggestionsList.parentElement) {
            this.suggestionsList.parentElement.removeChild(this.suggestionsList);
        }
    }

    async handleInput(event) {
        const query = this.inputElement.value.trim();
        if (query.length < 3) {
            this.suggestions = [];
            this.renderSuggestions();
            return;
        }

        try {
            const limit = 10; // Vous pouvez ajuster cette valeur (maximum 20)
            const response = await fetch(`https://api-adresse.data.gouv.fr/search/?q=${encodeURIComponent(query)}&limit=${limit}`);
            const data = await response.json();
            this.suggestions = data.features.map(feature => feature.properties.label);
            this.activeSuggestionIndex = -1;
            this.renderSuggestions();
        } catch (error) {
            console.error('Erreur lors de la récupération des suggestions d\'adresse :', error);
        }
    }

    handleKeyDown(event) {
        if (this.suggestions.length === 0) return;

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            this.activeSuggestionIndex = (this.activeSuggestionIndex + 1) % this.suggestions.length;
            this.renderSuggestions();
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            this.activeSuggestionIndex = (this.activeSuggestionIndex - 1 + this.suggestions.length) % this.suggestions.length;
            this.renderSuggestions();
        } else if (event.key === 'Enter') {
            if (this.activeSuggestionIndex > -1) {
                event.preventDefault();
                this.selectSuggestion(this.activeSuggestionIndex);
            }
        }
    }

    handleClickOutside(event) {
        if (!this.inputElement.contains(event.target) && !this.suggestionsList.contains(event.target)) {
            this.suggestions = [];
            this.renderSuggestions();
        }
    }

    renderSuggestions() {
        // Vider la liste des suggestions
        this.suggestionsList.innerHTML = '';

        if (this.suggestions.length === 0) {
            this.suggestionsList.classList.remove('show');
            return;
        }

        // Créer les éléments de suggestion
        this.suggestions.forEach((suggestion, index) => {
            const item = document.createElement('li');
            item.textContent = suggestion;
            item.className = 'suggestion-item';
            item.setAttribute('role', 'option');
            item.dataset.index = index;

            if (index === this.activeSuggestionIndex) {
                item.classList.add('active');
            }

            item.addEventListener('click', () => {
                this.selectSuggestion(index);
            });

            this.suggestionsList.appendChild(item);
        });

        this.suggestionsList.classList.add('show');

        // S'assurer que l'élément actif est visible
        this.ensureActiveSuggestionIsVisible();
    }

    ensureActiveSuggestionIsVisible() {
        if (this.activeSuggestionIndex > -1) {
            const activeItem = this.suggestionsList.querySelector(`[data-index="${this.activeSuggestionIndex}"]`);
            if (activeItem) {
                activeItem.scrollIntoView({ block: 'nearest' });
            }
        }
    }

    selectSuggestion(index) {
        if (index >= 0 && index < this.suggestions.length) {
            this.inputElement.value = this.suggestions[index];
            this.suggestions = [];
            this.renderSuggestions();
            this.inputElement.focus();
        }
    }
}

customElements.define('address-autocomplete', AddressAutocomplete);
