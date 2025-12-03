<?php
// Include configuration
require_once 'includes/config.php';

// Set page title
$pageTitle = "Pagrindinis";

// Include header (which has the navigation)
include 'includes/header.php';

// Include database class
require_once 'php/Database.php';
$db = new Database();
?>

<main class="container mt-4">

    <!-- TOP GREEN CARD -->
    <section class="row justify-content-center mb-4">
        <div class="col-lg-10">
            <div class="card card-green">
                <div class="card-body">
                    <h2 class="card-title">Sveiki atvykę į „Kino Duomenys"</h2>
                    <p class="card-text">
                        Čia galėsite peržiūrėti filmus, matyti jų įvertinimus ir komentarus.
                        <?php if (!isset($_SESSION['user_id'])): ?>
                            Prisijunkite, kad galėtumėte palikti savo nuomonę.
                        <?php else: ?>
                            Dabar galite palikti savo nuomonę apie filmus!
                        <?php endif; ?>
                    </p>
                    <a href="movies.php" class="btn btn-light btn-lg">
                        🎬 Peržiūrėti filmų sąrašą
                    </a>
                    
                    <!-- Add dynamic statistics -->
                    <?php
                    try {
                        $movieCount = $db->fetchOne("SELECT COUNT(*) as count FROM movies")['count'];
                        $reviewCount = $db->fetchOne("SELECT COUNT(*) as count FROM reviews")['count'];
                        
                        echo '<div class="mt-3 pt-3 border-top">';
                        echo '<p class="mb-1"><strong>Duomenų bazėje:</strong></p>';
                        echo '<p class="mb-1">Filmų: <span class="badge bg-success">' . $movieCount . '</span></p>';
                        echo '<p class="mb-0">Įvertinimų: <span class="badge bg-success">' . $reviewCount . '</span></p>';
                        echo '</div>';
                    } catch (Exception $e) {
                        // Silently handle if tables don't exist yet
                    }
                    ?>
                </div>
            </div>
        </div>
    </section>

    <!-- BOTTOM 3 RED CARDS -->
    <section class="row g-3">

        <!-- Card 1: Movies -->
        <div class="col-md-4">
            <div class="card h-100 card-red">
                <div class="card-body text-center">
                    <h5 class="card-title">Filmai</h5>
                    <p class="card-text">
                        Peržiūrėkite visus filmus ir jų įvertinimus.
                    </p>
                    <a href="movies.php" class="btn btn-outline-light w-100">
                        Eiti į filmus
                    </a>
                    
                    <?php
                    // Show latest movie
                    try {
                        $latestMovie = $db->fetchOne("SELECT title FROM movies ORDER BY id DESC LIMIT 1");
                        if ($latestMovie) {
                            echo '<div class="mt-3 pt-3 border-top border-light">';
                            echo '<p class="mb-0"><small>Naujausias filmas:<br><strong>' . htmlspecialchars($latestMovie['title']) . '</strong></small></p>';
                            echo '</div>';
                        }
                    } catch (Exception $e) {
                        // Ignore if no movies
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Card 2: User Reviews -->
        <div class="col-md-4">
            <div class="card h-100 card-red">
                <div class="card-body">
                    <h5 class="card-title">Vartotojų įvertinimai</h5>
                    <p class="card-text">
                        Matysite, ką kiti mano apie filmus — komentarai ir balai pagal 10 balų skalę.
                    </p>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <!-- If logged in -->
                        <div class="mt-3 pt-3 border-top border-light">
                            <p class="mb-2"><small>Sveikas, <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong>!</small></p>
                            <a href="submit_review.php" class="btn btn-sm btn-light w-100">
                                Pridėti įvertinimą
                            </a>
                        </div>
                    <?php else: ?>
                        <!-- If not logged in -->
                        <div class="mt-3 pt-3 border-top border-light">
                            <p class="mb-2"><small>Norite vertinti filmus?</small></p>
                            <a href="login.php" class="btn btn-sm btn-light w-100">
                                Prisijungti
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <?php
                    // Show top rated movie
                    try {
                        $topMovie = $db->fetchOne("
                            SELECT m.title, AVG(r.rating) as avg_rating 
                            FROM movies m 
                            LEFT JOIN reviews r ON m.id = r.movie_id 
                            GROUP BY m.id 
                            HAVING AVG(r.rating) IS NOT NULL 
                            ORDER BY avg_rating DESC 
                            LIMIT 1
                        ");
                        
                        if ($topMovie && $topMovie['avg_rating'] > 0) {
                            echo '<div class="mt-3 pt-3 border-top border-light">';
                            echo '<p class="mb-0"><small>Geriausiai įvertintas:<br>';
                            echo '<strong>' . htmlspecialchars($topMovie['title']) . '</strong><br>';
                            echo '<span class="text-warning">★ ' . number_format($topMovie['avg_rating'], 1) . '/10</span>';
                            echo '</small></p>';
                            echo '</div>';
                        }
                    } catch (Exception $e) {
                        // Ignore if no reviews
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Card 3: Future Plans -->
        <div class="col-md-4">
            <div class="card h-100 card-red">
                <div class="card-body">
                    <h5 class="card-title">Funkcijos</h5>
                    <p class="card-text">
                        Šios sistemos funkcijos:
                    </p>
                    
                    <ul class="list-unstyled">
                        <li class="mb-2">✓ Filmų katalogas</li>
                        <li class="mb-2">✓ Įvertinimų sistema</li>
                        <li class="mb-2">✓ Vartotojų komentarai</li>
                        <li class="mb-2">✓ Paieška ir filtravimas</li>
                        <li>✓ Ataskaitų generavimas</li>
                    </ul>
                    
                    <div class="mt-3 pt-3 border-top border-light">
                        <p class="mb-2"><small>Plėtimo planai:</small></p>
                        <ul class="list-unstyled">
                            <li><small>• Asmeniniai sąrašai</small></li>
                            <li><small>• Rekomendacijos</small></li>
                            <li><small>• Forumas</small></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </section>
    
</main>

<?php
// Close database connection
$db->close();

// Include footer
include 'includes/footer.php';
?>