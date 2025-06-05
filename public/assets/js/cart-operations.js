/**
 * Cart Operations - AJAX functionality for cart page operations
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('Cart operations module initializing...');

    // Initialize all cart functionality
    initializeCartRemoval();
    initializeCartUpdate();
    initializeCartCouponOperations();

    /**
     * Initialize AJAX cart item removal
     */
    function initializeCartRemoval() {
        const removeButtons = document.querySelectorAll('.btn-remove, .cart-remove-btn');
        
        removeButtons.forEach(button => {
            const form = button.closest('form');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Disable the button to prevent double clicks
                button.disabled = true;
                button.style.opacity = '0.6';
                
                // Add loading state
                const originalContent = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                // Get form data
                const formData = new FormData(form);
                
                // Send AJAX request
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Remove the cart item row with animation
                        const cartRow = button.closest('tr, .cart-item');
                        if (cartRow) {
                            cartRow.style.transition = 'all 0.3s ease';
                            cartRow.style.opacity = '0';
                            cartRow.style.transform = 'translateX(-100%)';
                            
                            setTimeout(() => {
                                cartRow.remove();
                                
                                // Update cart totals
                                updateCartTotals(data);
                                
                                // Update cart count in header
                                updateCartCount(data.cart_count || 0);
                                
                                // Check if cart is empty
                                checkEmptyCart();
                                
                                // Show success message
                                showToast(data.message || 'Item removed from cart!', 'success');
                            }, 300);
                        }
                    } else {
                        throw new Error(data.message || 'Failed to remove item');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Restore button state
                    button.disabled = false;
                    button.style.opacity = '1';
                    button.innerHTML = originalContent;
                    
                    // Show error message
                    showToast(error.message || 'Error removing item from cart', 'error');
                });
            });
        });
    }

    /**
     * Initialize AJAX cart item quantity update
     */
    function initializeCartUpdate() {
        const updateForms = document.querySelectorAll('form[action*="cart/update"]');
        
        updateForms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const button = form.querySelector('button[type="submit"]');
                const quantityInput = form.querySelector('input[name="quantity"]');
                
                if (!button || !quantityInput) return;
                
                // Disable form elements
                button.disabled = true;
                quantityInput.disabled = true;
                
                // Add loading state
                const originalContent = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                // Get form data
                const formData = new FormData(form);
                
                // Send AJAX request
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update item subtotal in the same row
                        const row = form.closest('tr, .cart-item');
                        const subtotalCell = row.querySelector('.product-total');
                        if (subtotalCell && data.formatted_item_subtotal) {
                            subtotalCell.textContent = data.formatted_item_subtotal;
                        }
                        
                        // Update cart totals
                        updateCartTotals(data);
                        
                        // Update cart count in header
                        updateCartCount(data.cart_count || 0);
                        
                        // Show success message
                        showToast(data.message || 'Cart updated successfully!', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to update cart');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast(error.message || 'Error updating cart', 'error');
                })
                .finally(() => {
                    // Restore form state
                    button.disabled = false;
                    quantityInput.disabled = false;
                    button.innerHTML = originalContent;
                });
            });
        });
    }    /**
     * Initialize AJAX coupon operations
     */
    function initializeCartCouponOperations() {
        // Initialize coupon apply form
        const couponForm = document.querySelector('form[action*="apply-coupon"]');
        
        if (couponForm) {
            couponForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const button = couponForm.querySelector('button[type="submit"]');
                const input = couponForm.querySelector('input[name="code"]');
                
                if (!button || !input) return;
                
                // Validate input
                const couponCode = input.value.trim();
                if (!couponCode) {
                    showToast('Please enter a coupon code', 'error');
                    return;
                }
                
                // Disable form elements
                button.disabled = true;
                input.disabled = true;
                
                // Add loading state
                const originalContent = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Applying...';
                
                // Get form data
                const formData = new FormData(couponForm);
                
                // Send AJAX request
                fetch(couponForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update cart totals
                        updateCartTotals(data);
                        
                        // Update coupon display
                        updateCouponDisplay(data);
                        
                        // Clear the input
                        input.value = '';
                        
                        // Show success message
                        showToast(data.message || 'Coupon applied successfully!', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to apply coupon');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast(error.message || 'Error applying coupon', 'error');
                })
                .finally(() => {
                    // Restore form state
                    button.disabled = false;
                    input.disabled = false;
                    button.innerHTML = originalContent;
                });
            });
        }

        // Initialize coupon remove functionality
        initializeCouponRemoval();
    }

    /**
     * Initialize AJAX coupon removal
     */
    function initializeCouponRemoval() {
        // Use event delegation to handle dynamically added remove buttons
        document.addEventListener('click', function(e) {
            if (e.target.matches('.remove-coupon-btn, .btn-remove-coupon') || 
                e.target.closest('.remove-coupon-btn, .btn-remove-coupon')) {
                
                e.preventDefault();
                
                const button = e.target.matches('.remove-coupon-btn, .btn-remove-coupon') ? 
                              e.target : e.target.closest('.remove-coupon-btn, .btn-remove-coupon');
                
                // Get the form or create a temporary one
                let form = button.closest('form');
                if (!form) {
                    // Create a temporary form for the DELETE request
                    form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '/cart/remove-coupon';
                    
                    // Add CSRF token
                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrfInput);
                    
                    // Add method override for DELETE
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                }
                
                // Disable button to prevent double clicks
                button.disabled = true;
                button.style.opacity = '0.6';
                
                // Add loading state
                const originalContent = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                
                // Get form data
                const formData = new FormData(form);
                
                // Send AJAX request
                fetch(form.action, {
                    method: 'POST', // Laravel uses POST with method override
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update cart totals
                        updateCartTotals(data);
                        
                        // Update coupon display
                        updateCouponDisplay(data);
                        
                        // Show success message
                        showToast(data.message || 'Coupon removed successfully!', 'success');
                    } else {
                        throw new Error(data.message || 'Failed to remove coupon');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    
                    // Restore button state
                    button.disabled = false;
                    button.style.opacity = '1';
                    button.innerHTML = originalContent;
                    
                    // Show error message
                    showToast(error.message || 'Error removing coupon', 'error');
                });
            }
        });
    }

    /**
     * Update coupon display after apply/remove operations
     */
    function updateCouponDisplay(data) {
        const couponDisplay = document.querySelector('.applied-coupon-display');
        const couponForm = document.querySelector('.coupon-apply-form');
        
        if (data.applied_coupon) {
            // Show applied coupon
            if (couponDisplay) {
                couponDisplay.innerHTML = `
                    <div class="alert alert-success d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-tag"></i> 
                            Coupon "${data.applied_coupon.code}" applied 
                            (${data.applied_coupon.discount_amount}% off)
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-coupon-btn">
                            <i class="fas fa-times"></i> Remove
                        </button>
                    </div>
                `;
                couponDisplay.style.display = 'block';
            }
            
            // Hide coupon form
            if (couponForm) {
                couponForm.style.display = 'none';
            }
        } else {
            // Hide applied coupon display
            if (couponDisplay) {
                couponDisplay.style.display = 'none';
                couponDisplay.innerHTML = '';
            }
            
            // Show coupon form
            if (couponForm) {
                couponForm.style.display = 'block';
            }
        }
    }    /**
     * Update cart totals in the sidebar
     */
    function updateCartTotals(data) {
        // Update subtotal
        const subtotalElement = document.querySelector('.cart-subtotal, [data-cart-subtotal]');
        if (subtotalElement && data.formatted_cart_subtotal) {
            subtotalElement.textContent = data.formatted_cart_subtotal;
        }
        
        // Update discount
        const discountElement = document.querySelector('.cart-discount, [data-cart-discount]');
        if (discountElement) {
            if (data.formatted_cart_discount && parseFloat(data.cart_discount) > 0) {
                discountElement.textContent = `-${data.formatted_cart_discount}`;
                discountElement.parentElement.style.display = 'table-row';
            } else {
                discountElement.textContent = '$0.00';
                discountElement.parentElement.style.display = 'none';
            }
        }
        
        // Update total
        const totalElement = document.querySelector('.cart-total, [data-cart-total]');
        if (totalElement && data.formatted_cart_total) {
            totalElement.textContent = data.formatted_cart_total;
        }

        // Alternative selectors for different table structures
        const totalCells = document.querySelectorAll('td');
        totalCells.forEach(cell => {
            const text = cell.textContent.trim();
            if (text.includes('Subtotal:') && data.formatted_cart_subtotal) {
                const nextCell = cell.nextElementSibling;
                if (nextCell) nextCell.textContent = data.formatted_cart_subtotal;
            } else if (text.includes('Discount:')) {
                const nextCell = cell.nextElementSibling;
                if (nextCell) {
                    if (data.formatted_cart_discount && parseFloat(data.cart_discount) > 0) {
                        nextCell.textContent = `-${data.formatted_cart_discount}`;
                        cell.parentElement.style.display = 'table-row';
                    } else {
                        nextCell.textContent = '$0.00';
                        cell.parentElement.style.display = 'none';
                    }
                }
            } else if (text.includes('Total:') && data.formatted_cart_total) {
                const nextCell = cell.nextElementSibling;
                if (nextCell) nextCell.textContent = data.formatted_cart_total;
            }
        });

        // Update any elements with specific data attributes
        const elementsToUpdate = [
            { selector: '[data-subtotal]', value: data.formatted_cart_subtotal },
            { selector: '[data-discount]', value: data.formatted_cart_discount },
            { selector: '[data-total]', value: data.formatted_cart_total },
            { selector: '[data-cart-count]', value: data.cart_count }
        ];

        elementsToUpdate.forEach(({ selector, value }) => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(element => {
                if (value !== undefined && value !== null) {
                    if (selector === '[data-discount]') {
                        element.textContent = parseFloat(data.cart_discount) > 0 ? `-${value}` : '$0.00';
                    } else {
                        element.textContent = value;
                    }
                }
            });
        });
    }

    /**
     * Update cart count indicator in header
     */
    function updateCartCount(count) {
        const cartCountElements = document.querySelectorAll('.cart-count, .cart-count-indicator');
        
        cartCountElements.forEach(element => {
            if (count > 0) {
                element.textContent = count;
                element.style.display = 'inline-flex';
                
                // Add animation
                element.classList.remove('cart-count-animated');
                void element.offsetWidth; // Force reflow
                element.classList.add('cart-count-animated');
                
                setTimeout(() => {
                    element.classList.remove('cart-count-animated');
                }, 500);
            } else {
                element.style.display = 'none';
            }
        });
    }

    /**
     * Check if cart is empty and show appropriate message
     */
    function checkEmptyCart() {
        const cartTable = document.querySelector('.cart-table tbody');
        const emptyCartMessage = document.querySelector('.empty-cart-message');
        
        if (cartTable) {
            const remainingItems = cartTable.querySelectorAll('tr').length;
            
            if (remainingItems === 0) {
                // Hide cart table and show empty message
                const cartWrap = document.querySelector('.cart-table-wrap');
                if (cartWrap) {
                    cartWrap.innerHTML = `
                        <div class="text-center empty-cart-message">
                            <h3>Your cart is empty</h3>
                            <p>Looks like you haven't added any products to your cart yet.</p>
                            <a href="/shop" class="boxed-btn mt-4">Continue Shopping</a>
                        </div>
                    `;
                }
                
                // Hide checkout button
                const checkoutButton = document.querySelector('a[href*="checkout"]');
                if (checkoutButton) {
                    checkoutButton.style.display = 'none';
                }
            }
        }
    }

    /**
     * Show toast notification
     */
    function showToast(message, type = 'success') {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            toastContainer.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                max-width: 350px;
            `;
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast ${type === 'success' ? 'cart-toast' : 'error-toast'}`;
        toast.style.cssText = `
            background-color: ${type === 'success' ? '#2ecc71' : '#e74c3c'};
            color: white;
            padding: 15px 25px;
            border-radius: 5px;
            margin-bottom: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            animation: slideInRight 0.5s ease;
            font-size: 14px;
            font-weight: 500;
            border-left: 5px solid ${type === 'success' ? '#27ae60' : '#c0392b'};
        `;
        toast.textContent = message;
        
        // Add CSS animation
        if (!document.querySelector('#cart-toast-styles')) {
            const style = document.createElement('style');
            style.id = 'cart-toast-styles';
            style.textContent = `
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }                .cart-count-animated {
                    animation: cartCountPulse 0.5s ease;
                }
                @keyframes cartCountPulse {
                    0% { transform: scale(1); }
                    50% { transform: scale(1.2); }
                    100% { transform: scale(1); }
                }
                
                /* Cart image hover effects */
                .cart-table .product-image a {
                    display: block;
                    border-radius: 8px;
                    overflow: hidden;
                    transition: all 0.3s ease;
                }
                .cart-table .product-image a:hover {
                    transform: scale(1.05);
                    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                }
                .cart-table .product-image img {
                    transition: all 0.3s ease;
                }
                .cart-table .product-image a:hover img {
                    filter: brightness(1.1);
                }
            `;
            document.head.appendChild(style);
        }
        
        // Add toast to container
        toastContainer.appendChild(toast);
        
        // Auto remove after 3 seconds
        setTimeout(() => {
            toast.style.animation = 'slideOutRight 0.5s ease';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 500);
        }, 3000);
    }
});
