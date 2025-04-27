<?php
// includes/loader.php
// Loading overlay script - will be included in all pages
// The loading overlay will appear between 500ms and 1sec randomly
?>
<div id="page-loader" class="page-loader">
    <div class="loader-content">
        <div class="spinner"></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loader = document.getElementById('page-loader');
    
    // Random duration between 500ms and 1sec
    const duration = Math.floor(Math.random() * (1000 - 500 + 1)) + 500;
    
    setTimeout(function() {
        loader.style.opacity = '0';
        setTimeout(function() {
            loader.style.display = 'none';
        }, 300);
    }, duration);
});
</script>