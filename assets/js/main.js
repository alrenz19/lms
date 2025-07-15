/**
 * LMS System - Main JavaScript
 * Contains utility functions and initialization for the blue theme
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // Initialize tooltips
    initTooltips();
    
    // Initialize mobile menu
    initMobileMenu();
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize alert dismissal
    initAlertDismissal();
    
    // Initialize toast system
    initToastSystem();
});

/**
 * Initialize tooltips
 */
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    
    tooltips.forEach(el => {
        el.classList.add('relative');
        
        el.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'absolute bg-gray-800 text-white text-xs rounded py-1 px-2 z-50 tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            tooltip.style.bottom = 'calc(100% + 5px)';
            tooltip.style.left = '50%';
            tooltip.style.transform = 'translateX(-50%)';
            tooltip.style.whiteSpace = 'nowrap';
            this.appendChild(tooltip);
        });
        
        el.addEventListener('mouseleave', function() {
            const tooltip = this.querySelector('.tooltip');
            if (tooltip) {
                tooltip.remove();
            }
        });
    });
}

/**
 * Initialize mobile menu
 */
function initMobileMenu() {
    const toggleBtn = document.querySelector('.toggle-sidebar');
    const sidebar = document.getElementById('sidebar');
    
    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('-translate-x-full');
        });
    }
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Check required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    highlightInvalidField(field, 'This field is required');
                } else {
                    removeFieldHighlight(field);
                }
            });
            
            // Check email fields
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(field => {
                if (field.value.trim() && !isValidEmail(field.value)) {
                    isValid = false;
                    highlightInvalidField(field, 'Please enter a valid email address');
                }
            });
            
            // Check password fields
            const passwordFields = form.querySelectorAll('input[type="password"][data-min-length]');
            passwordFields.forEach(field => {
                const minLength = parseInt(field.getAttribute('data-min-length'));
                if (field.value.trim() && field.value.length < minLength) {
                    isValid = false;
                    highlightInvalidField(field, `Password must be at least ${minLength} characters`);
                }
            });
            
            // Check password confirmation
            const passwordConfirmFields = form.querySelectorAll('input[data-confirm-field]');
            passwordConfirmFields.forEach(field => {
                const sourceFieldId = field.getAttribute('data-confirm-field');
                const sourceField = document.getElementById(sourceFieldId);
                
                if (sourceField && field.value !== sourceField.value) {
                    isValid = false;
                    highlightInvalidField(field, 'Passwords do not match');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
}

/**
 * Validate email format
 */
function isValidEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}

/**
 * Highlight invalid form field
 */
function highlightInvalidField(field, message) {
    // Remove any existing error
    removeFieldHighlight(field);
    
    // Add error classes
    field.classList.add('border-red-300', 'focus:border-red-500', 'focus:ring-red-200');
    
    // Create error message
    const errorMsg = document.createElement('p');
    errorMsg.className = 'text-red-500 text-xs mt-1 error-message';
    errorMsg.textContent = message;
    
    // Add error message after the field
    field.parentNode.insertBefore(errorMsg, field.nextSibling);
}

/**
 * Remove field highlight
 */
function removeFieldHighlight(field) {
    field.classList.remove('border-red-300', 'focus:border-red-500', 'focus:ring-red-200');
    
    // Remove any existing error messages
    const errorMsg = field.parentNode.querySelector('.error-message');
    if (errorMsg) {
        errorMsg.remove();
    }
}

/**
 * Initialize alert dismissal
 */
function initAlertDismissal() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    
    alerts.forEach(alert => {
        const closeBtn = alert.querySelector('.close-alert');
        if (closeBtn) {
            closeBtn.addEventListener('click', function() {
                alert.remove();
            });
        }
    });
    
    // Auto-dismiss success alerts after 5 seconds
    const successAlerts = document.querySelectorAll('.alert-success');
    successAlerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('opacity-0');
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

/**
 * Toast notification system
 */
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.className = 'fixed bottom-4 right-4 z-50 flex flex-col gap-2';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'transform transition-all duration-300 ease-in-out translate-x-full';
    
    // Set background color based on type
    let bgColor, textColor, iconName;
    switch (type) {
        case 'success':
            bgColor = 'bg-green-500';
            textColor = 'text-white';
            iconName = 'check-circle';
            break;
        case 'error':
            bgColor = 'bg-red-500';
            textColor = 'text-white';
            iconName = 'alert-circle';
            break;
        case 'warning':
            bgColor = 'bg-amber-500';
            textColor = 'text-white';
            iconName = 'alert-triangle';
            break;
        default: // info
            bgColor = 'bg-blue-500';
            textColor = 'text-white';
            iconName = 'info';
    }
    
    // Apply styles
    toast.className += ` ${bgColor} ${textColor} rounded-lg shadow-lg p-4 mb-2 flex items-center`;
    
    // Add content
    toast.innerHTML = `
        <i data-lucide="${iconName}" class="w-5 h-5 mr-2"></i>
        <span>${message}</span>
    `;
    
    // Add to container
    toastContainer.appendChild(toast);
    
    // Initialize icon
    if (typeof lucide !== 'undefined') {
        lucide.createIcons({
            attrs: {
                class: ["stroke-current"]
            }
        });
    }
    
    // Animate in
    setTimeout(() => {
        toast.classList.remove('translate-x-full');
        toast.classList.add('translate-x-0');
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        toast.classList.remove('translate-x-0');
        toast.classList.add('translate-x-full');
        
        // Remove from DOM after animation completes
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

/* Usage examples:
 * 1. Show success toast:
 *    showToast('Operation completed successfully', 'success');
 * 
 * 2. Show error toast:
 *    showToast('An error occurred', 'error');
 * 
 * 3. Show warning toast:
 *    showToast('Please confirm your action', 'warning');
 * 
 * 4. Show info toast (default):
 *    showToast('New message received');
 * 
 * 5. Usage with AJAX:
 *    fetch('/api/endpoint', {
 *       method: 'POST',
 *       body: JSON.stringify(data)
 *    })
 *    .then(response => response.json())
 *    .then(data => {
 *       showToast('Data saved successfully', 'success');
 *    })
 *    .catch(error => {
 *       showToast('Failed to save data', 'error');
 *    });
 */

/**
 * Initialize toast system
 */
function initToastSystem() {
    // Create a global function to show toasts
    window.showToast = showToast;
    
    // Override form submissions to show toast on successful action
    const forms = document.querySelectorAll('form[data-show-toast]');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const message = this.getAttribute('data-toast-message') || 'Operation completed successfully';
            const type = this.getAttribute('data-toast-type') || 'success';
            
            // Only store the toast data if it's a successful submission
            if (!form.hasAttribute('data-validate') || form.checkValidity()) {
                // Store toast info in session storage to display after page reload
                sessionStorage.setItem('pendingToast', JSON.stringify({
                    message: message,
                    type: type
                }));
            }
        });
    });
    
    // Check for pending toasts (from previous page actions)
    const pendingToast = sessionStorage.getItem('pendingToast');
    if (pendingToast) {
        try {
            const toastData = JSON.parse(pendingToast);
            showToast(toastData.message, toastData.type);
            sessionStorage.removeItem('pendingToast');
        } catch (e) {
            console.error('Error displaying toast:', e);
            sessionStorage.removeItem('pendingToast');
        }
    }
} 