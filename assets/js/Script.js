// JavaScript Document
document.addEventListener('DOMContentLoaded', function () {

    //  Login Cookie Detection
    //  Checks if a saved login cookie exists and shows a suggestion box
    function checkCookie() {
        const cookies = document.cookie.split(';');
        const userCookie = cookies.find(c => c.trim().startsWith('user_login='));

        if (userCookie) {
            const username = decodeURIComponent(userCookie.split('=')[1]);

            if (!document.body.classList.contains('logged-in')) {
                showCookieNotification(username);
            }
        }
    }

    function showCookieNotification(username) {
        if (document.getElementById('cookie-login-notification')) return;

        const notif = document.createElement('div');
        notif.id = 'cookie-login-notification';
        notif.className = 'alert alert-info alert-dismissible fade show position-fixed';
        notif.style.top = '20px';
        notif.style.right = '20px';
        notif.style.zIndex = '9999';

        notif.innerHTML = `
            <strong>Welcome back, ${username}!</strong><br>
            We found a saved login session. Would you like to sign in automatically?
            <div class="mt-2">
                <a href="login.php?autofill=${encodeURIComponent(username)}" class="btn btn-sm btn-success">Yes</a>
                <button class="btn btn-sm btn-outline-secondary" onclick="this.closest('.alert').remove()">No</button>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        document.body.appendChild(notif);

        setTimeout(() => notif.remove(), 10000);
    }

    //  Review Preview via AJAX
    //  Sends review form data to the server and displays a live preview
    window.previewReview = function () {
        const form = document.getElementById('reviewForm');
        if (!form) return;

        const formData = new FormData(form);
        const previewBtn = form.querySelector('button[onclick*="previewReview"]');

        if (previewBtn) {
            previewBtn.disabled = true;
            previewBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Loading...';
        }

        fetch('preview_review.php', { method: 'POST', body: formData })
            .then(res => res.text())
            .then(html => {
                const content = document.getElementById('previewContent');
                const block = document.getElementById('reviewPreview');

                if (content && block) {
                    content.innerHTML = html;
                    block.classList.remove('d-none');
                }
            })
            .catch(err => console.error('Preview error:', err))
            .finally(() => {
                if (previewBtn) {
                    previewBtn.disabled = false;
                    previewBtn.innerHTML = 'Preview';
                }
            });
    };

    //  Watchlist AJAX Handler
    //  Adds or removes movies from the user's watchlist
    function setupWatchlistAJAX() {
        const forms = document.querySelectorAll('.watchlist-form, form[action*="add_to_watch_list"]');

        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;

                // Loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                fetch(this.action, { method: 'POST', body: formData })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response error');
                        return res.json();
                    })
                    .then(data => {
                        if (data.success) {
                            // Update button appearance
                            if (data.in_watchlist) {
                                submitBtn.innerHTML = 'In Watchlist';
                                submitBtn.className = 'btn btn-success w-100';
                            } else {
                                submitBtn.innerHTML = 'Watch Later';
                                submitBtn.className = 'btn btn-primary w-100';
                            }

                            if (typeof showNotification === 'function') {
                                showNotification(data.message, 'success');
                            }
                        } else {
                            // Display server-side error
                            if (typeof showNotification === 'function') {
                                showNotification(data.message || 'Error occurred.', 'error');
                            }
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;

                            if (data.redirect) {
                                setTimeout(() => (window.location.href = data.redirect), 1500);
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Watchlist error:', err);
                        if (typeof showNotification === 'function') {
                            showNotification('An unexpected error occurred.', 'error');
                        }
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });

                return false;
            });
        });
    }

    //  Movie Card Hover Effects
    //  Applies a hover animation to movie cards for visual enhancement
    function setupMovieCardHover() {
        const cards = document.querySelectorAll('.hover-lift');

        cards.forEach(card => {
            card.addEventListener('mouseenter', function () {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.2)';
                this.style.transition = 'all 0.3s ease';
            });

            card.addEventListener('mouseleave', function () {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = 'none';
            });
        });
    }

    //  Form Enhancements
    //  Adds Bootstrap validation and rating field validation
    function enhanceForms() {
        const forms = document.querySelectorAll('form');

        forms.forEach(form => {
            form.addEventListener('submit', function (e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });

            // Rating inputs (1-10)
            form.querySelectorAll('input[type="number"][min="1"][max="10"]').forEach(input => {
                input.addEventListener('input', function () {
                    const v = parseInt(this.value, 10);
                    this.setCustomValidity(v < 1 || v > 10 ? 'Rating must be between 1 and 10.' : '');
                });
            });
        });
    }

    //  Basic Cookie Utility Functions
    window.setCookie = function (name, value, days) {
        let expires = '';
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + days * 86400000);
            expires = '; expires=' + date.toUTCString();
        }
        document.cookie = `${name}=${value || ''}${expires}; path=/`;
    };

    window.getCookie = function (name) {
        const nameEQ = name + '=';
        return document.cookie.split(';').map(c => c.trim()).find(c => c.startsWith(nameEQ))?.substring(nameEQ.length) || null;
    };

    window.eraseCookie = function (name) {
        document.cookie = `${name}=; Max-Age=-99999999; path=/`;
    };

    //  Initialization — runs when the page loads
    checkCookie();
    setupWatchlistAJAX();
    setupMovieCardHover();
    enhanceForms();

    document.body.classList.add('js-loaded');
});

//  General Helper Functions (Formatting, Truncation, Stars, etc.)
window.formatDate = function (dateString) {
    return new Date(dateString).toLocaleDateString('lt-LT', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

window.truncateText = function (text, max) {
    return text.length <= max ? text : text.substring(0, max) + '...';
};

window.generateStars = function (rating, max = 10) {
    let stars = '';
    for (let i = 1; i <= max; i++) {
        stars += i <= rating
            ? '<span class="text-warning">★</span>'
            : '<span class="text-secondary">☆</span>';
    }
    return stars;
};

window.isMobile = function () {
    return window.innerWidth <= 768;
};

window.smoothScroll = function (id) {
    const el = document.getElementById(id);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'start' });
};

//  Global Error Logger
//  Logs unexpected JavaScript errors to the console without UI alerts
window.addEventListener('error', function (e) {
    console.error('Global JS Error:', e.error || e.message || e);
});

//  Online / Offline Status Notifications
window.addEventListener('offline', function () {
    if (typeof showNotification === 'function')
        showNotification('Internet connection lost.', 'warning');
});

window.addEventListener('online', function () {
    if (typeof showNotification === 'function')
        showNotification('Connection restored.', 'success');
});
