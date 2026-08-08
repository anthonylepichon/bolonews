// Présentation : validation immédiate des confirmations de mot de passe.
// Rôle : exploiter les champs _first/_second de RepeatedType avant la validation Symfony.
document.querySelectorAll('input[type="password"][id$="_second"]').forEach((confirmation) => {
    const firstId = confirmation.id.replace(/_second$/, '_first');
    const password = document.getElementById(firstId);

    if (!(password instanceof HTMLInputElement)) {
        return;
    }

    const validateConfirmation = () => {
        const valuesMatch = confirmation.value === password.value;

        // setCustomValidity branche le message sur la validation HTML native et
        // empêche l'envoi tant que les deux valeurs restent différentes.
        confirmation.setCustomValidity(
            valuesMatch ? '' : 'Les mots de passe ne correspondent pas.'
        );
    };

    password.addEventListener('input', validateConfirmation);
    confirmation.addEventListener('input', validateConfirmation);
});
