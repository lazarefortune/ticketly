document.addEventListener('DOMContentLoaded', function() {
    // Sélectionne tous les ensembles d'incrémentation et de décrémentation
    const increments = document.querySelectorAll('.increment-button');
    const decrements = document.querySelectorAll('.decrement-button');
    const inputFields = document.querySelectorAll('input[data-input-counter]');

    // Fonction pour mettre à jour les états des boutons
    function updateButtonStates(inputField, incrementButton, decrementButton) {
        const currentValue = parseInt(inputField.value) || 0;
        const minValue = parseInt(inputField.getAttribute('min')) || 0;
        const maxValue = parseInt(inputField.getAttribute('max')) || Infinity;

        // Désactive le bouton de décrémentation si la valeur actuelle est inférieure ou égale à minValue
        decrementButton.disabled = currentValue <= minValue;

        // Désactive le bouton d'incrémentation si la valeur actuelle est supérieure ou égale à maxValue
        incrementButton.disabled = currentValue >= maxValue;

        // Change l'apparence des boutons désactivés
        decrementButton.classList.toggle('opacity-50', decrementButton.disabled);
        incrementButton.classList.toggle('opacity-50', incrementButton.disabled);
    }

    // Vérifie si les éléments sont présents et ajoute les gestionnaires d'événements
    if (increments.length && decrements.length && inputFields.length) {
        increments.forEach((incrementButton, index) => {
            const inputField = inputFields[index];
            const decrementButton = decrements[index];

            // Initialement, mettre à jour l'état des boutons
            updateButtonStates(inputField, incrementButton, decrementButton);

            // Gestionnaire d'événement pour le bouton d'incrémentation
            incrementButton.addEventListener('click', function() {
                let currentValue = parseInt(inputField.value) || 0;
                let maxValue = parseInt(inputField.getAttribute('max')) || Infinity;

                // Incrémente la valeur si elle est inférieure à la valeur maximale
                if (currentValue < maxValue) {
                    inputField.value = currentValue + 1;
                    updateButtonStates(inputField, incrementButton, decrementButton);
                }
            });

            // Gestionnaire d'événement pour le bouton de décrémentation
            decrementButton.addEventListener('click', function() {
                let currentValue = parseInt(inputField.value) || 0;
                let minValue = parseInt(inputField.getAttribute('min')) || 0;

                // Décrémente la valeur si elle est supérieure à la valeur minimale
                if (currentValue > minValue) {
                    inputField.value = currentValue - 1;
                    updateButtonStates(inputField, incrementButton, decrementButton);
                }
            });

            // Écouter les modifications manuelles dans le champ d'entrée
            inputField.addEventListener('input', function() {
                updateButtonStates(inputField, incrementButton, decrementButton);
            });
        });
    }
});
