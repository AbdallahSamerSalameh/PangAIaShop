/**
 * AJAX Cart functionality - handles adding items to cart without page reload
 */
// This flag helps avoid conflicts with other scripts
var ajaxCartInitialized = false;

document.addEventListener('DOMContentLoaded', function() {
    // Prevent duplicate initialization
    if (ajaxCartInitialized) return;
    ajaxCartInitialized = true;
    
    console.log("Ajax cart module initializing...");
    
    // Select all cart forms (on product pages, shop page, etc.)
    const cartForms = document.querySelectorAll('form[action*="cart/add"]');
    
    if (cartForms.length === 0) {
        console.log("No cart forms found on this page");
        return;
    }
    
    console.log(`Found ${cartForms.length} cart forms`);
    
    // Create sound element for cart success
    const cartSound = document.createElement('audio');
    cartSound.id = 'cart-success-sound';
    cartSound.preload = 'auto';
    // Base64 encoded short success sound
    cartSound.src = 'data:audio/mp3;base64,//uQxAAAEvGLIVT0AAuBtax3P2QCIAAIAGWUC4CCMAg7H/y91BwH5d/8uAGbMLC8O0PDdzP/lwZiY/+XYMzEgwo/+XRAQQ/y4A7+UdHbn/5QBH/y4Abn5d3Ln/5QOCcDg3Lm5cHAIDvJQODv/5cHAHf/LnOAQQODi4A74HLg4g4OT/5OWu9mov/GzFM1SxeyZHuJ358jP+MQBgAzQURm5ztS7pSZnPJpCcw9wyYkC8XMMhQJsJFK5myp2PcNzfvC1L3n8AgBAEAQeAUCgMDhaBoGAABkHQtjnlUyNgZG1y3+Z9Z99K8XabfaL/qpvj+jMu+VZE1yV3qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqpMQU1FMy45Mi4yMDAVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVQ==';
    // Add volume control (not too loud)
    cartSound.volume = 0.5;
    document.body.appendChild(cartSound);
    
    // Try to unlock audio for iOS/Safari
    document.addEventListener('click', function() {
        cartSound.play().then(() => {
            cartSound.pause();
            cartSound.currentTime = 0;
        }).catch(e => {
            // Silent catch - this is just to enable audio on iOS
        });
    }, { once: true });
    
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    
    // Initialize any missing cart count indicators on page load
    initializeCartCount();
      if (!toastContainer) {
        // Create toast container
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
        
        // CSS styles are now in cart-indicators.css
        // Just a dummy style element for backward compatibility
        const style = document.createElement('style');
        style.textContent = `
            .toast-container {
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
            }            .toast {
                background-color: #333;
                color: white;
                padding: 15px 25px;
                border-radius: 5px;
                margin-bottom: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.2);
                animation: toast-in-right 0.5s;
                font-size: 14px;
                font-weight: 500;
            }
            .cart-toast {
                background-color: #2ecc71;
                border-left: 5px solid #27ae60;
            }
            .error-toast {
                background-color: #e74c3c;
                border-left: 5px solid #c0392b;
            }
            @keyframes toast-in-right {
                from { transform: translateX(100%); }
                to { transform: translateX(0); }
            }
            
            .cart-loading {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background-color: rgba(255, 255, 255, 0.7);
                display: flex;
                justify-content: center;
                align-items: center;
                border-radius: inherit;
                z-index: 2;
            }
            
            .cart-spinner {
                color: #f28123;
                font-size: 24px;
            }
            
            .cart-btn, .single-product-form .cart-btn {
                position: relative;
            }
              .cart-count-indicator {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                position: absolute;
                top: -10px;
                right: -10px;
                min-height: 20px;
                min-width: 20px;
                padding: 2px;
                background-color: #f28123;
                color: white;
                border-radius: 50%;
                font-size: 12px;
                font-weight: bold;
                box-shadow: 0 2px 5px rgba(0,0,0,0.2);
                z-index: 100;
            }
            
            /* Make sure the header shopping cart icon has correct positioning */
            .header-icons .shopping-cart {
                position: relative;
            }
            
            @keyframes cart-count-animation {
                0% { transform: scale(0.5); }
                50% { transform: scale(1.2); }
                100% { transform: scale(1); }
            }
            
            .cart-count-animated {
                animation: cart-count-animation 0.5s ease;
            }
        `;
        document.head.appendChild(style);
    }
    
    // Add event listener to each cart form
    cartForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get the button that was clicked
            const submitButton = form.querySelector('button[type="submit"]');
            if (submitButton.disabled || submitButton.classList.contains('disabled')) {
                return; // Don't proceed if the button is disabled
            }
            
            // Add loading spinner to the button
            const loadingSpinner = document.createElement('div');
            loadingSpinner.className = 'cart-loading';
            loadingSpinner.innerHTML = '<i class="fas fa-spinner fa-spin cart-spinner"></i>';
            submitButton.appendChild(loadingSpinner);
            submitButton.disabled = true;
            
            // Create form data from the form
            const formData = new FormData(form);
              // Send AJAX request
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                // Check if response is not ok (includes authentication errors)
                if (!response.ok) {
                    return response.json().then(data => {
                        throw { status: response.status, data: data };
                    });
                }
                return response.json();
            })
            .then(data => {
                // Remove loading spinner
                submitButton.removeChild(loadingSpinner);
                submitButton.disabled = false;

                if (data.success) {
                    // Get the cart sound element
                    const cartSound = document.getElementById('cart-success-sound');
                    
                    // Play success sound with robust error handling
                    if (cartSound) {
                        // Reset audio to beginning to ensure it plays
                        cartSound.currentTime = 0;
                        
                        // Play the sound with fallback
                        const soundPromise = cartSound.play();
                        
                        if (soundPromise !== undefined) {
                            soundPromise.catch(err => {
                                console.log('Sound notification auto-play was blocked, will show visual notification only');
                            });
                        }
                    }
                    
                    // Show success message immediately
                    showToast(`${data.message || 'Product added to cart!'}`, 'cart-toast');                    // Update cart count indicator (in all headers across the site) - OPTIMIZED for instant update
                    // Prioritize server response for most accurate count
                    let newCount = data.cart_count || data.cartCount;
                    
                    // If server didn't provide count, calculate quickly
                    if (newCount === undefined || newCount === null) {
                        // Get current count from any visible indicator
                        const countIndicators = document.querySelectorAll('.cart-count');
                        let currentCount = 0;
                        
                        // Find first visible indicator with a value
                        for (let i = 0; i < countIndicators.length; i++) {
                            if (countIndicators[i].style.display !== 'none') {
                                currentCount = parseInt(countIndicators[i].textContent, 10) || 0;
                                break;
                            }
                        }
                        
                        // Add the quantity from the form
                        const quantityInput = form.querySelector('input[name="quantity"]');
                        const quantity = quantityInput ? parseInt(quantityInput.value, 10) || 1 : 1;
                        newCount = currentCount + quantity;
                    }
                    
                    // Ensure valid number and update instantly
                    updateCartCount(parseInt(newCount, 10) || 0);
                } else {
                    // Show error message
                    showToast(`Error: ${data.message || 'Could not add to cart'}`, 'error-toast');
                }
            })
            .catch(error => {
                console.error('Error adding to cart:', error);
                
                // Remove loading spinner
                if (loadingSpinner.parentNode === submitButton) {
                    submitButton.removeChild(loadingSpinner);
                }
                submitButton.disabled = false;
                  // Check if this is an authentication error
                if (error.status === 401 && error.data && error.data.requires_auth) {
                    // Redirect immediately to login page
                    window.location.href = error.data.redirect || '/login';
                    return;
                }
                
                // Show generic error message for other errors
                const message = error.data && error.data.message 
                    ? `Error: ${error.data.message}` 
                    : 'Error adding to cart. Please try again.';
                showToast(message, 'error-toast');
            });
        });
    });
      /**
     * Show a toast notification
     * @param {string} message - The message to display
     * @param {string} className - CSS class for styling the toast
     * @param {number} duration - How long to show the toast (in ms)
     */
    function showToast(message, className = '', duration = 3000) {
        // Check if toast container exists
        let toastContainer = document.querySelector('.toast-container');
        
        if (!toastContainer) {
            // Create toast container if it doesn't exist
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast message
        const toast = document.createElement('div');
        toast.className = 'toast ' + className;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'polite');
        
        // Add icon based on toast type
        let iconHTML = '';
        if (className.includes('cart-toast')) {
            iconHTML = '<i class="fas fa-check-circle" style="margin-right:8px;"></i>';
        } else if (className.includes('error-toast')) {
            iconHTML = '<i class="fas fa-exclamation-circle" style="margin-right:8px;"></i>';
        }
        
        toast.innerHTML = iconHTML + message;
        toastContainer.appendChild(toast);
        
        // Remove toast after duration
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transition = 'opacity 0.5s';
            
            setTimeout(() => {
                if (toast.parentNode === toastContainer) {
                    toastContainer.removeChild(toast);
                }
            }, 500);
        }, duration);
    }
      /**
     * Update cart count indicator in header - FIXED for reliable real-time updates
     */    function updateCartCount(count) {
        // Convert count to number to avoid issues
        count = parseInt(count, 10) || 0;
        
        // Find ALL cart count indicators across the page
        const cartCountElements = document.querySelectorAll('.cart-count, .cart-count-indicator');
        
        // Find all shopping cart links that might need count indicators
        const cartIcons = document.querySelectorAll('.header-icons a[href*="cart"], .shopping-cart, a.shopping-cart, a[href*="cart"]');
        
        // Process any existing indicators first
        if (cartCountElements.length > 0) {
            cartCountElements.forEach(element => {
                // Update the count value
                element.textContent = count;
                
                // If the count is 0, hide the indicator
                if (count <= 0) {
                    element.style.display = 'none';
                } else {
                    // Force display style to be visible
                    element.style.display = 'inline-flex';
                      // Remove animation class first to restart animation
                    element.classList.remove('cart-count-animated');
                    
                    // Force browser reflow to ensure animation restarts
                    void element.offsetWidth;
                    
                    // Add animation class
                    element.classList.add('cart-count-animated');
                    
                    // Remove animation class after animation completes
                    setTimeout(() => {
                        element.classList.remove('cart-count-animated');
                    }, 500);
                }
            });
        }
        
        // Also check for cart icons that might not have indicators yet
        cartIcons.forEach(cartIcon => {
            let countIndicator = cartIcon.querySelector('.cart-count');
            
            // If this cart icon doesn't have a count indicator, add one
            if (!countIndicator) {
                countIndicator = document.createElement('span');
                countIndicator.className = 'cart-count-indicator cart-count';
                cartIcon.appendChild(countIndicator);
            }
            
            // Update the count
            countIndicator.textContent = count;
            
            // Show/hide based on count
            if (count <= 0) {
                countIndicator.style.display = 'none';
            } else {
                countIndicator.style.display = 'inline-flex';
                  // Remove animation class first to restart animation
                countIndicator.classList.remove('cart-count-animated');
                
                // Force browser reflow to ensure animation restarts
                void countIndicator.offsetWidth;
                
                // Add animation class for visual feedback
                countIndicator.classList.add('cart-count-animated');
                
                // Remove animation class after animation completes
                setTimeout(() => {
                    countIndicator.classList.remove('cart-count-animated');
                }, 500);
            }
        });
    }
      /**
     * Initialize cart count indicators on page load
     */
    function initializeCartCount() {
        // Collect the current cart count from any existing indicators
        let currentCartCount = 0;
        const existingCounters = document.querySelectorAll('.cart-count');
        
        if (existingCounters.length > 0) {
            // Use the first non-empty counter value we find
            for (let i = 0; i < existingCounters.length; i++) {
                const count = parseInt(existingCounters[i].textContent.trim(), 10);
                if (!isNaN(count) && count > 0) {
                    currentCartCount = count;
                    break;
                }
            }
        }
        
        // Find all shopping cart links across the page
        const cartIcons = document.querySelectorAll('.header-icons a[href*="cart"], .shopping-cart');
        
        // Apply consistent indicators to all cart icons
        cartIcons.forEach(cartIcon => {
            // Find or create a cart count indicator
            let countIndicator = cartIcon.querySelector('.cart-count-indicator');
            
            if (!countIndicator) {
                countIndicator = document.createElement('span');
                countIndicator.className = 'cart-count-indicator cart-count';
                cartIcon.appendChild(countIndicator);
            }
            
            // Update the count
            countIndicator.textContent = currentCartCount;
            
            // Show/hide based on count
            if (currentCartCount <= 0) {
                countIndicator.style.display = 'none';
            } else {
                countIndicator.style.display = 'inline-flex';
            }
        });
        
        // Make sure the cart sound is preloaded
        if (!document.getElementById('cart-success-sound')) {
            const cartSound = document.createElement('audio');
            cartSound.id = 'cart-success-sound';
            cartSound.preload = 'auto';
            // Base64 encoded short success sound
            cartSound.src = 'data:audio/mp3;base64,//uQxAAAEvGLIVT0AAuBtax3P2QCIAAIAGWUC4CCMAg7H/y91BwH5d/8uAGbMLC8O0PDdzP/lwZiY/+XYMzEgwo/+XRAQQ/y4A7+UdHbn/5QBH/y4Abn5d3Ln/5QOCcDg3Lm5cHAIDvJQODv/5cHAHf/LnOAQQODi4A74HLg4g4OT/5OWu9mov/GzFM1SxeyZHuJ358jP+MQBgAzQURm5ztS7pSZnPJpCcw9wyYkC8XMMhQJsJFK5myp2PcNzfvC1L3n8AgBAEAQeAUCgMDhaBoGAABkHQtjnlUyNgZG1y3+Z9Z99K8XabfaL/qpvj+jMu+VZE1yV3qqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqqpMQU1FMy45Mi4yMDAVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVQ==';
            document.body.appendChild(cartSound);
        }
    }
});
