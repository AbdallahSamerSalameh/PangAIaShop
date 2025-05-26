// Add login link to mobile menu
(function($) {
    $(document).ready(function(){
        // Function to add login link to mobile menu
        function addLoginLinkToMobileMenu() {
            if ($('.mean-nav ul li.login-menu-item').length === 0) {
                // Add login menu item if it doesn't exist
                $('.mean-nav ul').append('<li class="login-menu-item"><a href="' + (typeof loginRoute !== 'undefined' ? loginRoute : '/login') + '"><i class="fas fa-user"></i> Login</a></li>');
            }
        }
        
        // Function to ensure menu is properly positioned
        function adjustMenuPosition() {
            // Get the header height
            var headerHeight = $('.main-menu-wrap').outerHeight();
            
            // Set menu position based on header height
            $('.mean-nav').css({
                'margin-top': headerHeight + 'px'
            });
        }
        
        // We need to observe for changes in the DOM to detect when the menu is created
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.addedNodes.length > 0) {
                    // Check if the mobile menu has been added
                    if ($('.mean-nav').length > 0) {
                        if ($('.login-menu-item').length === 0) {
                            addLoginLinkToMobileMenu();
                        }
                        adjustMenuPosition();
                    }
                }
            });
        });
        
        // Start observing the body for changes
        observer.observe(document.body, { childList: true, subtree: true });
        
        // Also check on document ready in case the menu is already there
        if ($('.mean-nav').length > 0) {
            addLoginLinkToMobileMenu();
            adjustMenuPosition();
        }
        
        // Re-check when meanmenu is initialized or window is resized
        $(document).on('click', '.meanmenu-reveal', function() {
            setTimeout(function() {
                if ($('.mean-nav').length > 0) {
                    if ($('.login-menu-item').length === 0) {
                        addLoginLinkToMobileMenu();
                    }
                    adjustMenuPosition();
                }
            }, 100);
        });
        
        // Adjust on window resize
        $(window).on('resize', function() {
            if ($('.mean-nav').length > 0) {
                adjustMenuPosition();
            }
        });
    });
})(jQuery);
