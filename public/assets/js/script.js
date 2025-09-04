// public/assets/js/script.js

document.addEventListener('DOMContentLoaded', function() {
    // Dark mode toggle
    document.addEventListener('click', function(e) {
        if (e.target && e.target.matches('.toggle-dark')) {
            e.preventDefault();
            fetch('/public/settings.php?toggle_dark=1')
                .then(() => location.reload())
                .catch(error => console.error('Error toggling dark mode:', error));
        }
    });

    // Add to cart with animation
    document.addEventListener('click', function(e) {
        if (e.target && (e.target.matches('.add-to-cart') || e.target.closest('.add-to-cart'))) {
            const button = e.target.matches('.add-to-cart') ? e.target : e.target.closest('.add-to-cart');
            const originalText = button.innerHTML;
            
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            button.disabled = true;
            
            // Simulate adding to cart (the actual add happens via href)
            setTimeout(() => {
                button.innerHTML = '<i class="fas fa-check"></i> Added!';
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.disabled = false;
                }, 1000);
            }, 500);
        }
    });

    // Form validation enhancement
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn && !submitBtn.disabled) {
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
                submitBtn.disabled = true;
                
                // Re-enable after 3 seconds in case of issues
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 3000);
            }
        });
    });

    // Auto-hide alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (alert.classList.contains('alert-success')) {
            setTimeout(() => {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 5000);
        }
    });

    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});

// Payment status checking utility
function showPaymentStatus(message, type = 'info') {
    const statusDiv = document.getElementById('payment-status');
    if (statusDiv) {
        const alertClass = `alert-${type}`;
        statusDiv.innerHTML = `<div class="alert ${alertClass} small">${message}</div>`;
    }
}

// Format currency for display
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES'
    }).format(amount);
}

// Copy text to clipboard utility
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        // Show temporary success message
        const toast = document.createElement('div');
        toast.className = 'position-fixed top-0 end-0 m-3 alert alert-success';
        toast.innerHTML = '<i class="fas fa-check"></i> Copied to clipboard!';
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy: ', err);
    });
}