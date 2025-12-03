<?php
require_once 'includes/config.php';
require_once 'php/Database.php';

$db = new Database();

// Get movie ID from URL
$movie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get movie details
$movie = $db->fetchOne("
    SELECT m.*, 
           AVG(r.rating) as avg_rating,
           COUNT(r.id) as review_count
    FROM movies m
    LEFT JOIN reviews r ON m.id = r.movie_id
    WHERE m.id = ?
    GROUP BY m.id
", [$movie_id]);

if (!$movie) {
    // Movie not found
    header('Location: movies.php');
    exit();
}

$pageTitle = $movie['title'];
include 'includes/header.php';
?>

<!-- Back button -->
<div class="mb-3">
    <a href="movies.php" class="btn btn-outline-light">
        ← Atgal į filmų sąrašą
    </a>
</div>

<!-- Movie details -->
<div class="card mb-4">
    <div class="card-body">
        <h2 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h2>
        
        <div class="row">
            <div class="col-md-8">
                <?php if ($movie['release_year']): ?>
                    <p><strong>Metai:</strong> <?php echo $movie['release_year']; ?></p>
                <?php endif; ?>
                
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
                <div class="card bg-light">
                    <div class="card-body">
                        <h5 class="card-title">Statistika</h5>
                        <p><strong>Vidutinis įvertinimas:</strong><br>
                        <span class="display-6">
                            <?php echo $movie['avg_rating'] ? number_format($movie['avg_rating'], 1) : 'Nėra'; ?>
                        </span> / 10</p>
                        
                        <p><strong>Iš viso įvertinimų:</strong><br>
                        <span class="display-6"><?php echo $movie['review_count']; ?></span></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Actors in this movie -->
<?php
$actors = $db->fetchAll("
    SELECT a.name, a.birth_year
    FROM actors a
    JOIN movie_actors ma ON a.id = ma.actor_id
    WHERE ma.movie_id = ?
    ORDER BY a.name
", [$movie_id]);

if (!empty($actors)):
?>
<div class="card mb-4">
    <div class="card-body">
        <h4 class="card-title">Aktoriai</h4>
        <div class="row">
            <?php foreach ($actors as $actor): ?>
                <div class="col-md-4 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title"><?php echo htmlspecialchars($actor['name']); ?></h6>
                            <?php if ($actor['birth_year']): ?>
                                <p class="card-text"><small>Gimimo metai: <?php echo $actor['birth_year']; ?></small></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Reviews -->
<h3 class="text-light mb-3">Vartotojų įvertinimai ir komentarai</h3>

<?php
// Get reviews for this movie
$reviews = $db->fetchAll("
    SELECT r.*, u.username
    FROM reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.movie_id = ?
    ORDER BY r.created_at DESC
", [$movie_id]);

if (empty($reviews)):
    echo '<div class="alert alert-info">Kol kas nėra įvertinimų. Būkite pirmas!</div>';
else:
    foreach ($reviews as $review):
?>
    <div class="card mb-3">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="card-title mb-1"><?php echo htmlspecialchars($review['username']); ?></h5>
                    <p class="text-muted mb-1">
                        <small><?php echo date('Y-m-d H:i', strtotime($review['created_at'])); ?></small>
                    </p>
                </div>
                <div class="badge bg-primary rounded-pill p-2">
                    <?php echo $review['rating']; ?> / 10
                </div>
            </div>
            <p class="card-text mt-3"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
        </div>
    </div>
<?php
    endforeach;
endif;

// Add review form (if logged in)
if (isLoggedIn()):
?>
<div class="card bg-dark text-light mt-4">
    <div class="card-body">
        <h4 class="card-title mb-3">Palikite savo įvertinimą</h4>
        
        <form method="POST" action="submit_review.php">
            <input type="hidden" name="movie_id" value="<?php echo $movie_id; ?>">
            
            <div class="mb-3">
                <label for="rating" class="form-label">Įvertinimas (1–10) *</label>
                <input type="number" id="rating" name="rating" class="form-control"
                       min="1" max="10" required>
            </div>

            <div class="mb-3">
                <label for="comment" class="form-label">Komentaras *</label>
                <textarea id="comment" name="comment" rows="4" class="form-control"
                          required placeholder="Parašykite savo nuomonę..."></textarea>
            </div>

            <button type="submit" class="btn btn-success">Pateikti</button>
        </form>
    </div>
</div>
<?php else: ?>
    <div class="alert alert-info">
        <a href="login.php" class="alert-link">Prisijunkite</a> norėdami palikti įvertinimą.
    </div>
<?php endif; ?>

<?php
$db->close();
include 'includes/footer.php';
?>