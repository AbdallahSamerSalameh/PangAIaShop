/**
 * Dropdown Scroll Fix
 * This script prevents the page from scrolling when a dropdown is opened.
 */
document.addEventListener('DOMContentLoaded', function() {
    // Fix for scroll position jumping when dropdown is opened
    const fixScrollJump = function() {
        // Get all dropdown containers
        const dropdowns = document.querySelectorAll('.custom-dropdown-container');
        
        dropdowns.forEach(function(dropdown) {
            // Get the input element (dropdown trigger)
            const input = dropdown.querySelector('.custom-dropdown-input');
            if (!input) return;
            
            // Capture the window scroll position when the mouse enters the dropdown area
            let scrollPos = 0;
            
            dropdown.addEventListener('mouseenter', function() {
                scrollPos = window.scrollY;
            });
            
            // Re-apply the scroll position if it changed without user explicitly scrolling
            input.addEventListener('click', function() {
                setTimeout(function() {
                    window.scrollTo({
                        top: scrollPos,
                        behavior: 'auto'
                    });
                }, 0);
                
                // Double-check the scroll position again after a short delay
                setTimeout(function() {
                    window.scrollTo({
                        top: scrollPos,
                        behavior: 'auto'
                    });
                }, 100);
            });
        });
    };
    
    // Run the fix immediately
    fixScrollJump();
    
    // Also run it after a small delay to catch any dynamically created dropdowns
    setTimeout(fixScrollJump, 500);
});
