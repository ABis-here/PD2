<?php
// Include configuration
require_once 'includes/config.php';

// Include database class
require_once 'php/Database.php';
$db = new Database();

// Get movie ID from URL
$movie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get movie details from database
$movie = $db->fetchOne("
    SELECT m.*, 
           AVG(r.rating) as avg_rating,
           COUNT(r.id) as review_count
    FROM movies m
    LEFT JOIN reviews r ON m.id = r.movie_id
    WHERE m.id = ?
    GROUP BY m.id
", [$movie_id]);

// If movie doesn't exist, redirect to movies list
if (!$movie) {
    header('Location: movies.php');
    exit();
}

// Set page title
$pageTitle = $movie['title'];

// Include header
include 'includes/header.php';

// Komentarų puslapiavimas
$reviewsPerPage = 10;
$reviewPage = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$reviewOffset = ($reviewPage - 1) * $reviewsPerPage;

// Get reviews for this movie with pagination
$reviews = $db->fetchAll("
    SELECT r.*, u.username
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.movie_id = ?
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
", [$movie_id, $reviewsPerPage, $reviewOffset]);

// Get total reviews count
$totalReviewsResult = $db->fetchOne("
    SELECT COUNT(*) as count
    FROM reviews r
    WHERE r.movie_id = ?
", [$movie_id]);
$totalReviews = $totalReviewsResult['count'];
$totalReviewPages = ceil($totalReviews / $reviewsPerPage);

// Get actors for this movie
$actors = $db->fetchAll("
    SELECT a.name, a.birth_year
    FROM actors a
    JOIN movie_actors ma ON a.id = ma.actor_id
    WHERE ma.movie_id = ?
    ORDER BY a.name
", [$movie_id]);
?>

<main class="container">

    <!-- Back button -->
    <div class="mb-3">
        <a href="movies.php" class="btn btn-outline-light">
            ← Atgal į filmų sąrašą
        </a>
    </div>

    <!-- Movie title -->
    <h2 class="text-light mb-3">
        <?php echo htmlspecialchars($movie['title']); ?>
        <?php if ($movie['release_year']): ?>
            <small class="text-muted">(<?php echo $movie['release_year']; ?>)</small>
        <?php endif; ?>
    </h2>

    <!-- TOP GREEN CARD - Movie Info -->
    <div class="card mb-4 card-green">
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <!-- Movie stats -->
                    <p><strong>Vidutinis įvertinimas:</strong> 
                        <span class="badge bg-light text-dark fs-6">
                            <?php echo $movie['avg_rating'] ? number_format($movie['avg_rating'], 1) : 'Nėra'; ?> / 10
                        </span>
                    </p>
                    <p><strong>Iš viso įvertinimų:</strong> 
                        <span class="badge bg-light text-dark"><?php echo $movie['review_count']; ?></span>
                    </p>
                    
                    <!-- Additional movie info -->
                    <?php if ($movie['director']): ?>
                        <p><strong>Režisierius:</strong> <?php echo htmlspecialchars($movie['director']); ?></p>
                    <?php endif; ?>
                    
                    <?php if ($movie['genre']): ?>
                        <p><strong>Žanras:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
                    <?php endif; ?>
                    
                    <p><strong>Aprašymas:</strong><br>
                    <?php echo nl2br(htmlspecialchars($movie['description'])); ?></p>
                </div>
                
                <div class="col-md-4">
                    <!-- Quick actions -->
                    <div class="card bg-light text-dark">
                        <div class="card-body">
                            <h5 class="card-title">Veiksmai</h5>
                            
                            <?php if (isset($_SESSION['user_id'])): ?>
                                <!-- Logged in - can review -->
                                <button class="btn btn-success w-100 mb-2" data-bs-toggle="modal" data-bs-target="#reviewModal">
                                    Pateikti įvertinimą
                                </button>
                                
                                <!-- Add to watchlist form - prevent default and use AJAX -->
                                <form method="POST" action="add_to_watchlist.php" class="mb-2 watchlist-form" 
                                      id="watchlistForm">
                                    <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
                                    <button type="submit" class="btn btn-primary w-100" id="watchlistBtn">
                                        <?php
                                        $inWatchlist = $db->fetchOne("SELECT id FROM watchlist WHERE movie_id = ? AND user_id = ?", 
                                                                   [$movie_id, $_SESSION['user_id']]);
                                        echo $inWatchlist ? 'Jau sąraše' : 'Žiūrėti vėliau';
                                        ?>
                                    </button>
                                </form>
                            <?php else: ?>
                                <!-- Not logged in -->
                                <p class="text-center">
                                    <small>Norite pridėti į sąrašą?</small><br>
                                    <a href="login.php" class="btn btn-sm btn-success mt-1 w-100">Prisijungti</a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Actors section -->
    <?php if (!empty($actors)): ?>
    <div class="card mb-4">
        <div class="card-body">
            <h4 class="card-title mb-3"> Aktoriai</h4>
            <div class="row">
                <?php foreach ($actors as $actor): ?>
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card">
                            <div class="card-body text-center">
                                <h6 class="card-title"><?php echo htmlspecialchars($actor['name']); ?></h6>
                                <?php if ($actor['birth_year']): ?>
                                    <p class="card-text text-muted">
                                        <small>Gimimo metai: <?php echo $actor['birth_year']; ?></small>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section: Comments -->
    <h3 class="text-light mb-3">Vartotojų įvertinimai ir komentarai</h3>

    <!-- Reviews statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Vidutinis</h5>
                    <p class="display-6">
                        <?php echo $movie['avg_rating'] ? number_format($movie['avg_rating'], 1) : '0.0'; ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Įvertinimų</h5>
                    <p class="display-6"><?php echo $totalReviews; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Žiūrėsiu</h5>
                    <p class="display-6">
                        <?php
                        try {
                            $watchlistCount = $db->fetchOne("SELECT COUNT(*) as count FROM watchlist WHERE movie_id = ?", [$movie_id])['count'];
                            echo $watchlistCount;
                        } catch (Exception $e) {
                            echo '0';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title">Metai</h5>
                    <p class="display-6"><?php echo $movie['release_year'] ?: '-'; ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Puslapio informacija -->
    <?php if ($totalReviews > 0): ?>
    <div class="alert alert-info">
        Rodomi įvertinimai: <strong><?php echo min(($reviewPage - 1) * $reviewsPerPage + 1, $totalReviews); ?>-<?php echo min($reviewPage * $reviewsPerPage, $totalReviews); ?></strong> 
        iš <strong><?php echo $totalReviews; ?></strong>
        (puslapis <?php echo $reviewPage; ?> iš <?php echo max(1, $totalReviewPages); ?>)
    </div>
    <?php endif; ?>

    <!-- COMMENTS – RED cards -->
    <?php if (empty($reviews)): ?>
        <div class="alert alert-info">
            Kol kas nėra įvertinimų. Būkite pirmas!
        </div>
    <?php else: ?>
        <?php foreach ($reviews as $index => $review): ?>
            <div class="card mb-3 card-red">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div>
                            <p class="mb-1">
                                <strong>Vartotojas:</strong> 
                                <span class="badge bg-light text-dark">
                                    <?php echo htmlspecialchars($review['username']); ?>
                                </span>
                            </p>
                            <p class="mb-1 text-muted">
                                <small>
                                    <?php echo date('Y-m-d H:i', strtotime($review['created_at'])); ?>
                                </small>
                            </p>
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark fs-6">
                                <?php echo $review['rating']; ?> / 10
                            </span>
                        </div>
                    </div>
                    
                    <?php if (isset($review['comment']) && !empty(trim($review['comment']))): ?>
                        <div class="mt-3 p-3 bg-light rounded">
                            <p class="mb-0 text-dark">
                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="mt-3 p-3 bg-light rounded">
                            <p class="mb-0 text-muted">
                                <em>Šis įvertinimas neturi komentaro.</em>
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <!-- Komentarų puslapiavimas -->
    <?php if ($totalReviewPages > 1): ?>
        <nav aria-label="Review pages">
            <ul class="pagination justify-content-center">
                <?php 
                // Previous page
                if ($reviewPage > 1): 
                    $prevUrl = "movie.php?id=$movie_id&page=" . ($reviewPage - 1);
                ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo $prevUrl; ?>">
                            &laquo; Ankstesnis
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php 
                // Page numbers
                $start_page = max(1, $reviewPage - 2);
                $end_page = min($totalReviewPages, $reviewPage + 2);
                
                for ($i = $start_page; $i <= $end_page; $i++): 
                    $pageUrl = "movie.php?id=$movie_id&page=" . $i;
                ?>
                    <li class="page-item <?php echo ($i == $reviewPage) ? 'active' : ''; ?>">
                        <a class="page-link" href="<?php echo $pageUrl; ?>">
                            <?php echo $i; ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php 
                // Next page
                if ($reviewPage < $totalReviewPages): 
                    $nextUrl = "movie.php?id=$movie_id&page=" . ($reviewPage + 1);
                ?>
                    <li class="page-item">
                        <a class="page-link" href="<?php echo $nextUrl; ?>">
                            Kitas &raquo;
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>

    <!-- Add review form (Modal) -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="reviewModalLabel">Pateikti įvertinimą</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="submit_review.php">
                    <div class="modal-body">
                        <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo generateCSRFToken(); ?>">
                        
                        <div class="mb-3">
                            <label for="rating" class="form-label">Įvertinimas (1–10)</label>
                            <input type="number" id="rating" name="rating" class="form-control"
                                   min="1" max="10" required>
                        </div>

                        <div class="mb-3">
                            <label for="comment" class="form-label">Komentaras</label>
                            <textarea id="comment" name="comment" rows="4" class="form-control"
                                      required placeholder="Parašykite savo nuomonę..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                        <button type="submit" class="btn btn-success">Pateikti</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

</main>

<script>
// Watchlist form handler
document.addEventListener('DOMContentLoaded', function() {
    const watchlistForm = document.getElementById('watchlistForm');
    if (watchlistForm) {
        watchlistForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
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
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update button
                    button.innerHTML = data.in_watchlist ? 'Jau sąraše' : 'Žiūrėti vėliau';
                    button.className = data.in_watchlist ? 
                        'btn btn-success w-100' : 'btn btn-primary w-100';
                    
                    // Show notification
                    showNotification(data.message, 'success');
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
                showNotification('Įvyko klaida! Bandykite dar kartą.', 'error');
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });
    }
});

function showNotification(message, type = 'success') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        max-width: 400px;
    `;
    
    notification.innerHTML = `
        <div class="d-flex align-items-center">
            <div class="me-2" style="font-size: 1.2rem;">${type === 'success' ? '✅' : '❌'}</div>
            <div class="flex-grow-1">
                ${message}
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 4 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 4000);
}
</script>

<?php
// Close database connection
$db->close();

// Include footer
include 'includes/footer.php';
?>