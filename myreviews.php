<?php
require_once 'includes/config.php';
require_once 'php/Database.php';

$db = new Database();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Mano įvertinimai";

$user_id = $_SESSION['user_id'];

// Handle review deletion
if (isset($_GET['delete'])) {
    $review_id = intval($_GET['delete']);
    
    // Verify the review belongs to the user
    $review = $db->fetchOne("SELECT id FROM reviews WHERE id = ? AND user_id = ?", [$review_id, $user_id]);
    
    if ($review) {
        $db->query("DELETE FROM reviews WHERE id = ?", [$review_id]);
        $_SESSION['success'] = 'Įvertinimas sėkmingai pašalintas!';
    } else {
        $_SESSION['error'] = 'Įvertinimas nerastas arba neturite teisių jį pašalinti!';
    }
    
    header('Location: myreviews.php');
    exit();
}

// Handle review update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_review'])) {
    $review_id = intval($_POST['review_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    
    // Verify the review belongs to the user
    $review = $db->fetchOne("SELECT id FROM reviews WHERE id = ? AND user_id = ?", [$review_id, $user_id]);
    
    if ($review && $rating >= 1 && $rating <= 10) {
        $db->query("UPDATE reviews SET rating = ?, comment = ? WHERE id = ?", 
                  [$rating, $comment, $review_id]);
        $_SESSION['success'] = 'Įvertinimas atnaujintas sėkmingai!';
    } else {
        $_SESSION['error'] = 'Klaida atnaujinant įvertinimą!';
    }
    
    header('Location: myreviews.php');
    exit();
}

// Get user's reviews with pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get total count
$total_reviews = $db->fetchOne("
    SELECT COUNT(*) as count 
    FROM reviews 
    WHERE user_id = ?
", [$user_id])['count'];

$total_pages = ceil($total_reviews / $limit);

// Get reviews for current page
$reviews = $db->fetchAll("
    SELECT r.*, m.title, m.id as movie_id, m.release_year
    FROM reviews r
    JOIN movies m ON r.movie_id = m.id
    WHERE r.user_id = ?
    ORDER BY r.created_at DESC
    LIMIT ? OFFSET ?
", [$user_id, $limit, $offset]);

include 'includes/header.php';
?>

<main class="container mt-4">
    <!-- TOP GREEN CARD - Overview -->
    <div class="card card-green mb-4">
        <div class="card-body">
            <h2 class="card-title">Mano įvertinimai</h2>
            
            <!-- Success/Error messages -->
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            
            <p class="card-text">
                Čia galite matyti visus savo paliktus filmų įvertinimus, redaguoti juos arba pašalinti.
            </p>
            
            <!-- Statistics -->
            <?php
            $stats = $db->fetchOne("
                SELECT 
                    COUNT(*) as total,
                    AVG(rating) as avg_rating,
                    MIN(created_at) as first_review,
                    MAX(created_at) as last_review
                FROM reviews 
                WHERE user_id = ?
            ", [$user_id]);
            ?>
            
            <div class="row mt-3">
                <div class="col-md-3 col-6">
                    <div class="card bg-light text-center">
                        <div class="card-body">
                            <h6 class="card-title">Iš viso</h6>
                            <p class="display-6"><?php echo $stats['total'] ?? '0'; ?></p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card bg-light text-center">
                        <div class="card-body">
                            <h6 class="card-title">Vidutinis</h6>
                            <p class="display-6">
                                <?php echo $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : '0.0'; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card bg-light text-center">
                        <div class="card-body">
                            <h6 class="card-title">Pirmas</h6>
                            <p class="fs-5">
                                <?php echo $stats['first_review'] ? date('Y-m-d', strtotime($stats['first_review'])) : '-'; ?>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-6">
                    <div class="card bg-light text-center">
                        <div class="card-body">
                            <h6 class="card-title">Paskutinis</h6>
                            <p class="fs-5">
                                <?php echo $stats['last_review'] ? date('Y-m-d', strtotime($stats['last_review'])) : '-'; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Reviews Table -->
    <div class="card card-red mb-4">
        <div class="card-body">
            <h4 class="card-title mb-3">Visi mano įvertinimai</h4>
            
            <?php if (empty($reviews)): ?>
                <div class="text-center py-5">
                    <h5>Jūs dar neturite įvertinimų</h5>
                    <p class="mb-4">Pradėkite vertinti filmus ir jūsų įvertinimai atsiras čia!</p>
                    <a href="movies.php" class="btn btn-light me-2">Peržiūrėti filmus</a>
                    <a href="submit_review.php" class="btn btn-outline-light">Pateikti įvertinimą</a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-dark table-hover">
                        <thead>
                            <tr>
                                <th>Filmas</th>
                                <th>Įvertinimas</th>
                                <th>Komentaras</th>
                                <th>Data</th>
                                <th>Veiksmai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reviews as $review): ?>
                                <tr>
                                    <td>
                                        <a href="movie.php?id=<?php echo $review['movie_id']; ?>" 
                                           class="text-white text-decoration-none">
                                            <strong><?php echo htmlspecialchars($review['title']); ?></strong>
                                            <?php if ($review['release_year']): ?>
                                                <br><small class="text-muted">(<?php echo $review['release_year']; ?>)</small>
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning text-dark fs-6">
                                            <?php echo $review['rating']; ?>/10
                                        </span>
                                    </td>
                                    <td>
                                        <div class="comment-preview" 
                                             style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php 
                                            if (!empty($review['comment'])) {
                                                echo htmlspecialchars($review['comment']);
                                            } else {
                                                echo '<em class="text-muted">Be komentaro</em>';
                                            }
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?php echo date('Y-m-d', strtotime($review['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-light" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editModal<?php echo $review['id']; ?>">
                                                Redaguoti
                                            </button>
                                            <a href="myreviews.php?delete=<?php echo $review['id']; ?>" 
                                               class="btn btn-outline-danger"
                                               onclick="return confirm('Ar tikrai norite pašalinti šį įvertinimą?')">
                                                Šalinti
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                
                                <!-- Edit Modal for each review -->
                                <div class="modal fade" id="editModal<?php echo $review['id']; ?>" tabindex="-1" 
                                     aria-labelledby="editModalLabel<?php echo $review['id']; ?>" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-warning">
                                                <h5 class="modal-title" id="editModalLabel<?php echo $review['id']; ?>">
                                                    Redaguoti įvertinimą
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form method="POST" action="">
                                                <div class="modal-body">
                                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Filmas</label>
                                                        <input type="text" class="form-control" 
                                                               value="<?php echo htmlspecialchars($review['title']); ?>" readonly>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Įvertinimas (1-10)</label>
                                                        <input type="number" name="rating" class="form-control" 
                                                               min="1" max="10" value="<?php echo $review['rating']; ?>" required>
                                                    </div>
                                                    
                                                    <div class="mb-3">
                                                        <label class="form-label">Komentaras</label>
                                                        <textarea name="comment" rows="4" class="form-control"><?php 
                                                            echo htmlspecialchars($review['comment'] ?? ''); 
                                                        ?></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Atšaukti</button>
                                                    <button type="submit" name="update_review" class="btn btn-warning">Išsaugoti</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Review pages">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="myreviews.php?page=<?php echo $page - 1; ?>">
                                    &laquo; Ankstesnis
                                </a>
                            </li>
                            
                            <?php 
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            
                            for ($i = $start_page; $i <= $end_page; $i++): 
                            ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="myreviews.php?page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="myreviews.php?page=<?php echo $page + 1; ?>">
                                    Kitas &raquo;
                                </a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bottom Section: Rating Distribution -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card card-green">
                <div class="card-body">
                    <h4 class="card-title">Įvertinimų pasiskirstymas</h4>
                    
                    <?php
                    $rating_distribution = $db->fetchAll("
                        SELECT rating, COUNT(*) as count
                        FROM reviews
                        WHERE user_id = ?
                        GROUP BY rating
                        ORDER BY rating DESC
                    ", [$user_id]);
                    ?>
                    
                    <?php if (empty($rating_distribution)): ?>
                        <p class="text-center py-3">Nėra duomenų</p>
                    <?php else: ?>
                        <div class="mt-3">
                            <?php foreach ($rating_distribution as $dist): ?>
                                <div class="d-flex align-items-center mb-2">
                                    <div class="me-3" style="width: 50px;">
                                        <span class="badge bg-warning text-dark"><?php echo $dist['rating']; ?>/10</span>
                                    </div>
                                    <div class="progress flex-grow-1" style="height: 20px;">
                                        <?php 
                                        $percentage = ($dist['count'] / $stats['total']) * 100;
                                        ?>
                                        <div class="progress-bar bg-success" 
                                             role="progressbar" 
                                             style="width: <?php echo $percentage; ?>%"
                                             aria-valuenow="<?php echo $percentage; ?>" 
                                             aria-valuemin="0" 
                                             aria-valuemax="100">
                                            <?php echo $dist['count']; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 mb-4">
            <div class="card card-green">
                <div class="card-body">
                    <h4 class="card-title">Veiksmai</h4>
                    
                    <div class="d-grid gap-3">
                        <a href="movies.php" class="btn btn-light btn-lg text-start">
                            Peržiūrėti visus filmus
                        </a>
                        
                        <a href="watchlist.php" class="btn btn-outline-light btn-lg text-start">
                            Mano žiūrėsiu vėliau sąrašas
                        </a>
                        
                        <a href="profile.php" class="btn btn-outline-light btn-lg text-start">
                            Grįžti į profilį
                        </a>
                    </div>
                    
                    <div class="mt-4 pt-3 border-top">
                        <h5>Patarimai</h5>
                        <ul class="list-unstyled">
                            <li class="mb-2">• Įvertinimus galite redaguoti arba šalinti</li>
                            <li class="mb-2">• Jei filmas jau vertintas, galite jį atnaujinti</li>
                            <li>• Vertinimai matomi ir filmo puslapyje</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
$db->close();
include 'includes/footer.php';
?>