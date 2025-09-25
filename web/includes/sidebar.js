document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    lucide.createIcons();
    
    // Toggle sidebar on mobile
    const toggleBtn = document.querySelector('.toggle-sidebar');
    const sidebar = document.getElementById('sidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
        });
    }
});