// Add this at the beginning of the script section
// Helper function to include in all pages that have wishlist functionality
function initializeWishlistFunctionality() {
    const wishlistForms = document.querySelectorAll('.wishlist-form');
    if (wishlistForms.length === 0) return;

    const wishlistButtons = document.querySelectorAll('.wishlist-btn');

    // Sync initial state from server
    wishlistButtons.forEach(button => {
        const productId = button.getAttribute('data-product-id');
        fetch(`/wishlist/check?product_id=${productId}`, {headers: {'X-Requested-With': 'XMLHttpRequest'}})
            .then(res => res.json())
            .then(data => {
                if (data.inWishlist) button.classList.add('active');
            })
            .catch(console.error);
    });

    // Handle add/remove
    wishlistForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const button = this.querySelector('.wishlist-btn');
            const productId = button.getAttribute('data-product-id');
            const isActive = button.classList.contains('active');
            const url = isActive ? '/wishlist/remove' : '/wishlist/add';
            const method = isActive ? 'DELETE' : 'POST';
            fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ product_id: productId })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    button.classList.toggle('active');
                    // Update all instances
                    document.querySelectorAll(`.wishlist-btn[data-product-id="${productId}"]`).forEach(btn => {
                        btn.classList.toggle('active', !isActive);
                    });
                    showToast(isActive ? 'Removed from wishlist' : 'Added to wishlist');
                } else {
                    showToast('Error updating wishlist');
                }
            })
            .catch(err => { console.error(err); showToast('Error updating wishlist'); });
        });
    });
}

// Helper function to show toast message
function showToast(message) {
    // Check if toast container exists
    let toastContainer = document.querySelector('.toast-container');
    
    if (!toastContainer) {
        // Create toast container
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
        
        // Add toast container styles
        const style = document.createElement('style');
        style.textContent = `
            .toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            }
            .toast {
                background-color: #333;
                color: white;
                padding: 15px 25px;
                border-radius: 5px;
                margin-bottom: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                animation: toast-in-right 0.5s;
            }
            @keyframes toast-in-right {
                from { transform: translateX(100%); }
                to { transform: translateX(0); }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Create toast message
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    toastContainer.appendChild(toast);
    
    // Remove toast after 3 seconds
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.5s';
        
        setTimeout(() => {
            toastContainer.removeChild(toast);
        }, 500);
    }, 3000);
}

// Initialize wishlist functionality on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeWishlistFunctionality);
} else {
    initializeWishlistFunctionality();
}
