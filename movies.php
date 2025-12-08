<?php
// Include configuration
require_once 'includes/config.php';

// Include database class
require_once 'php/Database.php';
$db = new Database();

// Set page title
$pageTitle = "Filmai";

// Get search/filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$genre = isset($_GET['genre']) ? $_GET['genre'] : '';
$year_from = isset($_GET['year_from']) ? intval($_GET['year_from']) : 1900;
$year_to = isset($_GET['year_to']) ? intval($_GET['year_to']) : date('Y');

// Build SQL query with filters
$sql = "
    SELECT m.*, 
           AVG(r.rating) as avg_rating,
           COUNT(r.id) as review_count
    FROM movies m
    LEFT JOIN reviews r ON m.id = r.movie_id
    WHERE 1=1
";

$params = [];

// Add search filter
if (!empty($search)) {
    $sql .= " AND (m.title LIKE ? OR m.director LIKE ? OR m.description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
}

// Add genre filter
if (!empty($genre) && $genre != 'all') {
    $sql .= " AND m.genre = ?";
    $params[] = $genre;
}

// Add year range filter
$sql .= " AND (m.release_year IS NULL OR (m.release_year >= ? AND m.release_year <= ?))";
$params[] = $year_from;
$params[] = $year_to;

// Complete SQL
$sql .= " GROUP BY m.id ORDER BY m.title";

// Get movies from database
$movies = $db->fetchAll($sql, $params);

// Get unique genres for filter dropdown
$genres = $db->fetchAll("SELECT DISTINCT genre FROM movies WHERE genre IS NOT NULL ORDER BY genre");

// Process review submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $movie_id = intval($_POST['movie_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    
    // Validate rating
    if ($rating >= 1 && $rating <= 10) {
        // Check if user already reviewed this movie
        $existing = $db->fetchOne("SELECT id FROM reviews WHERE movie_id = ? AND user_id = ?", 
                                 [$movie_id, $_SESSION['user_id']]);
        
        if ($existing) {
            // Update existing review
            $db->query("UPDATE reviews SET rating = ?, comment = ?, created_at = NOW() WHERE id = ?", 
                      [$rating, $comment, $existing['id']]);
        } else {
            // Insert new review
            $db->query("INSERT INTO reviews (movie_id, user_id, rating, comment) VALUES (?, ?, ?, ?)",
                      [$movie_id, $_SESSION['user_id'], $rating, $comment]);
        }
        
        // Redirect to prevent form resubmission
        header("Location: movies.php");
        exit();
    }
}

include 'includes/header.php';
?>

