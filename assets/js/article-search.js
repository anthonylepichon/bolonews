document.addEventListener('DOMContentLoaded', () => {
    const searchForm = document.querySelector(
        '[data-article-search-form]'
    );

    const resultsContainer = document.querySelector(
        '[data-articles-results]'
    );

    if (!searchForm || !resultsContainer) {
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

    const updateCategoryInput = (categoryId) => {
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

        resetLink.hidden = search === '';

        const resetUrl = new URL(
            searchForm.action,
            window.location.origin
        );

        if (categoryId !== '') {
            resetUrl.searchParams.set(
                'categorie',
                categoryId
            );
        }

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
    };

    const loadResults = async (
        url,
        updateHistory = true
    ) => {
        resultsContainer.setAttribute(
            'aria-busy',
            'true'
        );

        try {
            const response = await fetch(
                url.toString(),
                {
                    headers: {
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
                window.history.pushState(
                    {},
                    '',
                    url.toString()
                );
            }
        } catch (error) {
            console.error(error);

            // Retour au fonctionnement classique
            // si AJAX rencontre une erreur.
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
        loadResults(
            new URL(window.location.href),
            false
        );
    });
});
