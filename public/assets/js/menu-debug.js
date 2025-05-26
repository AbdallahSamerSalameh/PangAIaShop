// Debugging script to help identify menu issues
(function($) {
    $(document).ready(function() {
        // Log when mean menu is initialized
        const originalMeanMenu = $.fn.meanmenu;
        
        $.fn.meanmenu = function(options) {
            console.log('meanmenu initialized with options:', options);
            const result = originalMeanMenu.apply(this, arguments);
            
            // Check if the menu is correctly initialized
            setTimeout(function() {
                if ($('.mean-bar').length > 0) {
                    console.log('meanmenu bar created');
                    
                    // Check for orange background
                    const meanBarBg = window.getComputedStyle($('.mean-bar')[0]).backgroundColor;
                    console.log('mean-bar background:', meanBarBg);
                    
                    // Check alignment with logo
                    const logoPosition = $('.site-logo').offset();
                    const menuPosition = $('.mean-bar').offset();
                    console.log('Logo position:', logoPosition);
                    console.log('Menu position:', menuPosition);
                    
                    // Check if login item was added
                    setTimeout(function() {
                        const loginItems = $('.mean-nav .login-menu-item').length;
                        console.log('Login items found:', loginItems);
                    }, 500);
                }
            }, 1000);
            
            return result;
        };
    });
})(jQuery);
