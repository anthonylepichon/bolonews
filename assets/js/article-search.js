// Présentation : module de recherche et de filtrage des articles.
// Rôle : demander la liste à Symfony par AJAX, synchroniser l'interface et conserver
// le formulaire GET comme solution de secours lorsque JavaScript est indisponible.
document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.querySelector(
        '[data-article-search-form]'
    );

    const resultsContainer = document.querySelector(
        '[data-articles-results]'
    );

    if (!searchForm || !resultsContainer) {
        // Sortie immédiate sur les pages qui ne possèdent pas cette interface.
        return;
    }

    const searchInput = searchForm.querySelector(
        'input[name="recherche"]'
    );

    const resetLink = document.querySelector(
        '[data-search-reset]'
    );

    const filterLinks = Array.from(
        document.querySelectorAll(
            '[data-article-filter-link]'
        )
    );

    const activeFilters = document.querySelector(
        '[data-active-filters]'
    );

    const activeFilterList = document.querySelector(
        '[data-active-filter-list]'
    );

    const resultsTitle = document.querySelector(
        '[data-articles-title]'
    );

    const updateCategoryInput = (categoryId) => {
        // Le filtre catégorie est conservé dans un champ caché afin qu'une
        // recherche textuelle ultérieure envoie les deux critères ensemble.
        let categoryInput = searchForm.querySelector(
            'input[name="categorie"]'
        );

        if (categoryId === '') {
            categoryInput?.remove();

            return;
        }

        if (!categoryInput) {
            categoryInput = document.createElement('input');
            categoryInput.type = 'hidden';
            categoryInput.name = 'categorie';

            searchForm.append(categoryInput);
        }

        categoryInput.value = categoryId;
    };

    const updateFilterLinks = (
        search,
        selectedCategoryId
    ) => {
        // Les href restent toujours valides pour une ouverture dans un nouvel
        // onglet ou un usage sans JavaScript ; aria-current indique le filtre actif.
        filterLinks.forEach((link) => {
            const categoryId = link.dataset.categoryId;
            const linkUrl = new URL(
                searchForm.action,
                window.location.origin
            );

            if (search !== '') {
                linkUrl.searchParams.set(
                    'recherche',
                    search
                );
            }

            if (categoryId !== '') {
                linkUrl.searchParams.set(
                    'categorie',
                    categoryId
                );
            }

            link.href = linkUrl.toString();

            if (categoryId === selectedCategoryId) {
                link.setAttribute(
                    'aria-current',
                    'true'
                );
            } else {
                link.removeAttribute('aria-current');
            }
        });
    };

    const updateActiveFilters = (
        search,
        categoryId
    ) => {
        // Le résumé visible est reconstruit avec textContent : le texte saisi
        // n'est jamais interprété comme du HTML.
        if (!activeFilters || !activeFilterList) {
            return;
        }

        activeFilterList.replaceChildren();

        if (search !== '') {
            const searchItem =
                document.createElement('li');

            searchItem.textContent =
                `Recherche : « ${search} »`;

            activeFilterList.append(searchItem);
        }

        if (categoryId !== '') {
            const selectedLink = filterLinks.find(
                (link) =>
                    link.dataset.categoryId
                    === categoryId
            );

            if (selectedLink) {
                const categoryItem =
                    document.createElement('li');

                categoryItem.textContent =
                    `Catégorie : ${
                        selectedLink.dataset.categoryLabel
                    }`;

                activeFilterList.append(
                    categoryItem
                );
            }
        }

        activeFilters.hidden =
            activeFilterList.children.length === 0;
    };

    const updateResetLink = (
        search,
        categoryId
    ) => {
        if (!resetLink) {
            return;
        }

        resetLink.hidden =
            search === '' && categoryId === '';

        const resetUrl = new URL(
            searchForm.action,
            window.location.origin
        );

        resetLink.href = resetUrl.toString();
    };

    const updateInterface = (url) => {
        const search =
            url.searchParams.get('recherche') ?? '';

        const categoryId =
            url.searchParams.get('categorie') ?? '';

        searchInput.value = search;

        updateCategoryInput(categoryId);

        updateFilterLinks(
            search,
            categoryId
        );

        updateActiveFilters(
            search,
            categoryId
        );

        updateResetLink(
            search,
            categoryId
        );

        if (resultsTitle) {
            resultsTitle.textContent =
                search !== '' || categoryId !== ''
                    ? 'Résultats de recherche'
                    : 'Tous les articles';
        }
    };

    const loadResults = async (
        url,
        updateHistory = true
    ) => {
        // aria-busy informe les technologies d'assistance qu'un résultat est
        // momentanément en cours de chargement.
        resultsContainer.setAttribute(
            'aria-busy',
            'true'
        );

        try {
            const response = await fetch(
                url.toString(),
                {
                    headers: {
                        // Le contrôleur reconnaît cet en-tête et renvoie seulement
                        // article/_list.html.twig au lieu de la page complète.
                        'X-Requested-With':
                            'XMLHttpRequest',
                    },
                }
            );

            if (!response.ok) {
                throw new Error(
                    'La recherche a échoué.'
                );
            }

            resultsContainer.innerHTML =
                await response.text();

            updateInterface(url);

            if (updateHistory) {
                // pushState synchronise la barre d'adresse sans rechargement ;
                // le bouton Retour est géré plus bas avec l'événement popstate.
                window.history.pushState(
                    {},
                    '',
                    url.toString()
                );
            }
        } catch (error) {
            console.error(error);

            // Amélioration progressive : en cas d'échec AJAX, une navigation
            // classique laisse Symfony rendre la même recherche côté serveur.
            window.location.assign(
                url.toString()
            );
        } finally {
            resultsContainer.removeAttribute(
                'aria-busy'
            );
        }
    };

    searchForm.addEventListener(
        'submit',
        (event) => {
            // On bloque la soumission HTML uniquement parce que loadResults()
            // prend le relais. Sans ce script, le formulaire fonctionne normalement.
            event.preventDefault();

            const url = new URL(
                searchForm.action,
                window.location.origin
            );

            const search = searchInput.value.trim();

            const categoryInput =
                searchForm.querySelector(
                    'input[name="categorie"]'
                );

            if (search !== '') {
                url.searchParams.set(
                    'recherche',
                    search
                );
            }

            if (categoryInput?.value) {
                url.searchParams.set(
                    'categorie',
                    categoryInput.value
                );
            }

            loadResults(url);
        }
    );

    filterLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();

            const url = new URL(
                searchForm.action,
                window.location.origin
            );

            const search = searchInput.value.trim();
            const categoryId =
                link.dataset.categoryId;

            if (search !== '') {
                url.searchParams.set(
                    'recherche',
                    search
                );
            }

            if (categoryId !== '') {
                url.searchParams.set(
                    'categorie',
                    categoryId
                );
            }

            loadResults(url);
        });
    });

    resetLink?.addEventListener(
        'click',
        (event) => {
            event.preventDefault();

            const url = new URL(
                resetLink.href
            );

            loadResults(url);
        }
    );

    window.addEventListener('popstate', () => {
        // Recharge l'état correspondant à l'URL lorsque l'utilisateur navigue
        // dans l'historique, sans ajouter une nouvelle entrée avec pushState.
        loadResults(
            new URL(window.location.href),
            false
        );
    });
});
