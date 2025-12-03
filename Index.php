<?php
// Include configuration
require_once 'includes/config.php';

// Set page title
$pageTitle = "Pagrindinis";

// Include header
include 'includes/header.php';

// Include database class
require_once 'php/Database.php';
$db = new Database();
?>

<div class="row">
    <!-- Left column: Menu -->
    <aside class="col-md-3 mb-3">
        <div class="list-group">
            <a href="movies.php" class="list-group-item list-group-item-action">Filmai</a>
            <a href="actors.php" class="list-group-item list-group-item-action">Aktoriai</a>
            <a href="search.php" class="list-group-item list-group-item-action">Paieška</a>
            <a href="reports.php" class="list-group-item list-group-item-action">Ataskaitos</a>
        </div>
        
        <!-- Quick stats -->
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">Statistika</h5>
                <?php
                // Get statistics from database
                $movieCount = $db->fetchOne("SELECT COUNT(*) as count FROM movies")['count'];
                $reviewCount = $db->fetchOne("SELECT COUNT(*) as count FROM reviews")['count'];
                $userCount = $db->fetchOne("SELECT COUNT(*) as count FROM users")['count'];
                ?>
                <p class="mb-1">Filmų: <strong><?php echo $movieCount; ?></strong></p>
                <p class="mb-1">Įvertinimų: <strong><?php echo $reviewCount; ?></strong></p>
                <p class="mb-0">Vartotojų: <strong><?php echo $userCount; ?></strong></p>
            </div>
        </div>
    </aside>

    <!-- Right column: Content -->
    <section class="col-md-9">
        <div class="card bg-dark text-light">
            <div class="card-body">
                <h2 class="card-title">Sveiki atvykę į „Kino Duomenys"</h2>
                <p class="card-text">
                    Čia galėsite peržiūrėti filmus, matyti jų įvertinimus ir komentarus.
                    Prisijunkite, kad galėtumėte palikti savo nuomonę.
                </p>
                
                <div class="row mt-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">📊 Geriausiai įvertinti filmai</h5>
                                <?php
                                $topMovies = $db->fetchAll("
                                    SELECT m.title, AVG(r.rating) as avg_rating
                                    FROM movies m
                                    LEFT JOIN reviews r ON m.id = r.movie_id
                                    GROUP BY m.id
                                    HAVING avg_rating > 0
                                    ORDER BY avg_rating DESC
                                    LIMIT 3
                                ");
                                
                                if (!empty($topMovies)) {
                                    echo '<ul class="list-group list-group-flush">';
                                    foreach ($topMovies as $movie) {
                                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                                        echo htmlspecialchars($movie['title']);
                                        echo '<span class="badge bg-primary rounded-pill">';
                                        echo number_format($movie['avg_rating'], 1);
                                        echo '</span></li>';
                                    }
                                    echo '</ul>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">🎬 Naujausi filmai</h5>
                                <?php
                                $newMovies = $db->fetchAll("
                                    SELECT title, release_year 
                                    FROM movies 
                                    ORDER BY release_year DESC 
                                    LIMIT 3
                                ");
                                
                                if (!empty($newMovies)) {
                                    echo '<ul class="list-group list-group-flush">';
                                    foreach ($newMovies as $movie) {
                                        echo '<li class="list-group-item">';
                                        echo htmlspecialchars($movie['title']) . ' (' . $movie['release_year'] . ')';
                                        echo '</li>';
                                    }
                                    echo '</ul>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<?php
// Close database connection
$db->close();

// Include footer
include 'includes/footer.php';
?>