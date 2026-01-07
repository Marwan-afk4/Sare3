document.addEventListener('DOMContentLoaded', function() {
    const yearFromInput = document.querySelector('input[name="year_from"]');
    const yearToInput = document.querySelector('input[name="year_to"]');

    if (yearFromInput && yearToInput) {
        function validateYearRange() {
            const yearFrom = parseInt(yearFromInput.value);
            const yearTo = parseInt(yearToInput.value);

            // Clear previous custom validity
            yearToInput.setCustomValidity('');

            if (yearFrom && yearTo && yearTo < yearFrom) {
                yearToInput.setCustomValidity('Year To must be greater than or equal to Year From');
            }
        }

        yearFromInput.addEventListener('input', validateYearRange);
        yearToInput.addEventListener('input', validateYearRange);

        // Validate on form submission
        const form = yearFromInput.closest('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                validateYearRange();
                if (!yearToInput.checkValidity()) {
                    e.preventDefault();
                    yearToInput.reportValidity();
                }
            });
        }
    }
});
