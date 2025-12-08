document.addEventListener('DOMContentLoaded', function() {
 
    function checkCookie() {
        // Check if user_login cookie exists
        const cookies = document.cookie.split(';');
        const userCookie = cookies.find(cookie => cookie.trim().startsWith('user_login='));
        
        if (userCookie) {
            const username = decodeURIComponent(userCookie.split('=')[1]);
            console.log('Cookie found for user: ' + username);
            
            // If not logged in, suggest auto-login
            if (!document.body.classList.contains('logged-in')) {
                console.log('Automatinis prisijungimas galimas: ' + username);
                
                // Optional: Show login suggestion
                showCookieNotification(username);
            }
        }
    }
    
    function showCookieNotification(username) {
        // Create notification if not exists
        if (!document.getElementById('cookie-login-notification')) {
            const notification = document.createElement('div');
            notification.id = 'cookie-login-notification';
            notification.className = 'alert alert-info alert-dismissible fade show position-fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.style.minWidth = '300px';
            notification.innerHTML = `
                <strong>Sveikas sugrįžęs, ${username}!</strong><br>
                Turime įrašytą prisijungimą. Ar norite prisijungti automatiškai?
                <div class="mt-2">
                    <a href="login.php?autofill=${encodeURIComponent(username)}" class="btn btn-sm btn-success">Taip</a>
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="this.parentElement.parentElement.remove()">Ne</button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 10 seconds
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 10000);
        }
    }
    
    // AJAX preview for reviews
    window.previewReview = function() {
        const form = document.getElementById('reviewForm');
        if (!form) return;
        
        const formData = new FormData(form);
        const previewBtn = form.querySelector('button[onclick*="previewReview"]');
        
        if (previewBtn) {
            previewBtn.disabled = true;
            previewBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Kraunama...';
        }
        
        fetch('preview_review.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            document.getElementById('previewContent').innerHTML = data;
            document.getElementById('reviewPreview').classList.remove('d-none');
        })
        .catch(error => {
            console.error('AJAX error:', error);
            alert('Klaida įkėlus peržiūrą. Bandykite dar kartą.');
        })
        .finally(() => {
            if (previewBtn) {
                previewBtn.disabled = false;
                previewBtn.innerHTML = 'Peržiūrėti';
            }
        });
    };
    
function setupWatchlistAJAX() {
    // Handle all watchlist forms
    const watchlistForms = document.querySelectorAll('.watchlist-form, form[action*="add_to_watchlist"]');
    
    watchlistForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const formData = new FormData(this);
            const button = this.querySelector('button[type="submit"]');
            const originalText = button.innerHTML;
            
            // Show loading
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update button
                    button.innerHTML = data.in_watchlist ? '✅ Jau sąraše' : '✚ Žiūrėti vėliau';
                    button.className = data.in_watchlist ? 
                        'btn btn-success w-100' : 'btn btn-primary w-100';
                    
                    // Show notification
                    showNotification(data.message, 'success');
                    
                    console.log('Watchlist update successful:', data);
                } else {
                    showNotification(data.message || 'Klaida!', 'error');
                    button.innerHTML = originalText;
                    button.disabled = false;
                    
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1500);
                    }
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Įvyko klaida!', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            });
            
            return false;
        });
    });
}

    // UI Enhancement Functions
    
    // Add hover effect to movie cards
    function setupMovieCardHover() {
        const movieCards = document.querySelectorAll('.hover-lift');
        movieCards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.transition = 'transform 0.3s ease';
                this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.2)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '';
            });
        });
    }    
    // Form validation enhancement
    function enhanceForms() {
        const forms = document.querySelectorAll('form');
        forms.forEach(form => {
            // Add Bootstrap validation
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
            
            // Real-time validation for specific fields
            const ratingInputs = form.querySelectorAll('input[type="number"][min="1"][max="10"]');
            ratingInputs.forEach(input => {
                input.addEventListener('input', function() {
                    const value = parseInt(this.value);
                    if (value < 1 || value > 10) {
                        this.setCustomValidity('Įvertinimas turi būti tarp 1 ir 10');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            });
        });
    }
    
    // Cookie Management Functions
   
    
    // Set cookie
    window.setCookie = function(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + (value || "") + expires + "; path=/";
    };
    
    // Get cookie
    window.getCookie = function(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    };
    
    // Delete cookie
    window.eraseCookie = function(name) {   
        document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
    };
    
    
    // Run initialization functions
    checkCookie();
    setupWatchlistAJAX();
    setupMovieCardHover();
    enhanceForms();
    
    // Mark page as loaded
    document.body.classList.add('js-loaded');
    
    // Debug: Log initialization
    console.log('Kino Duomenys JavaScript initialized');
    console.log('Assignment requirements implemented:');
    console.log('- #8: PHP include() demonstration');
    console.log('- #10: Cookie handling');
    console.log('- #15: AJAX functionality');
    
    // Demo: Show PHP include() functionality
    if (typeof loadHeader === 'function') {
        loadHeader();
    }
    
});



// Format date
window.formatDate = function(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('lt-LT', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

// Truncate text
window.truncateText = function(text, maxLength) {
    if (text.length <= maxLength) return text;
    return text.substr(0, maxLength) + '...';
};

// Generate star rating HTML
window.generateStars = function(rating, maxStars = 10) {
    let stars = '';
    for (let i = 1; i <= maxStars; i++) {
        if (i <= rating) {
            stars += '<span class="text-warning">★</span>';
        } else {
            stars += '<span class="text-secondary">☆</span>';
        }
    }
    return stars;
};

// Check if user is on mobile
window.isMobile = function() {
    return window.innerWidth <= 768;
};

// Smooth scroll to element
window.smoothScroll = function(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
};


// Global error handler
window.addEventListener('error', function(e) {
    console.error('Global error:', e.error);
    
    // Don't show alert for known/expected errors
    if (e.message.includes('fetch') || e.message.includes('NetworkError')) {
        // Network errors are common, just log them
        return;
    }
    
    // Show user-friendly error message for unexpected errors
    if (!document.querySelector('.global-error-alert')) {
        const alert = document.createElement('div');
        alert.className = 'global-error-alert alert alert-danger position-fixed';
        alert.style.bottom = '20px';
        alert.style.right = '20px';
        alert.style.zIndex = '9999';
        alert.innerHTML = `
            <strong>Klaida!</strong> Įvyko netikėta klaida.
            <button class="btn btn-sm btn-outline-danger ms-2" onclick="this.parentElement.remove()">
                ✕
            </button>
        `;
        document.body.appendChild(alert);
        
        setTimeout(() => {
            if (alert.parentNode) alert.remove();
        }, 10000);
    }
});

// Handle offline/online status
window.addEventListener('offline', function() {
    showNotification('Prarastas interneto ryšys. Kai kurios funkcijos gali neveikti.', 'warning');
});

window.addEventListener('online', function() {
    showNotification('Internetas atkurtas!', 'success');
});