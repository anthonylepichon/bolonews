document.addEventListener('DOMContentLoaded', () => {
    const likeButton = document.querySelector(
        '[data-like-button]'
    );

    if (!likeButton) {
        return;
    }

    const likeIcon = likeButton.querySelector(
        '[data-like-icon]'
    );

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
        // Ces valeurs permettent de restaurer fidèlement
        // l'interface si la requête échoue.
        const previousState = {
            pressed: likeButton.getAttribute(
                'aria-pressed'
            ),
            icon: likeIcon.textContent,
            label: likeLabel.textContent,
            count: likeCount.textContent,
        };

        likeButton.disabled = true;

        if (errorMessage) {
            errorMessage.hidden = true;
            errorMessage.textContent = '';
        }

        const requestBody = JSON.stringify({
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
                userMessage =
                    'La réponse du serveur est invalide.';

                throw new Error();
            }

            likeButton.setAttribute(
                'aria-pressed',
                data.liked ? 'true' : 'false'
            );

            likeIcon.textContent = data.liked
                ? '♥'
                : '♡';

            likeLabel.textContent = data.liked
                ? 'Je n’aime plus'
                : 'J’aime';

            likeCount.textContent = data.likeCount;
        } catch {
            likeButton.setAttribute(
                'aria-pressed',
                previousState.pressed
            );

            likeIcon.textContent = previousState.icon;
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
});
