/**
 * Custom Searchable Country Dropdown
 * This creates a fully searchable dropdown with proper display for country selection
 */
document.addEventListener('DOMContentLoaded', function() {
    // Find all country select elements
    const countrySelects = document.querySelectorAll('.country-select');
    
    // Initialize each with a custom dropdown
    countrySelects.forEach(function(select) {
        initializeCustomDropdown(select);
    });
    
    /**
     * Initialize a custom searchable dropdown
     * @param {HTMLSelectElement} selectElement - The original select element
     */
    function initializeCustomDropdown(selectElement) {
        // Get current selected value from data attribute or value
        const currentValue = selectElement.getAttribute('data-current') || selectElement.value;
        
        // Find the matching country name
        let currentText = 'Select a country';
        if (currentValue && typeof countries !== 'undefined' && Array.isArray(countries)) {
            const country = countries.find(c => c.code === currentValue);
            if (country) {
                currentText = country.name;
            }
        }
        
        // Create container for custom dropdown
        const container = document.createElement('div');
        container.className = 'custom-dropdown-container';
        
        // Create input field (dropdown trigger)
        const input = document.createElement('div');
        input.className = 'custom-dropdown-input';
        input.textContent = currentText;
        input.setAttribute('data-value', currentValue || '');
        
        // Create dropdown items container
        const itemsContainer = document.createElement('div');
        itemsContainer.className = 'custom-dropdown-items';
        
        // Add search box
        const searchBox = document.createElement('div');
        searchBox.className = 'custom-dropdown-search';
        
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search countries...';
        searchInput.autocomplete = 'off';
        searchBox.appendChild(searchInput);
        itemsContainer.appendChild(searchBox);
        
        // Create items container for scrolling
        const scrollContainer = document.createElement('div');
        scrollContainer.className = 'custom-dropdown-scroll';
        itemsContainer.appendChild(scrollContainer);
        
        // Get countries data and populate dropdown
        if (typeof countries !== 'undefined' && Array.isArray(countries)) {
            // Sort countries alphabetically
            const sortedCountries = [...countries].sort((a, b) => 
                a.name.localeCompare(b.name)
            );
            
            // Add items to dropdown
            sortedCountries.forEach(function(country) {
                const item = document.createElement('div');
                item.className = 'custom-dropdown-item';
                if (country.code === currentValue) {
                    item.classList.add('selected');
                }
                
                item.textContent = country.name;
                item.setAttribute('data-value', country.code);
                item.setAttribute('data-search', country.name.toLowerCase());
                
                // Handle item click
                item.addEventListener('click', function() {
                    // Update hidden select element
                    selectElement.value = country.code;
                    
                    // Trigger change event on select
                    const event = new Event('change', { bubbles: true });
                    selectElement.dispatchEvent(event);
                    
                    // Update display input
                    input.textContent = country.name;
                    input.setAttribute('data-value', country.code);
                    
                    // Close dropdown
                    itemsContainer.classList.remove('active');
                    
                    // Update selected class
                    const items = scrollContainer.querySelectorAll('.custom-dropdown-item');
                    items.forEach(i => i.classList.remove('selected'));
                    item.classList.add('selected');
                });
                
                scrollContainer.appendChild(item);
            });
        }        // Add event listeners for dropdown
        input.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault(); // Always prevent default to avoid any scrolling
            const isActive = itemsContainer.classList.contains('active');
            
            // Close all other dropdowns first
            document.querySelectorAll('.custom-dropdown-items').forEach(function(dropdown) {
                dropdown.classList.remove('active');
            });
            
            // Toggle current dropdown
            if (!isActive) {
                // Store current scroll position
                const scrollPos = window.scrollY;
                
                // Make dropdown visible
                itemsContainer.classList.add('active');
                searchInput.value = '';
                filterItems('');
                container.classList.add('dropdown-open');
                
                // Restore scroll position immediately
                setTimeout(() => {
                    window.scrollTo({
                        top: scrollPos,
                        behavior: 'auto'
                    });
                }, 0);
                
                // Find the selected item and scroll it into view *within* the dropdown only
                const selectedItem = scrollContainer.querySelector('.custom-dropdown-item.selected');
                if (selectedItem) {
                    // Use a safer way to scroll the item into view that won't affect page scroll
                    if (selectedItem.offsetTop < scrollContainer.scrollTop || 
                        selectedItem.offsetTop + selectedItem.offsetHeight > scrollContainer.scrollTop + scrollContainer.offsetHeight) {
                        scrollContainer.scrollTop = selectedItem.offsetTop - (scrollContainer.offsetHeight / 2);
                    }
                }
                
                // Focus without scrolling
                setTimeout(() => {
                    // Another scroll position check/fix
                    window.scrollTo({
                        top: scrollPos,
                        behavior: 'auto'
                    });
                    
                    // Now it's safe to focus the search input
                    searchInput.focus({preventScroll: true});
                }, 50);
            } else {
                itemsContainer.classList.remove('active');
                container.classList.remove('dropdown-open');
            }
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) {
                itemsContainer.classList.remove('active');
                container.classList.remove('dropdown-open');
            }
        });
        
        // Implement search functionality
        searchInput.addEventListener('input', function() {
            filterItems(this.value.trim().toLowerCase());
        });        // Keyboard navigation for the dropdown
        searchInput.addEventListener('keydown', function(e) {
            // Store current scroll position
            const scrollPos = window.scrollY;
            
            if (e.key === 'Escape') {
                e.preventDefault(); // Prevent any possible scrolling
                itemsContainer.classList.remove('active');
                container.classList.remove('dropdown-open');
                return;
            }
            
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault(); // Prevent default arrow key scrolling
                const items = Array.from(scrollContainer.querySelectorAll('.custom-dropdown-item:not([style*="display: none"])'));
                if (items.length === 0) return;
                
                const selectedIndex = items.findIndex(item => item.classList.contains('selected'));
                let nextIndex;
                
                if (e.key === 'ArrowDown') {
                    nextIndex = selectedIndex < 0 ? 0 : (selectedIndex + 1) % items.length;
                } else {
                    nextIndex = selectedIndex < 0 ? items.length - 1 : (selectedIndex - 1 + items.length) % items.length;
                }
                
                const nextItem = items[nextIndex];
                items.forEach(i => i.classList.remove('selected'));
                nextItem.classList.add('selected');
                
                // Manually scroll the container instead of using scrollIntoView
                if (nextItem.offsetTop < scrollContainer.scrollTop || 
                    nextItem.offsetTop + nextItem.offsetHeight > scrollContainer.scrollTop + scrollContainer.offsetHeight) {
                    scrollContainer.scrollTop = nextItem.offsetTop - (scrollContainer.offsetHeight / 2);
                }
                
                // Restore page scroll position
                setTimeout(() => {
                    window.scrollTo({
                        top: scrollPos,
                        behavior: 'auto'
                    });
                }, 0);
            }
            
            if (e.key === 'Enter' && itemsContainer.classList.contains('active')) {
                e.preventDefault();
                const selectedItem = scrollContainer.querySelector('.custom-dropdown-item.selected');
                if (selectedItem) {
                    selectedItem.click();
                }
            }
        });
          // Prevent dropdown close when clicking inside search
        searchInput.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault(); // Prevent any default behavior that might cause scrolling
        });
        
        /**
         * Filter dropdown items based on search text
         * @param {string} searchText - The search text
         */
        function filterItems(searchText) {
            const items = scrollContainer.querySelectorAll('.custom-dropdown-item');
            let hasVisibleItems = false;
            
            items.forEach(function(item) {
                const text = item.textContent.toLowerCase();
                const code = item.getAttribute('data-value').toLowerCase();
                
                if (text.includes(searchText) || code.includes(searchText)) {
                    item.style.display = 'block';
                    hasVisibleItems = true;
                } else {
                    item.style.display = 'none';
                }
            });
            
            // If there's no match, show a "no results" message
            const noResultsMsg = itemsContainer.querySelector('.no-results-msg');
            
            if (!hasVisibleItems && searchText) {
                if (!noResultsMsg) {
                    const msg = document.createElement('div');
                    msg.className = 'no-results-msg';
                    msg.textContent = 'No countries match your search';
                    itemsContainer.appendChild(msg);
                }
            } else if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }
        
        // Add elements to DOM
        container.appendChild(input);
        container.appendChild(itemsContainer);
        
        // Hide original select and insert custom dropdown after it
        selectElement.style.display = 'none';
        selectElement.parentNode.insertBefore(container, selectElement.nextSibling);
        
        // Update the hidden select with the correct value
        if (currentValue) {
            selectElement.value = currentValue;
        }
    }
});
