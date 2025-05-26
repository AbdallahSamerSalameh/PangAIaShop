/**
 * Enhanced country dropdown functionality with proper display
 */
document.addEventListener('DOMContentLoaded', function() {
    // Fix country dropdown styling and behavior
    const countrySelect = document.getElementById('country');
    
    if (countrySelect) {
        // Ensure the country-select class is present for the custom dropdown to work
        if (!countrySelect.classList.contains('country-select')) {
            countrySelect.classList.add('country-select');
        }
        
        // Fix the height issue
        countrySelect.style.height = 'auto';
        
        // Sort countries by name for better usability
        if (typeof countries !== 'undefined' && Array.isArray(countries)) {
            // Clear existing options first
            while (countrySelect.firstChild) {
                countrySelect.removeChild(countrySelect.firstChild);
            }
            
            // Add the empty/placeholder option
            const emptyOption = document.createElement('option');
            emptyOption.value = '';
            emptyOption.textContent = 'Select a country';
            countrySelect.appendChild(emptyOption);
            
            // Sort countries alphabetically
            const sortedCountries = [...countries].sort((a, b) => 
                a.name.localeCompare(b.name)
            );
            
            // Get user's current country 
            const currentCountryCode = countrySelect.getAttribute('data-current') || '';
            
            // Add sorted countries
            sortedCountries.forEach(function(country) {
                const option = document.createElement('option');
                option.value = country.code;
                // Show just the name to fix display issues
                option.textContent = country.name;
                
                // Select user's current country if it matches
                if (country.code === currentCountryCode) {
                    option.selected = true;
                }
                
                countrySelect.appendChild(option);
            });
        }
        
        // Add event handlers to fix dropdown display issues
        countrySelect.addEventListener('mousedown', function(e) {
            if(this.options.length > 8) {
                // If dropdown has many options, show several at once
                this.size = 10; 
                
                // Prevent default browser handling to avoid duplicate arrows
                e.preventDefault();
                
                // Show the dropdown manually
                const event = document.createEvent('MouseEvents');
                event.initMouseEvent('mousedown', true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
                this.dispatchEvent(event);
            }
        });
        
        // Reset to normal when selection is made or focus is lost
        countrySelect.addEventListener('change', function() {
            this.size = 1;
            this.blur();
        });
        
        countrySelect.addEventListener('blur', function() {
            this.size = 1; // Back to default
        });
        
        // Fix for when dropdown is clicked outside
        document.addEventListener('click', function(e) {
            if (e.target !== countrySelect) {
                countrySelect.size = 1;
            }
        });
    }
});
