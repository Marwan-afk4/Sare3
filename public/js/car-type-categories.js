/**
 * Enhanced Car Type Categories Multi-Select
 * Using Choices.js for better UX
 */

function initCarTypeCategoriesSelect(elementId, options = {}) {
    const selectElement = document.getElementById(elementId);

    if (!selectElement) {
        console.warn(`Element with ID "${elementId}" not found`);
        return null;
    }

    // Default options
    const defaultOptions = {
        removeItemButton: true,
        searchEnabled: true,
        searchPlaceholderValue: options.searchPlaceholder || 'Search categories...',
        noResultsText: options.noResultsText || 'No categories found',
        noChoicesText: options.noChoicesText || 'No categories available',
        itemSelectText: options.itemSelectText || 'Click to select',
        placeholder: true,
        placeholderValue: options.placeholderValue || 'Select categories',
        shouldSort: false,
        maxItemCount: options.maxItemCount || -1,
        searchResultLimit: 50,
        renderChoiceLimit: -1,
        classNames: {
            containerOuter: 'choices',
            containerInner: 'choices__inner',
            input: 'choices__input',
            inputCloned: 'choices__input--cloned',
            list: 'choices__list',
            listItems: 'choices__list--multiple',
            listSingle: 'choices__list--single',
            listDropdown: 'choices__list--dropdown',
            item: 'choices__item',
            itemSelectable: 'choices__item--selectable',
            itemDisabled: 'choices__item--disabled',
            itemChoice: 'choices__item--choice',
            placeholder: 'choices__placeholder',
            group: 'choices__group',
            groupHeading: 'choices__heading',
            button: 'choices__button',
            activeState: 'is-active',
            focusState: 'is-focused',
            openState: 'is-open',
            disabledState: 'is-disabled',
            highlightedState: 'is-highlighted',
            selectedState: 'is-selected',
            flippedState: 'is-flipped',
            loadingState: 'is-loading',
        }
    };

    // Initialize Choices.js
    const choices = new Choices(selectElement, defaultOptions);

    // Create counter element
    const counterElement = document.createElement('div');
    counterElement.className = 'category-counter';
    counterElement.innerHTML = `
        <span class="counter-badge">0</span>
        <span class="counter-text">${options.counterText || 'categories selected'}</span>
    `;

    // Insert counter after the select element's parent
    const parentDiv = selectElement.closest('.form-floating, .mb-3');
    if (parentDiv && parentDiv.nextElementSibling) {
        parentDiv.parentNode.insertBefore(counterElement, parentDiv.nextElementSibling);
    } else if (parentDiv) {
        parentDiv.parentNode.appendChild(counterElement);
    }

    // Update counter function
    function updateCounter() {
        const selectedCount = choices.getValue(true).length;
        const badge = counterElement.querySelector('.counter-badge');
        const text = counterElement.querySelector('.counter-text');

        badge.textContent = selectedCount;

        if (selectedCount === 0) {
            text.textContent = options.counterTextZero || 'No categories selected';
            counterElement.classList.remove('has-selections');
        } else if (selectedCount === 1) {
            text.textContent = options.counterTextOne || '1 category selected';
            counterElement.classList.add('has-selections');
        } else {
            text.textContent = (options.counterTextMultiple || '{count} categories selected').replace('{count}', selectedCount);
            counterElement.classList.add('has-selections');
        }
    }

    // Listen for changes
    selectElement.addEventListener('addItem', updateCounter);
    selectElement.addEventListener('removeItem', updateCounter);

    // Initial update
    updateCounter();

    // Add keyboard shortcuts
    selectElement.addEventListener('keydown', function (e) {
        // Ctrl/Cmd + A to select all
        if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
            e.preventDefault();
            const allOptions = Array.from(selectElement.options).map(opt => opt.value);
            choices.setChoiceByValue(allOptions);
        }

        // Escape to clear all
        if (e.key === 'Escape' && e.shiftKey) {
            e.preventDefault();
            choices.removeActiveItems();
        }
    });

    return choices;
}

// Auto-initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    // Check if we're on a car types page
    const carCategorySelect = document.getElementById('car_category_ids_select');

    if (carCategorySelect) {
        // Get page language for translations
        const lang = document.documentElement.lang || 'en';

        // Arabic translations
        const translations = {
            ar: {
                searchPlaceholder: 'ابحث عن فئة...',
                noResultsText: 'لا توجد نتائج',
                noChoicesText: 'لا توجد فئات متاحة',
                itemSelectText: 'انقر للاختيار',
                placeholderValue: 'اختر الفئات',
                counterText: 'فئات محددة',
                counterTextZero: 'لم يتم اختيار فئات',
                counterTextOne: 'فئة واحدة محددة',
                counterTextMultiple: '{count} فئات محددة'
            },
            en: {
                searchPlaceholder: 'Search categories...',
                noResultsText: 'No categories found',
                noChoicesText: 'No categories available',
                itemSelectText: 'Click to select',
                placeholderValue: 'Select categories',
                counterText: 'categories selected',
                counterTextZero: 'No categories selected',
                counterTextOne: '1 category selected',
                counterTextMultiple: '{count} categories selected'
            }
        };

        const t = translations[lang] || translations.en;

        initCarTypeCategoriesSelect('car_category_ids_select', t);
    }
});

