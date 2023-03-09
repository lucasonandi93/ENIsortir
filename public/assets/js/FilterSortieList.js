// Définir la méthode pour générer le formulaire de filtre
function generateFilterForm(sortieData) {
    // Créer un élément de formulaire
    const form = document.createElement('form');

    // Ajouter une classe au formulaire pour le style
    form.classList.add('filter-form');

    // Ajouter un champ de recherche pour filtrer par nom de sortie
    const nameInput = document.createElement('input');
    nameInput.type = 'text';
    nameInput.placeholder = 'Filtrer par nom de sortie';
    nameInput.addEventListener('input', () => {
        // Appeler la méthode de filtrage avec le nom de sortie en entrée
        filterSortiesByName(nameInput.value, sortieData);
    });
    form.appendChild(nameInput);

    // Ajouter un champ de sélection pour filtrer par catégorie de sortie
    const categorySelect = document.createElement('select');
    categorySelect.innerHTML = '<option value="">Toutes les catégories</option>';
    const categories = getUniqueCategories(sortieData);
    categories.forEach(category => {
        const option = document.createElement('option');
        option.value = category;
        option.textContent = category;
        categorySelect.appendChild(option);
    });
    categorySelect.addEventListener('change', () => {
        // Appeler la méthode de filtrage avec la catégorie en entrée
        filterSortiesByCategory(categorySelect.value, sortieData);
    });
    form.appendChild(categorySelect);

    // Ajouter un champ de sélection pour filtrer par date de sortie
    const dateSelect = document.createElement('select');
    dateSelect.innerHTML = '<option value="">Toutes les dates</option>';
    const dates = getUniqueDates(sortieData);
    dates.forEach(date => {
        const option = document.createElement('option');
        option.value = date;
        option.textContent = date;
        dateSelect.appendChild(option);
    });
    dateSelect.addEventListener('change', () => {
        // Appeler la méthode de filtrage avec la date en entrée
        filterSortiesByDate(dateSelect.value, sortieData);
    });
    form.appendChild(dateSelect);

    // Retourner le formulaire
    return form;
}

// Définir la méthode de filtrage par nom de sortie
function filterSortiesByName(name, sortieData) {
    // Filtrer les sorties par nom de sortie
    const filteredSorties = sortieData.filter(sortie => sortie.name.toLowerCase().includes(name.toLowerCase()));

    // Appeler la méthode d'affichage des sorties avec les sorties filtrées
    displaySorties(filteredSorties);
}

// Définir la méthode de filtrage par catégorie de sortie
function filterSortiesByCategory(category, sortieData) {
    // Filtrer les sorties par catégorie
    const filteredSorties = category ? sortieData.filter(sortie => sortie.category === category) : sortieData;

    // Appeler la méthode d'affichage des sorties avec les sorties filtrées
    displaySorties(filteredSorties);
}

// Définir la méthode de filtrage par date de sortie
function filterSortiesByDate(date, sortieData) {
    // Filtrer les sorties par date de sortie
    const filteredSorties = date ? sortieData.filter(sortie => sortie.date === date) : sortieData;

    // Appeler la méthode d'affichage des sorties avec les sorties filtrées
    displaySorties(filteredSorties);
}

// Définir la méthode pour afficher les sorties
function displaySorties(sortie
