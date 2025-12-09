<?php
require_once 'config.php';
?>
<!doctype html>
<html lang="lt">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' – Kino Duomenys' : 'Kino Duomenys'; ?></title>
    
    <meta name="description" content="Kino filmų duomenų bazė su įvertinimais ir komentarais">
    <meta name="keywords" content="kino filmai, įvertinimai, komentarai, Lietuva">
    <meta name="author" content="Augustas Baublys, Benediktas Gudžinskas">
    <meta name="robots" content="index, follow">
    <meta http-equiv="expires" content="Sat, 01 Dec 2025 23:59:59 GMT">
    
    <link href="https://fonts.googleapis.com/css2?family=Snowburst+One&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/Styles.css">
</head>
<body>
    <header class="bg-danger p-3 mb-4">
        <div class="container d-flex justify-content-between align-items-center">
            <h1 class="h3 m-0">
                <a href="index.php" class="text-white text-decoration-none">Kino Duomenys</a>
            </h1>

            <div class="d-flex gap-2">
                <?php if (isLoggedIn()): ?>
                    <div class="dropdown">
                        <button class="btn btn-outline-light dropdown-toggle" type="button"
                                id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['username']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php">Mano profilis</a></li>
                            <li><a class="dropdown-item" href="myreviews.php">Mano vertinimai</a></li>
                            <li><a class="dropdown-item" href="watchlist.php">Mano žiūrėti vėliau sąrašas</a></li>
                            <?php if (isAdmin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="admin/">Administravimas</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Atsijungti</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-light">Prisijungti</a>
                    <a href="register.php" class="btn btn-outline-light">Registruotis</a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <main class="container">