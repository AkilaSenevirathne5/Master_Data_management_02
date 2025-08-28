<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Validation
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }
    
    // If no errors, try to login
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['is_admin'] = $user['is_admin'];
            
            header("Location: dashboard.php");
            exit();
        } else {
            $errors['general'] = 'Invalid email or password';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="bi bi-shield-lock text-primary" style="font-size: 4rem;"></i>
            </div>
            <h2 class="h3 text-dark fw-bold">Welcome Back</h2>
            <p class="text-muted">Sign in to your MDM System account</p>
        </div>
        
        <div class="card fade-in">
            <div class="card-body p-4">
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php echo $errors['general']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="mb-4">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                               placeholder="Enter your email">
                        <?php if (isset($errors['email'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <?php echo $errors['email']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">
                            <i class="bi bi-lock me-2"></i>Password
                        </label>
                        <input type="password" class="form-control <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                               id="password" name="password" placeholder="Enter your password">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <?php echo $errors['password']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-3 mb-3">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Sign In
                    </button>
                </form>
                
                <div class="text-center">
                    <p class="text-muted mb-0">
                        Don't have an account? 
                        <a href="register.php" class="text-decoration-none fw-bold">
                            Register here
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-shield-check me-1"></i>
                Secure login powered by MDM System
            </small>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>