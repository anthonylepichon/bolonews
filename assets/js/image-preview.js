// Présentation : module mutualisé d'aperçu des fichiers image.
// Rôle : prévisualiser localement avatar ou illustration avant l'envoi du formulaire.
const previewForms = document.querySelectorAll('[data-image-preview-form]');

previewForms.forEach((form) => {
    const input = form.querySelector('[data-image-input]');
    const preview = form.querySelector('[data-image-preview]');

    if (!(input instanceof HTMLInputElement) || !preview) {
        return;
    }

    const initialMarkup = preview.outerHTML;
    let objectUrl = null;

    input.addEventListener('change', () => {
        const file = input.files?.[0];

        if (objectUrl !== null) {
            // Une URL temporaire doit être libérée avant d'en créer une nouvelle
            // afin de ne pas conserver inutilement le fichier en mémoire.
            URL.revokeObjectURL(objectUrl);
            objectUrl = null;
        }

        if (!file) {
            // Si la sélection est annulée, on restaure l'avatar, l'image actuelle
            // ou l'espace réservé qui existait au chargement.
            const currentPreview = form.querySelector('[data-image-preview]');

            if (currentPreview) {
                currentPreview.outerHTML = initialMarkup;
            }

            return;
        }

        // createObjectURL affiche le fichier local sans l'envoyer au serveur.
        objectUrl = URL.createObjectURL(file);
        const image = document.createElement('img');
        image.dataset.imagePreview = '';
        image.src = objectUrl;
        image.alt = 'Aperçu de l’image sélectionnée';

        if (input.dataset.previewShape === 'avatar') {
            // Les avatars récupèrent la classe ronde existante ; les images
            // d'article utilisent le format rectangulaire défini par leur wrapper.
            image.className = preview.className;
        }

        const currentPreview = form.querySelector('[data-image-preview]');

        if (currentPreview) {
            currentPreview.replaceWith(image);
        }
    });
});
