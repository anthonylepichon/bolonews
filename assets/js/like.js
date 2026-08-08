// Présentation : module AJAX du bouton « J'aime » d'un article.
// Rôle : envoyer l'action à Symfony, mettre à jour l'état et le compteur, puis
// restaurer l'interface si la requête échoue.
// Turbo remplace le contenu de la page sans recharger tout le document.
// DOMContentLoaded ne se produit donc qu'au premier affichage : ce WeakSet
// permet d'initialiser chaque nouveau bouton une seule fois, y compris après
// une navigation Turbo ou la restauration d'une page depuis son cache.
const initializedLikeButtons = new WeakSet();

const initializeLikeButton = () => {
    const likeButton = document.querySelector(
        '[data-like-button]'
    );

    if (
        !likeButton
        || initializedLikeButtons.has(likeButton)
    ) {
        // Le module est globalement importé, mais ne s'active que sur un article.
        return;
    }

    initializedLikeButtons.add(likeButton);

    const likeLabel = likeButton.querySelector(
        '[data-like-label]'
    );

    const likeCount = likeButton.querySelector(
        '[data-like-count]'
    );

    const errorMessage = document.querySelector(
        '[data-like-error]'
    );

    likeButton.addEventListener('click', async () => {
        // Ces valeurs permettent de restaurer fidèlement l'interface si la
        // requête réseau, la sécurité ou la réponse JSON rencontre une erreur.
        const previousState = {
            pressed: likeButton.getAttribute(
                'aria-pressed'
            ),
            label: likeLabel.textContent,
            count: likeCount.textContent,
        };

        // Désactiver temporairement empêche deux clics rapides de créer des
        // requêtes concurrentes et un compteur incohérent.
        likeButton.disabled = true;

        if (errorMessage) {
            errorMessage.hidden = true;
            errorMessage.textContent = '';
        }

        const requestBody = JSON.stringify({
            // Le jeton généré par Twig est revérifié dans LikeController.
            _token: likeButton.dataset.likeToken,
        });

        let userMessage =
            'Le J’aime n’a pas pu être enregistré.';

        try {
            const response = await fetch(
                likeButton.dataset.likeUrl,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type':
                            'application/json',
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: requestBody,
                }
            );

            let data;

            try {
                data = await response.json();
            } catch {
                userMessage =
                    'La réponse du serveur est invalide.';

                throw new Error();
            }

            if (!response.ok) {
                userMessage = data.error ?? userMessage;

                throw new Error();
            }

            if (
                typeof data.liked !== 'boolean'
                || !Number.isInteger(data.likeCount)
            ) {
                // La forme de la réponse est contrôlée avant de modifier le DOM.
                userMessage =
                    'La réponse du serveur est invalide.';

                throw new Error();
            }

            likeButton.setAttribute(
                'aria-pressed',
                data.liked ? 'true' : 'false'
            );

            likeLabel.textContent = data.liked
                ? 'Je n’aime plus'
                : 'J’aime';

            likeCount.textContent = data.likeCount;
        } catch {
            // Une erreur restaure exactement l'état antérieur au clic, puis rend
            // un message accessible dans la zone data-like-error.
            likeButton.setAttribute(
                'aria-pressed',
                previousState.pressed
            );

            likeLabel.textContent = previousState.label;
            likeCount.textContent = previousState.count;

            if (errorMessage) {
                errorMessage.textContent = userMessage;

                errorMessage.hidden = false;
            }
        } finally {
            likeButton.disabled = false;
        }
    });
};

// L'appel direct couvre le premier chargement de la page. L'évènement
// turbo:load relance ensuite la recherche du bouton après chaque navigation.
initializeLikeButton();
document.addEventListener('turbo:load', initializeLikeButton);
