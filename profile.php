<?php
require_once 'includes/config.php';
require_once 'php/Database.php';

$db = new Database();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = "Mano profilis";

// Get user data
$user_id = $_SESSION['user_id'];
$user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$user_id]);

// Get user statistics
$user_stats = $db->fetchOne("
    SELECT 
        COUNT(DISTINCT r.id) as total_reviews,
        COUNT(DISTINCT w.id) as total_watchlist,
        AVG(r.rating) as avg_rating,
        MIN(r.created_at) as first_review,
        MAX(r.created_at) as last_review
    FROM users u
    LEFT JOIN reviews r ON u.id = r.user_id
    LEFT JOIN watchlist w ON u.id = w.user_id
    WHERE u.id = ?
    GROUP BY u.id
", [$user_id]);

// Handle profile update
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $email = trim($_POST['email']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validate email
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Check if email exists (except for current user)
        $existing = $db->fetchOne("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $user_id]);
        
        if ($existing) {
            $error = 'Šis el. paštas jau naudojamas kito vartotojo!';
        } else {
            // Update email
            $db->query("UPDATE users SET email = ? WHERE id = ?", [$email, $user_id]);
            $_SESSION['email'] = $email;
            $success = 'El. paštas atnaujintas sėkmingai!';
        }
    } else {
        $error = 'Netinkamas el. pašto formatas!';
    }
    
    // Handle password change
    if (!empty($new_password)) {
        if (password_verify($current_password, $user['password_hash'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $new_password_hash = password_hash($new_password, PASSWORD_BCRYPT);
                    $db->query("UPDATE users SET password_hash = ? WHERE id = ?", [$new_password_hash, $user_id]);
                    $success .= ' Slaptažodis pakeistas sėkmingai!';
                } else {
                    $error = 'Naujas slaptažodis turi būti bent 6 simbolių!';
                }
            } else {
                $error = 'Nauji slaptažodžiai nesutampa!';
            }
        } else {
            $error = 'Dabartinis slaptažodis neteisingas!';
        }
    }
    
    // Refresh user data
    $user = $db->fetchOne("SELECT * FROM users WHERE id = ?", [$user_id]);
}

include 'includes/header.php';
?>

<main class="container mt-4">
    <!-- TOP GREEN CARD - Profile Overview -->
    <div class="card card-green mb-4">
        <div class="card-body">
            <h2 class="card-title">Mano profilis</h2>
            <p class="card-text">Čia galite peržiūrėti ir redaguoti savo paskyros informaciją.</p>
            
            <!-- Success/Error messages -->
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <div class="row mt-3">
                <div class="col-md-8">
                    <!-- User information -->
                    <div class="card bg-light text-dark mb-3">
                        <div class="card-body">
                            <h5 class="card-title">Asmeninė informacija</h5>
                            <table class="table">
                                <tr>
                                    <th width="30%">Vartotojo vardas:</th>
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($user['username']); ?></span>
                                        <?php if ($user['is_admin']): ?>
                                            <span class="badge bg-danger ms-2">Administratorius</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>El. paštas:</th>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                </tr>
                                <tr>
                                    <th>Paskyra sukurta:</th>
                                    <td><?php echo date('Y-m-d H:i', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Paskyros tipas:</th>
                                    <td>
                                        <?php if ($user['is_admin']): ?>
                                            <span class="text-danger">Administratorius</span>
                                        <?php else: ?>
                                            <span class="text-success">Įprastas vartotojas</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <!-- Quick statistics -->
                    <div class="card bg-dark text-white">
                        <div class="card-body">
                            <h5 class="card-title">Statistika</h5>
                            
                            <?php if ($user_stats): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Įvertinimų:</span>
                                    <span class="badge bg-success"><?php echo $user_stats['total_reviews']; ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Sąraše filmų:</span>
                                    <span class="badge bg-info"><?php echo $user_stats['total_watchlist']; ?></span>
                                </div>
                                <?php if ($user_stats['avg_rating']): ?>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Vid. įvertinimas:</span>
                                        <span class="badge bg-warning text-dark"><?php echo number_format($user_stats['avg_rating'], 1); ?>/10</span>
                                    </div>
                                <?php endif; ?>
                                <?php if ($user_stats['first_review']): ?>
                                    <div class="mt-3 pt-2 border-top">
                                        <small class="text-muted">
                                            Pirmas įvertinimas:<br>
                                            <?php echo date('Y-m-d', strtotime($user_stats['first_review'])); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <p class="text-center mb-0">
                                    <small>Jūs dar neturite statistinių duomenų</small><br>
                                    <a href="movies.php" class="btn btn-sm btn-primary mt-2">Pradėti vertinti</a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MIDDLE SECTION: Update Profile Form -->
    <div class="row">
        <!-- Update Profile -->
        <div class="col-md-6 mb-4">
            <div class="card card-red">
                <div class="card-body">
                    <h4 class="card-title">Redaguoti profilį</h4>
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">El. paštas</label>
                            <input type="email" name="email" class="form-control" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Dabartinis slaptažodis (tik slaptažodžiui pakeisti)</label>
                            <input type="password" name="current_password" class="form-control">
                            <small class="text-muted">Palikite tuščią, jei nenorite keisti slaptažodžio</small>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Naujas slaptažodis</label>
                            <input type="password" name="new_password" class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Pakartokite naują slaptažodį</label>
                            <input type="password" name="confirm_password" class="form-control">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-light w-100">
                            Atnaujinti profilį
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="col-md-6 mb-4">
            <div class="card card-red">
                <div class="card-body">
                    <h4 class="card-title">Greitos nuorodos</h4>
                    
                    <div class="d-grid gap-2">
                        <a href="myreviews.php" class="btn btn-outline-light btn-lg text-start">
                            Mano įvertinimai
                            <span class="badge bg-light text-dark float-end">
                                <?php echo $user_stats['total_reviews'] ?? '0'; ?>
                            </span>
                        </a>
                        
                        <a href="watchlist.php" class="btn btn-outline-light btn-lg text-start">
                            Mano žiūrėsiu vėliau
                            <span class="badge bg-light text-dark float-end">
                                <?php echo $user_stats['total_watchlist'] ?? '0'; ?>
                            </span>
                        </a>
                </div>
            </div>
        </div>
    </div>

</main>

<?php
$db->close();
include 'includes/footer.php';
?>