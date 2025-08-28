<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

// Get brand ID from URL
if (!isset($_GET['id'])) {
    header("Location: brands.php");
    exit();
}

$id = $_GET['id'];

// Get brand data
$stmt = $pdo->prepare("SELECT * FROM master_brand WHERE id = ?");
$stmt->execute([$id]);
$brand = $stmt->fetch();

if (!$brand) {
    header("Location: brands.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = trim($_POST['code']);
    $name = trim($_POST['name']);
    $status = $_POST['status'];
    
    // Validation
    $errors = [];
    if (empty($code)) $errors[] = "Code is required";
    if (empty($name)) $errors[] = "Name is required";
    
    // Check if code exists (excluding current brand)
    $stmt = $pdo->prepare("SELECT id FROM master_brand WHERE code = ? AND id != ?");
    $stmt->execute([$code, $id]);
    if ($stmt->rowCount() > 0) {
        $errors[] = "Brand code already exists";
    }
    
    if (empty($errors)) {
        $stmt = $pdo->prepare("UPDATE master_brand SET code = ?, name = ?, status = ?, updated_at = NOW() WHERE id = ?");
        if ($stmt->execute([$code, $name, $status, $id])) {
            $_SESSION['success'] = "Brand updated successfully";
            header("Location: brands.php");
            exit();
        } else {
            $errors[] = "Failed to update brand";
        }
    }
}
?>

<?php include 'includes/header.php'; ?>
<h2>Edit Brand</h2>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5>Edit Brand Details</h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <form method="post">
                    <div class="mb-3">
                        <label for="code" class="form-label">Code</label>
                        <input type="text" class="form-control" id="code" name="code" 
                               value="<?php echo htmlspecialchars($brand['code']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($brand['name']); ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-select" id="status" name="status">
                            <option value="Active" <?php echo $brand['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
                            <option value="Inactive" <?php echo $brand['status'] == 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                        </select>
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="brands.php" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Update Brand</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>