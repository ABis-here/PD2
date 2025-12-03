<?php
require_once 'includes/config.php';
require_once 'php/Database.php';

$db = new Database();
$pageTitle = "Filmai";

// Get movies with average ratings
$movies = $db->fetchAll("
    SELECT m.*, 
           AVG(r.rating) as avg_rating,
           COUNT(r.id) as review_count
    FROM movies m
    LEFT JOIN reviews r ON m.id = r.movie_id
    GROUP BY m.id
    ORDER BY m.title
");

// Process review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isLoggedIn()) {
    $movie_id = $_POST['movie_id'];
    $rating = $_POST['rating'];
    $comment = trim($_POST['comment']);
    
    // Insert review
    $db->query("INSERT INTO reviews (movie_id, user_id, rating, comment) VALUES (?, ?, ?, ?)",
              [$movie_id, $_SESSION['user_id'], $rating, $comment]);
    
    // Refresh page
    header("Location: movies.php");
    exit();
}

include 'includes/header.php';
?>

<div class="row">
    <!-- Left menu -->
    <aside class="col-md-3 mb-3">
        <div class="list-group">
            <a href="movies.php" class="list-group-item list-group-item-action active">Filmai</a>
            <a href="actors.php" class="list-group-item list-group-item-action">Aktoriai</a>
            <a href="directors.php" class="list-group-item list-group-item-action">Režisieriai</a>
            <a href="search.php" class="list-group-item list-group-item-action">Paieška</a>
            
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                <a href="admin/add_movie.php" class="list-group-item list-group-item-action list-group-item-danger">
                    Pridėti filmą
                </a>
            <?php endif; ?>
        </div>
    </aside>

    <!-- Main content -->
    <section class="col-md-9">
        <h2 class="mb-3 text-light">Filmai</h2>
        
        <!-- Movies list -->
        <?php foreach ($movies as $movie): ?>
            <a href="movie.php?id=<?php echo $movie['id']; ?>" class="text-decoration-none text-reset">
                <div class="card mb-3">
                    <div class="card-body">
                        <h3 class="card-title">
                            <?php echo htmlspecialchars($movie['title']); ?>
                            <?php if ($movie['release_year']): ?>
                                <small class="text-muted">(<?php echo $movie['release_year']; ?>)</small>
                            <?php endif; ?>
                        </h3>
                        
                        <?php if ($movie['director']): ?>
                            <p class="card-text"><strong>Režisierius:</strong> <?php echo htmlspecialchars($movie['director']); ?></p>
                        <?php endif; ?>
                        
                        <?php if ($movie['genre']): ?>
                            <p class="card-text"><strong>Žanras:</strong> <?php echo htmlspecialchars($movie['genre']); ?></p>
                        <?php endif; ?>
                        
                        <p class="card-text"><?php echo htmlspecialchars($movie['description']); ?></p>
                        
                        <div class="d-flex justify-content-between">
                            <p class="mb-1"><strong>Vidutinis įvertinimas:</strong> 
                                <?php echo $movie['avg_rating'] ? number_format($movie['avg_rating'], 1) : 'Nėra'; ?> / 10</p>
                            <p class="mb-0"><strong>Komentarų:</strong> <?php echo $movie['review_count']; ?></p>
                        </div>
                    </div>
                </div>
            </a>
        <?php endforeach; ?>

        <!-- Review form -->
        <?php if (isLoggedIn()): ?>
        <div class="card bg-dark text-light mt-4">
            <div class="card-body">
                <h3 class="card-title mb-3">Palikti įvertinimą ir komentarą</h3>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="movie_id" class="form-label">Filmas *</label>
                        <select id="movie_id" name="movie_id" class="form-control" required>
                            <option value="">Pasirinkite filmą</option>
                            <?php foreach ($movies as $movie): ?>
                                <option value="<?php echo $movie['id']; ?>">
                                    <?php echo htmlspecialchars($movie['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="rating" class="form-label">Įvertinimas (1–10) *</label>
                        <input type="number" id="rating" name="rating" class="form-control"
                               min="1" max="10" required>
                    </div>

                    <div class="mb-3">
                        <label for="comment" class="form-label">Komentaras *</label>
                        <textarea id="comment" name="comment" rows="4" class="form-control"
                                  required placeholder="Parašykite savo nuomonę apie filmą..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">Siųsti</button>
                </form>
            </div>
        </div>
        <?php else: ?>
            <div class="alert alert-info mt-4">
                <a href="login.php" class="alert-link">Prisijunkite</a> norėdami palikti įvertinimą.
            </div>
        <?php endif; ?>
    </section>
</div>

<?php
$db->close();
include 'includes/footer.php';
?>