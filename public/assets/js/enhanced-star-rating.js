// Enhanced Star Rating functionality - additional functionality on top of star-rating.js
document.addEventListener('DOMContentLoaded', function() {
    // Wait a brief moment to ensure the DOM is fully processed
    setTimeout(function() {
        // Get all star rating inputs and the star icons
        const starInputs = document.querySelectorAll('.rating-input input');
        const starLabels = document.querySelectorAll('.rating-input label i');
        
        // Force update all stars with the fas class to have orange color
        function forceUpdateStarColors() {
            document.querySelectorAll('.rating-input label i.fas').forEach(star => {
                star.style.setProperty('color', '#f28123', 'important');
                star.style.fontWeight = '900';
            });
        }
        
        // Add click event directly to the star labels for better mobile support
        document.querySelectorAll('.rating-input label').forEach(label => {
            label.addEventListener('click', function(e) {
                // Get the corresponding input
                const inputId = this.getAttribute('for');
                const input = document.getElementById(inputId);
                
                if (input) {
                    // Check the input
                    input.checked = true;
                    
                    // Reset all stars
                    starLabels.forEach(star => {
                        star.className = 'far fa-star rating-star';
                        star.style.color = '#ddd';
                    });
                    
                    // Get the value of the selected rating (5, 4, 3, 2, 1)
                    const rating = parseInt(input.value);
                    
                    // Fill in the correct stars
                    const starsToFill = document.querySelectorAll(`.rating-input label[for="${inputId}"] i, .rating-input label[for="${inputId}"] ~ label i`);
                    starsToFill.forEach(star => {
                        star.className = 'fas fa-star rating-star';
                        star.style.setProperty('color', '#f28123', 'important');
                        star.style.fontWeight = '900';
                    });
                    
                    // Stop event propagation to prevent conflicts
                    e.stopPropagation();
                }
            });
        });
        
        // Run force update on page load and after a delay
        forceUpdateStarColors();
        setTimeout(forceUpdateStarColors, 200);
        
        // Also run when the reviews tab is activated (if it exists)
        const reviewsTab = document.getElementById('reviews-tab');
        if (reviewsTab) {
            reviewsTab.addEventListener('click', function() {
                setTimeout(forceUpdateStarColors, 100);
            });
        }
    }, 100);
});