<main class="container mt-4">
    <div class="row justify-content-center">
        <section class="col-lg-10">

            <!-- TOP GREEN INFO CARD -->
            <div class="card card-green mb-4 shadow">
                <div class="card-body">
                    <h2 class="card-title">Filmai</h2>
                    <p class="card-text mb-0">
                        Čia rasite visus pridėtus filmus, jų vidutinį įvertinimą ir komentarų kiekį.
                        Spauskite ant filmo kortelės, kad peržiūrėtumėte visus paliktus atsiliepimus.
                    </p>
                    
                    <!-- Quick stats -->
                    <div class="row mt-3">
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center p-2">
                                    <h6 class="card-title mb-0">Iš viso filmų</h6>
                                    <p class="display-6 mb-0"><?php echo count($movies); ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center p-2">
                                    <h6 class="card-title mb-0">Vid. įvertinimas</h6>
                                    <?php
                                    $avgAll = $db->fetchOne("SELECT AVG(rating) as avg FROM reviews");
                                    $avgRating = $avgAll['avg'] ? number_format($avgAll['avg'], 1) : 'Nėra';
                                    ?>
                                    <p class="display-6 mb-0"><?php echo $avgRating; ?></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card bg-light">
                                <div class="card-body text-center p-2">
                                    <h6 class="card-title mb-0">Iš viso komentarų</h6>
                                    <?php
                                    $totalReviews = $db->fetchOne("SELECT COUNT(*) as count FROM reviews")['count'];
                                    ?>
                                    <p class="display-6 mb-0"><?php echo $totalReviews; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FILTERS SECTION -->
            <div class="card mb-4">
                <div class="card-body">
                    <h4 class="card-title mb-3">Filtrai ir paieška</h4>
                    
                    <form method="GET" action="movies.php" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Paieška</label>
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Filmo pavadinimas, režisierius..." 
                                   value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Žanras</label>
                            <select name="genre" class="form-select">
                                <option value="all">Visi žanrai</option>
                                <?php foreach ($genres as $g): ?>
                                    <option value="<?php echo htmlspecialchars($g['genre']); ?>"
                                            <?php echo ($genre == $g['genre']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($g['genre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Metai (nuo–iki)</label>
                            <div class="input-group">
                                <input type="number" name="year_from" class="form-control" 
                                       placeholder="Nuo" min="1900" max="<?php echo date('Y'); ?>"
                                       value="<?php echo $year_from; ?>">
                                <span class="input-group-text">–</span>
                                <input type="number" name="year_to" class="form-control" 
                                       placeholder="Iki" min="1900" max="<?php echo date('Y'); ?>"
                                       value="<?php echo $year_to; ?>">
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">Filtruoti</button>
                            <a href="movies.php" class="btn btn-outline-secondary">Išvalyti filtrus</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- MOVIES LIST -->
            <?php if (empty($movies)): ?>
                <div class="alert alert-warning">
                    Filmų nerasta. Pabandykite kitus filtrus arba 
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <a href="admin/add_movie.php" class="alert-link">pridėkite naują filmą</a>.
                    <?php else: ?>
                        palaukite, kol administratorius pridės filmus.
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($movies as $movie): ?>
                    <a href="movie.php?id=<?php echo $movie['id']; ?>" class="text-decoration-none text-reset">
                        <div class="card mb-3 card-red shadow hover-lift">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-9">
                                        <h3 class="card-title">
                                            <?php echo htmlspecialchars($movie['title']); ?>
                                            <?php if ($movie['release_year']): ?>
                                                <small class="text-muted">(<?php echo $movie['release_year']; ?>)</small>
                                            <?php endif; ?>
                                        </h3>
                                        
                                        <?php if ($movie['director']): ?>
                                            <p class="mb-1">
                                                <strong>Režisierius:</strong> 
                                                <?php echo htmlspecialchars($movie['director']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <?php if ($movie['genre']): ?>
                                            <p class="mb-1">
                                                <strong>Žanras:</strong> 
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($movie['genre']); ?></span>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <p class="mb-2"><?php echo htmlspecialchars(substr($movie['description'], 0, 150)); ?>...</p>
                                    </div>
                                    
                                    <div class="col-md-3 text-end">
                                        <!-- Rating stars -->
                                        <?php if ($movie['avg_rating']): ?>
                                            <div class="mb-3">
                                                <div class="display-6">
                                                    <?php echo number_format($movie['avg_rating'], 1); ?>
                                                    <small class="text-muted fs-6">/10</small>
                                                </div>
                                                <div class="text-warning">
                                                    <?php
                                                    $fullStars = floor($movie['avg_rating']);
                                                    $halfStar = ($movie['avg_rating'] - $fullStars) >= 0.5;
                                                    for ($i = 0; $i < 5; $i++) {
                                                        if ($i < $fullStars) {
                                                            echo '★';
                                                        } elseif ($i == $fullStars && $halfStar) {
                                                            echo '½';
                                                        } else {
                                                            echo '☆';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="mb-3">
                                                <div class="display-6 text-muted">–</div>
                                                <small class="text-muted">Nėra įvertinimų</small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between">
                                            <span class="badge bg-info">
                                                <?php echo $movie['review_count']; ?> komentarų
                                            </span>
                                            
                                            <!-- Quick actions -->
                                            <?php if (isset($_SESSION['user_id'])): ?>
                                                <button class="btn btn-sm btn-outline-dark" 
                                                        onclick="event.preventDefault(); 
                                                        document.getElementById('quickReview<?php echo $movie['id']; ?>').classList.toggle('d-none');">
                                                    Įvertinti
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Quick review form (hidden by default) -->
                                <?php if (isset($_SESSION['user_id'])): ?>
                                <div id="quickReview<?php echo $movie['id']; ?>" class="d-none mt-3 pt-3 border-top">
                                    <form method="POST" action="movies.php" class="row g-2">
                                        <input type="hidden" name="movie_id" value="<?php echo $movie['id']; ?>">
                                        
                                        <div class="col-md-2">
                                            <input type="number" name="rating" class="form-control" 
                                                   min="1" max="10" placeholder="1-10" required>
                                        </div>
                                        
                                        <div class="col-md-8">
                                            <input type="text" name="comment" class="form-control" 
                                                   placeholder="Trumpas komentaras..." maxlength="200" required>
                                        </div>
                                        
                                        <div class="col-md-2">
                                            <button type="submit" class="btn btn-success w-100">Siųsti</button>
                                        </div>
                                    </form>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
                
                <!-- Pagination -->
                <?php
                // For now simple pagination - can be enhanced later
                $totalMovies = count($movies);
                $moviesPerPage = 10;
                $totalPages = ceil($totalMovies / $moviesPerPage);
                
                if ($totalPages > 1):
                    $currentPage = isset($_GET['page']) ? intval($_GET['page']) : 1;
                ?>
                    <nav aria-label="Movie pages">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= min($totalPages, 5); $i++): ?>
                                <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                    <a class="page-link" href="movies.php?page=<?php echo $i; 
                                        if (!empty($search)) echo '&search=' . urlencode($search);
                                        if (!empty($genre)) echo '&genre=' . urlencode($genre);
                                        echo '&year_from=' . $year_from . '&year_to=' . $year_to;
                                    ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>

            <!-- REVIEW FORM -->
            <div class="card card-green shadow mb-5">
                <div class="card-body">
                    <h3 class="card-title mb-3">Palikti įvertinimą ir komentarą</h3>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <form method="POST" action="movies.php" id="reviewForm">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Filmas *</label>
                                    <select name="movie_id" class="form-control" required>
                                        <option value="">Pasirinkite filmą</option>
                                        <?php foreach ($movies as $movie): ?>
                                            <option value="<?php echo $movie['id']; ?>">
                                                <?php echo htmlspecialchars($movie['title']); ?>
                                                <?php if ($movie['release_year']): ?>
                                                    (<?php echo $movie['release_year']; ?>)
                                                <?php endif; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Įvertinimas (1–10) *</label>
                                    <input type="number" name="rating" class="form-control" 
                                           min="1" max="10" required>
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label class="form-label">Data</label>
                                    <input type="date" class="form-control" 
                                           value="<?php echo date('Y-m-d'); ?>" disabled>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Komentaras *</label>
                                <textarea name="comment" class="form-control" rows="4" 
                                          placeholder="Parašykite savo nuomonę..." required></textarea>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <button type="submit" class="btn btn-light">Siųsti</button>
                                
                                <!-- Assignment requirement #15: AJAX preview -->
                                <button type="button" class="btn btn-outline-light" 
                                        onclick="previewReview()">
                                    Peržiūrėti
                                </button>
                            </div>
                        </form>
                        
                        <!-- Preview area -->
                        <div id="reviewPreview" class="mt-3 d-none">
                            <div class="card">
                                <div class="card-body">
                                    <h5>Jūsų įvertinimo peržiūra:</h5>
                                    <p id="previewContent"></p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <p class="mb-2">Norite palikti įvertinimą?</p>
                            <a href="login.php" class="btn btn-light">Prisijungti</a>
                            <a href="register.php" class="btn btn-outline-light">Registruotis</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </section>
    </div>
</main>
<?php
// Close database connection
$db->close();

// Include footer
include 'includes/footer.php';
?>