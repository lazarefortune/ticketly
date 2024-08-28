document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner le champ de mot de passe
    const passwordInput = document.querySelector('.password-verifier');

    // Sélectionner la div contenant les critères de validation
    const criteriaDiv = document.querySelector('.password-criteria');

    // Définir les critères de validation
    const criteria = {
        length: { element: document.getElementById('password_verifier_length'), check: password => password.length >= 6 },
        uppercase: { element: document.getElementById('password_verifier_lowercase'), check: password => /[A-Z]/.test(password) },
        number: { element: document.getElementById('password_verifier_number'), check: password => /[0-9]/.test(password) },
        special: { element: document.getElementById('password_verifier_special'), check: password => /[!@#$%^&*(),.?":{}|<>]/.test(password) }
    };

    // Fonction pour mettre à jour l'affichage des critères
    const updateCriteria = () => {
        const password = passwordInput.value;

        Object.keys(criteria).forEach(key => {
            const { element, check } = criteria[key];
            const isValid = check(password);

            // Mettre à jour les classes en fonction de la validité
            if (password === '') {
                // Afficher l'icône 'x' et mettre la couleur par défaut à gris
                element.querySelector('.password_verifier_icon_check').style.display = 'none';
                element.querySelector('.password_verifier_icon_x').style.display = 'inline-block';
                element.classList.remove('text-red-800', 'text-green-700');
                element.classList.add('text-gray-500');
            } else {
                // Mettre à jour les classes et icônes basées sur la validation
                element.querySelector('.password_verifier_icon_check').style.display = isValid ? 'inline-block' : 'none';
                element.querySelector('.password_verifier_icon_x').style.display = isValid ? 'none' : 'inline-block';
                element.classList.toggle('text-red-800', !isValid);
                element.classList.toggle('text-green-700', isValid);
                element.classList.remove('text-gray-500'); // Enlever le gris après vérification
            }
        });
    };

    // Afficher la div des critères lors du focus sur le champ de mot de passe
    passwordInput.addEventListener('focus', () => {
        criteriaDiv.classList.remove('opacity-0', 'max-h-0');
        criteriaDiv.classList.add('opacity-100', 'max-h-[1000px]');
        updateCriteria();  // Assurer que l'affichage est correct dès le focus
    });

    // Cacher la div des critères lorsque le champ de mot de passe perd le focus
    passwordInput.addEventListener('blur', () => {
        criteriaDiv.classList.remove('opacity-100', 'max-h-[1000px]');
        criteriaDiv.classList.add('opacity-0', 'max-h-0');
    });

    // Ajouter un écouteur d'événements pour la saisie
    passwordInput.addEventListener('input', updateCriteria);
});
