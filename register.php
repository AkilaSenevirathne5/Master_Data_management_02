<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$name = $email = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    }
    
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email';
    } else {
        // Check if email exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors['email'] = 'Email is already registered';
        }
    }
    
    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }
    
    // If no errors, register user
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$name, $email, $hashed_password])) {
            $_SESSION['success'] = 'Registration successful. Please login.';
            header("Location: login.php");
            exit();
        } else {
            $errors['general'] = 'Something went wrong. Please try again.';
        }
    }
}
?>

<?php include 'includes/header.php'; ?>

<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="text-center mb-4">
            <div class="mb-3">
                <i class="bi bi-person-plus text-primary" style="font-size: 4rem;"></i>
            </div>
            <h2 class="h3 text-dark fw-bold">Create Account</h2>
            <p class="text-muted">Join the MDM System community</p>
        </div>
        
        <div class="card fade-in">
            <div class="card-body p-4">
                <?php if (!empty($errors['general'])): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php echo $errors['general']; ?>
                    </div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="mb-4">
                        <label for="name" class="form-label">
                            <i class="bi bi-person me-2"></i>Full Name
                        </label>
                        <input type="text" class="form-control <?php echo isset($errors['name']) ? 'is-invalid' : ''; ?>" 
                               id="name" name="name" value="<?php echo htmlspecialchars($name); ?>"
                               placeholder="Enter your full name">
                        <?php if (isset($errors['name'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <?php echo $errors['name']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope me-2"></i>Email Address
                        </label>
                        <input type="email" class="form-control <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                               id="email" name="email" value="<?php echo htmlspecialchars($email); ?>"
                               placeholder="Enter your email address">
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
                               id="password" name="password" placeholder="Create a strong password">
                        <?php if (isset($errors['password'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <?php echo $errors['password']; ?>
                            </div>
                        <?php endif; ?>
                        <small class="form-text text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Password must be at least 6 characters long
                        </small>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">
                            <i class="bi bi-shield-lock me-2"></i>Confirm Password
                        </label>
                        <input type="password" class="form-control <?php echo isset($errors['confirm_password']) ? 'is-invalid' : ''; ?>" 
                               id="confirm_password" name="confirm_password" placeholder="Confirm your password">
                        <?php if (isset($errors['confirm_password'])): ?>
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <?php echo $errors['confirm_password']; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-3 mb-3">
                        <i class="bi bi-person-plus me-2"></i>
                        Create Account
                    </button>
                </form>
                
                <div class="text-center">
                    <p class="text-muted mb-0">
                        Already have an account? 
                        <a href="login.php" class="text-decoration-none fw-bold">
                            Sign in here
                        </a>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="bi bi-shield-check me-1"></i>
                Your data is protected and secure
            </small>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>