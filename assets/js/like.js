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
        likeButton.disabled = true;

        if (errorMessage) {
            errorMessage.hidden = true;
            errorMessage.textContent = '';
        }

        const requestBody = new URLSearchParams({
            _token: likeButton.dataset.likeToken,
        });

        try {
            const response = await fetch(
                likeButton.dataset.likeUrl,
                {
                    method: 'POST',
                    headers: {
                        'Content-Type':
                            'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: requestBody,
                }
            );

            if (!response.ok) {
                throw new Error(
                    'La requête J’aime a échoué.'
                );
            }

            const data = await response.json();

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

            likeCount.textContent = data.likesCount;
        } catch (error) {
            console.error(error);

            if (errorMessage) {
                errorMessage.textContent =
                    'Le J’aime n’a pas pu être enregistré.';

                errorMessage.hidden = false;
            }
        } finally {
            likeButton.disabled = false;
        }
    });
});
