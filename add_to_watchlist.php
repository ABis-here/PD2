<?php
require_once 'includes/config.php';
require_once 'php/Database.php';

$db = new Database();

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Turite prisijungti!',
        'redirect' => 'login.php'
    ]);
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['movie_id'])) {
    $movie_id = intval($_POST['movie_id']);
    $user_id = $_SESSION['user_id'];
    
    // Validate movie exists
    $movie = $db->fetchOne("SELECT id FROM movies WHERE id = ?", [$movie_id]);
    
    if (!$movie) {
        echo json_encode([
            'success' => false,
            'message' => 'Filmas nerastas!'
        ]);
        exit();
    }
    
    // Check if already in watchlist
    $existing = $db->fetchOne("SELECT id FROM watchlist WHERE movie_id = ? AND user_id = ?", 
                             [$movie_id, $user_id]);
    
    if ($existing) {
        // Remove from watchlist
        $db->query("DELETE FROM watchlist WHERE movie_id = ? AND user_id = ?", 
                  [$movie_id, $user_id]);
        
        $message = 'Filmas pašalintas iš žiūrėsiu veliau sąrašo!';
        $in_watchlist = false;
    } else {
        // Add to watchlist
        $db->query("INSERT INTO watchlist (movie_id, user_id) VALUES (?, ?)", 
                  [$movie_id, $user_id]);
        
        // Log to file
        $logMessage = date('Y-m-d H:i:s') . " - Vartotojas " . $_SESSION['user_id'] . 
                     " (" . $_SESSION['username'] . ") pridėjo filmą " . $movie_id . 
                     " į žiūrėsiu vėliau sąrašą\n";
        file_put_contents('database/watchlist_log.txt', $logMessage, FILE_APPEND);
        
        $message = 'Filmas sėkmingai pridėtas į žiūrėti vėliau sąrašą!';
        $in_watchlist = true;
    }
    
    // Get updated watchlist count for this movie
    $watchlist_count = $db->fetchOne("SELECT COUNT(*) as count FROM watchlist WHERE movie_id = ?", 
                                    [$movie_id])['count'];
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'in_watchlist' => $in_watchlist,
        'watchlist_count' => $watchlist_count
    ]);
    exit();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Netinkama užklausa!'
    ]);
    exit();
}
?>