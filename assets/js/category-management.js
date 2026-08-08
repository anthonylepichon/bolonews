// Présentation : module d'édition en ligne des catégories administratives.
// Rôle : alterner affichage/formulaire et annuler localement ; Symfony assure l'enregistrement.
document.querySelectorAll('[data-category-row]').forEach((row) => {
    const display = row.querySelector('[data-category-display]');
    const edit = row.querySelector('[data-category-edit]');
    const editButton = row.querySelector('[data-category-edit-button]');
    const cancelButton = row.querySelector('[data-category-cancel-button]');

    if (!display || !edit || !editButton || !cancelButton) {
        return;
    }

    editButton.addEventListener('click', () => {
        // hidden alterne entre la lecture du libellé et son formulaire d'édition.
        display.hidden = true;
        edit.hidden = false;
        edit.querySelector('input')?.focus();
    });

    cancelButton.addEventListener('click', () => {
        // reset annule aussi les changements non envoyés avant de revenir à la vue.
        const form = edit.querySelector('form');
        form?.reset();
        edit.hidden = true;
        display.hidden = false;
    });
});
