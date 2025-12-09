<?php
require_once 'includes/config.php';
require_once 'php/Database.php';

$db = new Database();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Mano žiūrėsiu vėliau sąrašas";

// Get user's watchlist
$watchlist = $db->fetchAll("
    SELECT m.*, w.added_at
    FROM watchlist w
    JOIN movies m ON w.movie_id = m.id
    WHERE w.user_id = ?
    ORDER BY w.added_at DESC
", [$_SESSION['user_id']]);

// Remove from watchlist
if (isset($_GET['remove'])) {
    $movie_id = intval($_GET['remove']);
    $db->query("DELETE FROM watchlist WHERE movie_id = ? AND user_id = ?", 
              [$movie_id, $_SESSION['user_id']]);
    
    $_SESSION['success'] = 'Filmas pašalintas iš sąrašo!';
    header('Location: watchlist.php');
    exit();
}

include 'includes/header.php';
?>

<div class="container mt-4">
    <h2 class="mb-4">Mano žiūrėsiu vėliau sąrašas</h2>
    
    <!-- Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
    <?php endif; ?>
    
    <?php if (empty($watchlist)): ?>
        <div class="card">
            <div class="card-body text-center">
                <h5 class="card-title">Jūsų žiūrėsiu vėliau sąrašas tuščias</h5>
                <p class="card-text">Pridėkite filmų, kuriuos planuojate žiūrėti!</p>
                <a href="movies.php" class="btn btn-primary">Peržiūrėti filmus</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($watchlist as $movie): ?>
                <div class="col-md-6 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h5 class="card-title"><?php echo htmlspecialchars($movie['title']); ?></h5>
                                    <?php if ($movie['release_year']): ?>
                                        <p class="card-text text-muted">(<?php echo $movie['release_year']; ?>)</p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <a href="watchlist.php?remove=<?php echo $movie['id']; ?>" 
                                       class="btn btn-sm btn-danger d-flex align-items-center justify-content-center"
                                       onclick="return confirm('Ar tikrai norite pašalinti šį filmą iš sąrašo?')"
                                       style="width: 32px; height: 32px; padding: 0;"
                                       title="Pašalinti iš sąrašo">
                                        <i class="bi bi-x-lg"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <?php if ($movie['director']): ?>
                                <p class="card-text"><strong>Režisierius:</strong> <?php echo htmlspecialchars($movie['director']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($movie['genre']): ?>
                                <p class="card-text"><strong>Žanras:</strong> <span class="badge bg-secondary"><?php echo htmlspecialchars($movie['genre']); ?></span></p>
                            <?php endif; ?>
                            
                            <p class="card-text"><?php echo htmlspecialchars(substr($movie['description'], 0, 100)); ?>...</p>
                            
                            <div class="mt-3">
                                <a href="movie.php?id=<?php echo $movie['id']; ?>" class="btn btn-sm btn-primary">Peržiūrėti</a>
                                <button class="btn btn-sm btn-outline-secondary" disabled>
                                    Pridėta: <?php echo date('Y-m-d', strtotime($movie['added_at'])); ?>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$db->close();
include 'includes/footer.php';
?>