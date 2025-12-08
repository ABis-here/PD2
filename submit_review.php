<?php
require_once 'includes/config.php';
require_once 'php/Database.php';

$db = new Database();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'])) {
        die('CSRF validation failed');
    }
    
    $movie_id = intval($_POST['movie_id']);
    $rating = intval($_POST['rating']);
    $comment = trim($_POST['comment']);
    
    // Check if user already reviewed this movie
    $existing = $db->fetchOne("SELECT id FROM reviews WHERE movie_id = ? AND user_id = ?", 
                             [$movie_id, $_SESSION['user_id']]);
    
    if ($existing) {
        // Update existing review
        $db->query("UPDATE reviews SET rating = ?, comment = ? WHERE id = ?", 
                  [$rating, $comment, $existing['id']]);
    } else {
        // Insert new review
        $db->query("INSERT INTO reviews (movie_id, user_id, rating, comment) VALUES (?, ?, ?, ?)",
                  [$movie_id, $_SESSION['user_id'], $rating, $comment]);
    }
    
    // Redirect back to movie page
    header("Location: movie.php?id=$movie_id");
    exit();
} else {
    header('Location: index.php');
    exit();
}
?>