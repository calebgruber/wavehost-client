document.addEventListener('DOMContentLoaded', function() {
    // Page loader
    const loader = document.getElementById('page-loader');
    
    // Show loader for a random time between 500ms and 1000ms
    const loaderTime = Math.floor(Math.random() * (1000 - 500 + 1)) + 500;
    
    setTimeout(function() {
        loader.classList.add('loader-hidden');
        
        // Remove loader from DOM after transition completes
        loader.addEventListener('transitionend', function() {
            if (loader.parentNode) {
                loader.parentNode.removeChild(loader);
            }
        });
    }, loaderTime);
    
    // Dropdown menu functionality
    const dropdowns = document.querySelectorAll('.dropdown');
    
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('click', (e) => {
            dropdown.classList.toggle('active');
            e.stopPropagation();
        });
    });
    
    // Close dropdowns when clicking outside
    document.addEventListener('click', () => {
        dropdowns.forEach(dropdown => {
            dropdown.classList.remove('active');
        });
    });
});