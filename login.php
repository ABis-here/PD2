<?php
require_once 'includes/config.php';
require_once 'php/Database.php';

$db = new Database();
$error = '';

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Get user from database
    $user = $db->fetchOne("SELECT * FROM users WHERE username = ?", [$username]);
    
    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        // Assignment requirement #10: Create cookie
        if (isset($_POST['remember'])) {
            setcookie('user_login', $user['username'], time() + (86400 * 30), "/"); // 30 days
        }
        
        // Redirect to home page
        header('Location: index.php');
        exit();
    } else {
        $error = 'Neteisingas vartotojo vardas arba slaptažodis';
    }
}

$pageTitle = "Prisijungimas";
include 'includes/header.php';
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-4">
        <div class="card p-4">
            <h2 class="mb-3 text-center">Prisijungimas</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Vartotojo vardas</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Slaptažodis</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                
                <!-- Assignment requirement #10: Remember me checkbox -->
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Prisiminti mane</label>
                </div>
                
                <button type="submit" class="btn btn-danger w-100">Prisijungti</button>
            </form>
            
            <p class="text-center mt-3">
                Neturi paskyros? 
                <a href="register.php" class="text-decoration-none">Registruokis</a>
            </p>
        </div>
    </div>
</div>

<?php
$db->close();
include 'includes/footer.php';
?>