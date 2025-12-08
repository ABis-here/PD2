    </main>

    <footer class="text-center text-lg-start mt-4">
        <div class="text-center p-3">
            © <?php echo date('Y'); ?> Kino Duomenys
            Autoriai - Augustas Baublys, Benediktas Gudžinskas
            | <a href="sitemap.php" class="text-white">Svetainės žemėlapis</a>
            | <a href="mailto:bgudzinskas@gmail.com" class="text-white">Kontaktai</a>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Your custom JavaScript -->
    <script src="assets/js/script.js"></script>
    
    <!-- IMPORTANT: Initialize AJAX after everything loads -->
    <script>
    // Wait for everything to be fully loaded
    window.addEventListener('load', function() {
        console.log('Page fully loaded, initializing watchlist AJAX...');
        
        // Manually attach event listeners to watchlist forms
        const watchlistForms = document.querySelectorAll('.watchlist-form, form[action*="add_to_watchlist"]');
        
        watchlistForms.forEach(form => {
            // Remove any existing listeners
            const newForm = form.cloneNode(true);
            form.parentNode.replaceChild(newForm, form);
            
            // Add new listener
            newForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Watchlist form intercepted!');
                
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
                    console.log('Response received');
                    return response.json();
                })
                .then(data => {
                    console.log('Data:', data);
                    
                    if (data.success) {
                        // Update button
                        button.innerHTML = data.in_watchlist ? '✅ Jau sąraše' : '✚ Žiūrėti vėliau';
                        button.className = data.in_watchlist ? 
                            'btn btn-success w-100' : 'btn btn-primary w-100';
                        
                        // Show beautiful notification
                        showWatchlistNotification(data.message, 'success');
                        
                        // Update count if exists
                        const countElement = document.getElementById('watchlistCount');
                        if (countElement) {
                            countElement.textContent = data.watchlist_count;
                        }
                    } else {
                        showWatchlistNotification(data.message || 'Klaida!', 'error');
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
                    console.error('Fetch error:', error);
                    showWatchlistNotification('Įvyko klaida! Bandykite dar kartą.', 'error');
                    button.innerHTML = originalText;
                    button.disabled = false;
                });
            });
        });
        
        // Function to show beautiful notifications
        function showWatchlistNotification(message, type = 'success') {
            // Remove existing
            document.querySelectorAll('.watchlist-notification').forEach(n => n.remove());
            
            // Create notification
            const notification = document.createElement('div');
            notification.className = `watchlist-notification alert alert-${type} alert-dismissible fade show`;
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                min-width: 300px;
                max-width: 400px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                animation: slideIn 0.3s ease-out;
            `;
            
            // Add icon
            let icon = '✅';
            if (type === 'error') icon = '❌';
            if (type === 'warning') icon = '⚠️';
            
            notification.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="me-2" style="font-size: 1.2rem;">${icon}</div>
                    <div class="flex-grow-1">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white" 
                            onclick="this.parentElement.parentElement.remove()"></button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.style.opacity = '0';
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        if (notification.parentNode) notification.remove();
                    }, 300);
                }
            }, 4000);
        }
        
        // Add CSS animation
        if (!document.querySelector('#watchlist-notification-styles')) {
            const style = document.createElement('style');
            style.id = 'watchlist-notification-styles';
            style.textContent = `
                @keyframes slideIn {
                    from {
                        transform: translateX(100%);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                
                .watchlist-notification {
                    transition: all 0.3s ease;
                }
            `;
            document.head.appendChild(style);
        }
        
        console.log(`Found ${watchlistForms.length} watchlist forms`);
    });
    </script>
</body>
</html>