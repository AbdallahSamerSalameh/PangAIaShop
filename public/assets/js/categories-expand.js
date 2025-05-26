/**
 * Categories expand/collapse functionality
 */
document.addEventListener('DOMContentLoaded', function() {    // Find the category section - it could have different class names on different pages
    // Look for a section with both 'product-section' class and category items inside
    const productSections = document.querySelectorAll('.product-section');
    let categories = null;
    
    // Find the product section that contains category items
    for (const section of productSections) {
        if (section.querySelector('.category-item')) {
            categories = section;
            break;
        }
    }
    
    if (!categories) return;
    
    // Find all categories and count them
    const categoryItems = categories.querySelectorAll('.category-item');
    const totalCategories = categoryItems.length;
    
    // If we have more than 6 categories, we need to add expand/collapse functionality
    if (totalCategories <= 6) return;

    // Hide categories beyond the first 6
    for (let i = 6; i < categoryItems.length; i++) {
        categoryItems[i].classList.add('hidden-category');
    }
    
    // Create the button container
    const buttonContainer = document.createElement('div');
    buttonContainer.className = 'col-12 text-center mt-4';
    
    // Create the expand/collapse button
    const expandButton = document.createElement('button');
    expandButton.className = 'boxed-btn expand-categories-btn';
    expandButton.innerHTML = '<i class="fas fa-chevron-down mr-2"></i> Show All Categories';
    expandButton.setAttribute('aria-expanded', 'false');
    
    // Add button to container
    buttonContainer.appendChild(expandButton);
      // Add the button container after the categories
    // Find the row that contains the category items
    const categoryRow = Array.from(categories.querySelectorAll('.row')).find(row => 
        row.querySelector('.category-item')
    );
    
    if (categoryRow) {
        categoryRow.appendChild(buttonContainer);
    }
    
    // Add event listener to the button
    expandButton.addEventListener('click', function() {
        const isExpanded = this.getAttribute('aria-expanded') === 'true';
        
        // Toggle categories visibility
        for (let i = 6; i < categoryItems.length; i++) {
            categoryItems[i].classList.toggle('hidden-category');
        }
        
        // Update button text and aria attribute
        if (isExpanded) {
            this.innerHTML = '<i class="fas fa-chevron-down mr-2"></i> Show All Categories';
            this.setAttribute('aria-expanded', 'false');
        } else {
            this.innerHTML = '<i class="fas fa-chevron-up mr-2"></i> Show Less';
            this.setAttribute('aria-expanded', 'true');
        }
    });
    
    // Add CSS for hidden categories
    const style = document.createElement('style');
    style.textContent = `
        .hidden-category {
            display: none !important;
        }
        
        .expand-categories-btn {
            transition: all 0.3s ease;
        }
        
        .expand-categories-btn:hover {
            transform: translateY(-2px);
        }
    `;
    document.head.appendChild(style);
});
