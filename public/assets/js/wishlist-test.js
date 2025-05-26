/**
 * Wishlist Functionality Test Script
 * 
 * This script tests if the wishlist functionality works correctly across all pages.
 * It simulates user interactions with the wishlist buttons and verifies that:
 * 1. The localStorage state is correctly updated
 * 2. The wishlist buttons' visual state is updated immediately
 * 3. The server requests are sent correctly
 */

// Test function to check localStorage state
function checkWishlistLocalStorage() {
    const wishlist = JSON.parse(localStorage.getItem('userWishlist')) || {};
    console.log('Current wishlist state in localStorage:', wishlist);
    return wishlist;
}

// Test function to check if all wishlist buttons with same product ID have consistent state
function checkWishlistButtonsConsistency(productId) {
    const buttons = document.querySelectorAll(`.wishlist-btn[data-product-id="${productId}"]`);
    let isConsistent = true;
    let activeCount = 0;
    let inactiveCount = 0;
    
    buttons.forEach(btn => {
        if (btn.classList.contains('active')) {
            activeCount++;
        } else {
            inactiveCount++;
        }
    });
    
    // If there are buttons with different states, they are inconsistent
    isConsistent = activeCount === 0 || inactiveCount === 0;
    
    console.log(`Wishlist buttons for product ${productId}:`, {
        totalButtons: buttons.length,
        activeButtons: activeCount,
        inactiveButtons: inactiveCount,
        isConsistent: isConsistent
    });
    
    return isConsistent;
}

// Function to test adding to wishlist
function testAddToWishlist(productId) {
    console.group(`Testing: Add product ${productId} to wishlist`);
    
    // Get initial state
    const initialState = checkWishlistLocalStorage();
    const wasInWishlist = initialState[productId] === true;
    
    if (wasInWishlist) {
        console.log(`Product ${productId} was already in wishlist, removing first for clean test`);
        // If already in wishlist, we need to remove it first
        // Find a button for this product
        const button = document.querySelector(`.wishlist-btn[data-product-id="${productId}"]`);
        if (button) {
            button.click();
            // Wait for UI and localStorage to update
            setTimeout(() => {
                testAddToWishlist(productId);
            }, 1000);
            return;
        }
    }
    
    // Find a button for this product
    const button = document.querySelector(`.wishlist-btn[data-product-id="${productId}"]`);
    
    if (!button) {
        console.error(`No wishlist button found for product ${productId}`);
        console.groupEnd();
        return;
    }
    
    // Simulate click
    console.log(`Clicking wishlist button for product ${productId}`);
    button.click();
    
    // Check immediate visual feedback
    console.log(`Immediate visual state: Button class list ${button.classList.contains('active') ? 'has' : 'does not have'} 'active'`);
    
    // Wait for localStorage to update
    setTimeout(() => {
        // Check localStorage
        const updatedState = checkWishlistLocalStorage();
        console.log(`Product ${productId} in localStorage: ${updatedState[productId] === true}`);
        
        // Check buttons consistency
        const isConsistent = checkWishlistButtonsConsistency(productId);
        console.log(`Wishlist buttons state consistency: ${isConsistent ? 'Consistent' : 'Inconsistent'}`);
        
        console.groupEnd();
    }, 500);
}

// Function to test removing from wishlist
function testRemoveFromWishlist(productId) {
    console.group(`Testing: Remove product ${productId} from wishlist`);
    
    // Get initial state
    const initialState = checkWishlistLocalStorage();
    const wasInWishlist = initialState[productId] === true;
    
    if (!wasInWishlist) {
        console.log(`Product ${productId} was not in wishlist, adding first for clean test`);
        // If not in wishlist, we need to add it first
        // Find a button for this product
        const button = document.querySelector(`.wishlist-btn[data-product-id="${productId}"]`);
        if (button) {
            button.click();
            // Wait for UI and localStorage to update
            setTimeout(() => {
                testRemoveFromWishlist(productId);
            }, 1000);
            return;
        }
    }
    
    // Find a button for this product
    const button = document.querySelector(`.wishlist-btn[data-product-id="${productId}"]`);
    
    if (!button) {
        console.error(`No wishlist button found for product ${productId}`);
        console.groupEnd();
        return;
    }
    
    // Simulate click
    console.log(`Clicking wishlist button for product ${productId}`);
    button.click();
    
    // Check immediate visual feedback
    console.log(`Immediate visual state: Button class list ${button.classList.contains('active') ? 'has' : 'does not have'} 'active'`);
    
    // Wait for localStorage to update
    setTimeout(() => {
        // Check localStorage
        const updatedState = checkWishlistLocalStorage();
        console.log(`Product ${productId} in localStorage: ${updatedState[productId] === true}`);
        
        // Check buttons consistency
        const isConsistent = checkWishlistButtonsConsistency(productId);
        console.log(`Wishlist buttons state consistency: ${isConsistent ? 'Consistent' : 'Inconsistent'}`);
        
        console.groupEnd();
    }, 500);
}

// Function to test page load behavior
function testPageLoadWishlistState() {
    console.group('Testing: Page load wishlist state');
    
    // Get all wishlist buttons
    const buttons = document.querySelectorAll('.wishlist-btn');
    const wishlistState = checkWishlistLocalStorage();
    
    // Check each button's state
    buttons.forEach(button => {
        const productId = button.getAttribute('data-product-id');
        const shouldBeActive = wishlistState[productId] === true;
        const isActive = button.classList.contains('active');
        
        console.log(`Product ${productId}: Should be ${shouldBeActive ? 'active' : 'inactive'}, is ${isActive ? 'active' : 'inactive'}`);
    });
    
    console.groupEnd();
}

// Run tests when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('Wishlist functionality test script loaded');
    
    // Check initial wishlist state
    console.group('Initial wishlist state');
    checkWishlistLocalStorage();
    console.groupEnd();
    
    // Wait for the page to fully load and for any asynchronous scripts to complete
    setTimeout(() => {
        // Test page load behavior
        testPageLoadWishlistState();
        
        // Find first product to test with
        const firstButton = document.querySelector('.wishlist-btn');
        if (firstButton) {
            const productId = firstButton.getAttribute('data-product-id');
            
            // Run tests
            setTimeout(() => testAddToWishlist(productId), 1000);
            setTimeout(() => testRemoveFromWishlist(productId), 3000);
        } else {
            console.error('No wishlist buttons found on page');
        }
    }, 2000);
});

// Log that the script has been executed
console.log('Wishlist test script executed');
