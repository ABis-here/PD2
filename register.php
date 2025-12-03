<?php
require_once 'includes/config.php';
require_once 'php/Database.php';

$db = new Database();
$error = '';
$success = '';

// Process registration form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $password2 = $_POST['password2'];
    
    // Validation
    if ($password !== $password2) {
        $error = 'Slaptažodžiai nesutampa';
    } elseif (strlen($password) < 6) {
        $error = 'Slaptažodis turi būti bent 6 simbolių';
    } else {
        // Check if username exists
        $existing = $db->fetchOne("SELECT id FROM users WHERE username = ?", [$username]);
        
        if ($existing) {
            $error = 'Toks vartotojo vardas jau užimtas';
        } else {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            
            // Insert user
            $db->query("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)", 
                      [$username, $email, $password_hash]);
            
            $success = 'Registracija sėkminga! Galite <a href="login.php">prisijungti</a>.';
        }
    }
}

$pageTitle = "Registracija";
include 'includes/header.php';
?>

<main class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-4">
            <div class="card p-4">
                <h2 class="mb-3 text-center">Registracija</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="username" class="form-label">Vartotojo vardas *</label>
                        <input type="text" id="username" name="username" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">El. paštas *</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Slaptažodis *</label>
                        <input type="password" id="password" name="password" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="password2" class="form-label">Pakartokite slaptažodį *</label>
                        <input type="password" id="password2" name="password2" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">Registruotis</button>
                </form>

                <p class="text-center mt-3">
                    Jau turite paskyrą?
                    <a href="login.php" class="text-decoration-none">Prisijunkite</a>
                </p>
            </div>
        </div>
    </div>
</main>

<?php
$db->close();
include 'includes/footer.php';
?>