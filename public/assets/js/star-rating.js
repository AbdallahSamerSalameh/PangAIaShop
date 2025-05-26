// Star rating functionality
document.addEventListener('DOMContentLoaded', function() {
    const starInputs = document.querySelectorAll('.rating-input input');
    const starLabels = document.querySelectorAll('.rating-input label i');
    
    // Initialize filled stars for any pre-selected rating
    updateStars();
    
    // Add click event to each star input
    starInputs.forEach(input => {
        input.addEventListener('change', function() {
            updateStars();
        });
    });
    
    function updateStars() {
        // Reset all stars to empty
        starLabels.forEach(star => {
            star.className = 'far fa-star';
            star.style.color = '#ddd'; // Reset to grey color
        });
        
        // Find the checked star
        const checkedStar = document.querySelector('.rating-input input:checked');
        if (checkedStar) {
            // Get all stars that should be filled (the selected star and all stars after it)
            const starsToFill = document.querySelectorAll(`.rating-input label[for="${checkedStar.id}"] i, .rating-input label[for="${checkedStar.id}"] ~ label i`);
            
            // Fill the selected stars
            starsToFill.forEach(star => {
                star.className = 'fas fa-star';
                star.style.color = '#f28123'; // Orange color
                star.style.fontWeight = '900';
            });
        }
    }
    
    // Also handle hover effects with JavaScript for better compatibility
    const ratingContainer = document.querySelector('.rating-input');
    if (ratingContainer) {
        // Handle mouseenter for labels
        ratingContainer.addEventListener('mouseenter', function(e) {
            if (e.target.tagName === 'LABEL') {
                const hoverStar = e.target;
                const starIcon = hoverStar.querySelector('i');
                const prevStars = [];
                
                // Get the previous siblings
                let prevStar = hoverStar;
                while (prevStar = prevStar.previousElementSibling) {
                    if (prevStar.tagName === 'LABEL') {
                        prevStars.push(prevStar.querySelector('i'));
                    }
                }
                
                // Highlight current and previous stars
                starIcon.style.color = '#f28123';
                prevStars.forEach(star => {
                    star.style.color = '#f28123';
                });
            }
        });
        
        // Handle mouseleave for the entire container
        ratingContainer.addEventListener('mouseleave', function() {
            // Reset to the selected state
            updateStars();
        });
    }
    
    // Initial update
    updateStars();
});
