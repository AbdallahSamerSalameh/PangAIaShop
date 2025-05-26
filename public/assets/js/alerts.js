/**
 * PangAIaShop Alert Manager
 * Handles auto-dismissing alerts and other alert-related functionality
 */
document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss success alerts after 3 seconds
    const successAlerts = document.querySelectorAll('.alert-success');
    
    if (successAlerts.length > 0) {
        setTimeout(function() {
            successAlerts.forEach(function(alert) {
                // Fade out effect
                let opacity = 1;
                const fadeInterval = setInterval(function() {
                    if (opacity <= 0.1) {
                        clearInterval(fadeInterval);
                        alert.style.display = 'none';
                    }
                    opacity -= 0.1;
                    alert.style.opacity = opacity;
                }, 100);
            });
        }, 3000); // 3 seconds before starting to fade out
    }
});
