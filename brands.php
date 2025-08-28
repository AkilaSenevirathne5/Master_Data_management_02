<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_brand'])) {
        $code = trim($_POST['code']);
        $name = trim($_POST['name']);
        $status = $_POST['status'];
        
        // Validation
        $errors = [];
        if (empty($code)) $errors[] = "Code is required";
        if (empty($name)) $errors[] = "Name is required";
        
        // Check if code exists
        $stmt = $pdo->prepare("SELECT id FROM master_brand WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Brand code already exists";
        }
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO master_brand (code, name, status) VALUES (?, ?, ?)");
            if ($stmt->execute([$code, $name, $status])) {
                $_SESSION['success'] = "Brand added successfully";
            } else {
                $_SESSION['error'] = "Failed to add brand";
            }
            header("Location: brands.php");
            exit();
        }
    }
    
    // Handle delete
    if (isset($_POST['delete_brand'])) {
        $id = $_POST['id'];
        
        // Check if brand is used in items
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM master_item WHERE brand_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $_SESSION['error'] = "Cannot delete brand. It is being used by items.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM master_brand WHERE id = ?");
            if ($stmt->execute([$id])) {
                $_SESSION['success'] = "Brand deleted successfully";
            } else {
                $_SESSION['error'] = "Failed to delete brand";
            }
        }
        header("Location: brands.php");
        exit();
    }
}

// Get all brands with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if (!empty($search)) {
    $where = " WHERE code LIKE :search OR name LIKE :search ";
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM master_brand $where");
if (!empty($search)) {
    $stmt->execute([':search' => "%$search%"]);
} else {
    $stmt->execute();
}
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$stmt = $pdo->prepare("SELECT * FROM master_brand $where ORDER BY created_at DESC LIMIT :limit OFFSET :offset");
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$brands = $stmt->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="fade-in">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-tags text-primary me-3" style="font-size: 2.5rem;"></i>
                <div>
                    <h1 class="h2 mb-0 text-dark fw-bold">Brand Management</h1>
                    <p class="text-muted mb-0">Manage your product brands and categories</p>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success fade-in">
            <i class="bi bi-check-circle me-2"></i>
            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger fade-in">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <div class="card mb-4 slide-in-left" style="animation-delay: 0.1s;">
        <div class="card-header d-flex align-items-center">
            <i class="bi bi-plus-circle text-success me-2"></i>
            <h5 class="mb-0">Add New Brand</h5>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="code" class="form-label">
                                <i class="bi bi-hash me-1"></i>Code
                            </label>
                            <input type="text" class="form-control" id="code" name="code" required 
                                   placeholder="Enter brand code">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-tag me-1"></i>Name
                            </label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                   placeholder="Enter brand name">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="status" class="form-label">
                                <i class="bi bi-circle me-1"></i>Status
                            </label>
                            <select class="form-select" id="status" name="status">
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" name="add_brand" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle me-1"></i>
                                Add Brand
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card slide-in-left" style="animation-delay: 0.2s;">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <i class="bi bi-list-ul text-primary me-2"></i>
                <h5 class="mb-0">Brand List</h5>
                <span class="badge bg-primary ms-2"><?php echo $total; ?> brands</span>
            </div>
            <form method="get" class="d-flex">
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" 
                           placeholder="Search brands..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <?php if (count($brands) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash me-1"></i>Code</th>
                                <th><i class="bi bi-tag me-1"></i>Name</th>
                                <th><i class="bi bi-circle me-1"></i>Status</th>
                                <th><i class="bi bi-calendar me-1"></i>Created At</th>
                                <th><i class="bi bi-gear me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($brands as $brand): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary"><?php echo htmlspecialchars($brand['code']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($brand['name']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $brand['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                            <i class="bi bi-<?php echo $brand['status'] == 'Active' ? 'check-circle' : 'x-circle'; ?> me-1"></i>
                                            <?php echo $brand['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar3 text-muted me-1"></i>
                                        <?php echo date('M j, Y', strtotime($brand['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="edit_brand.php?id=<?php echo $brand['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil me-1"></i>Edit
                                            </a>
                                            <form method="post" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this brand?');">
                                                <input type="hidden" name="id" value="<?php echo $brand['id']; ?>">
                                                <button type="submit" name="delete_brand" class="btn btn-sm btn-outline-danger">
                                                    <i class="bi bi-trash me-1"></i>Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page-1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                                        <i class="bi bi-chevron-left me-1"></i>Previous
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page+1; ?><?php echo !empty($search) ? '&search='.urlencode($search) : ''; ?>">
                                        Next<i class="bi bi-chevron-right ms-1"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No brands found.</p>
                    <?php if (!empty($search)): ?>
                        <p class="text-muted">Try adjusting your search criteria.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>