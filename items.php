<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_item'])) {
        $code = trim($_POST['code']);
        $name = trim($_POST['name']);
        $brand_id = $_POST['brand_id'];
        $category_id = $_POST['category_id'];
        $status = $_POST['status'];
        
        // Validation
        $errors = [];
        if (empty($code)) $errors[] = "Code is required";
        if (empty($name)) $errors[] = "Name is required";
        if (empty($brand_id)) $errors[] = "Brand is required";
        if (empty($category_id)) $errors[] = "Category is required";
        
        // Check if code exists
        $stmt = $pdo->prepare("SELECT id FROM master_item WHERE code = ?");
        $stmt->execute([$code]);
        if ($stmt->rowCount() > 0) {
            $errors[] = "Item code already exists";
        }
        
        if (empty($errors)) {
            $stmt = $pdo->prepare("INSERT INTO master_item (code, name, brand_id, category_id, status) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$code, $name, $brand_id, $category_id, $status])) {
                $_SESSION['success'] = "Item added successfully";
            } else {
                $_SESSION['error'] = "Failed to add item";
            }
            header("Location: items.php");
            exit();
        }
    }
    
    // Handle delete
    if (isset($_POST['delete_item'])) {
        $id = $_POST['id'];
        
        $stmt = $pdo->prepare("DELETE FROM master_item WHERE id = ?");
        if ($stmt->execute([$id])) {
            $_SESSION['success'] = "Item deleted successfully";
        } else {
            $_SESSION['error'] = "Failed to delete item";
        }
        header("Location: items.php");
        exit();
    }
}

// Get all items with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 5;
$offset = ($page - 1) * $per_page;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$where = '';
if (!empty($search)) {
    $where = " WHERE i.code LIKE :search OR i.name LIKE :search OR b.name LIKE :search OR c.name LIKE :search ";
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM master_item i LEFT JOIN master_brand b ON i.brand_id = b.id LEFT JOIN master_category c ON i.category_id = c.id $where");
if (!empty($search)) {
    $stmt->execute([':search' => "%$search%"]);
} else {
    $stmt->execute();
}
$total = $stmt->fetchColumn();
$total_pages = ceil($total / $per_page);

$stmt = $pdo->prepare("SELECT i.*, b.name as brand_name, c.name as category_name FROM master_item i LEFT JOIN master_brand b ON i.brand_id = b.id LEFT JOIN master_category c ON i.category_id = c.id $where ORDER BY i.created_at DESC LIMIT :limit OFFSET :offset");
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$items = $stmt->fetchAll();

// Get brands and categories for dropdowns
$brands = $pdo->query("SELECT * FROM master_brand WHERE status = 'Active' ORDER BY name")->fetchAll();
$categories = $pdo->query("SELECT * FROM master_category WHERE status = 'Active' ORDER BY name")->fetchAll();
?>

<?php include 'includes/header.php'; ?>

<div class="fade-in">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-box-seam text-primary me-3" style="font-size: 2.5rem;"></i>
                <div>
                    <h1 class="h2 mb-0 text-dark fw-bold">Item Management</h1>
                    <p class="text-muted mb-0">Manage your product inventory and details</p>
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
            <h5 class="mb-0">Add New Item</h5>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="row g-3">
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="code" class="form-label">
                                <i class="bi bi-hash me-1"></i>Code
                            </label>
                            <input type="text" class="form-control" id="code" name="code" required 
                                   placeholder="Enter item code">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-box me-1"></i>Name
                            </label>
                            <input type="text" class="form-control" id="name" name="name" required 
                                   placeholder="Enter item name">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="brand_id" class="form-label">
                                <i class="bi bi-tag me-1"></i>Brand
                            </label>
                            <select class="form-select" id="brand_id" name="brand_id" required>
                                <option value="">Select Brand</option>
                                <?php foreach ($brands as $brand): ?>
                                    <option value="<?php echo $brand['id']; ?>"><?php echo htmlspecialchars($brand['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="mb-3">
                            <label for="category_id" class="form-label">
                                <i class="bi bi-collection me-1"></i>Category
                            </label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
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
                    <div class="col-md-1">
                        <div class="mb-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" name="add_item" class="btn btn-success w-100">
                                <i class="bi bi-plus-circle"></i>
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
                <h5 class="mb-0">Item List</h5>
                <span class="badge bg-primary ms-2"><?php echo $total; ?> items</span>
            </div>
            <form method="get" class="d-flex">
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-light border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" 
                           placeholder="Search items..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
        <div class="card-body">
            <?php if (count($items) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th><i class="bi bi-hash me-1"></i>Code</th>
                                <th><i class="bi bi-box me-1"></i>Name</th>
                                <th><i class="bi bi-tag me-1"></i>Brand</th>
                                <th><i class="bi bi-collection me-1"></i>Category</th>
                                <th><i class="bi bi-circle me-1"></i>Status</th>
                                <th><i class="bi bi-calendar me-1"></i>Created At</th>
                                <th><i class="bi bi-gear me-1"></i>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $item): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-primary"><?php echo htmlspecialchars($item['code']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo htmlspecialchars($item['brand_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?php echo htmlspecialchars($item['category_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $item['status'] == 'Active' ? 'success' : 'danger'; ?>">
                                            <i class="bi bi-<?php echo $item['status'] == 'Active' ? 'check-circle' : 'x-circle'; ?> me-1"></i>
                                            <?php echo $item['status']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar3 text-muted me-1"></i>
                                        <?php echo date('M j, Y', strtotime($item['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="edit_item.php?id=<?php echo $item['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-pencil me-1"></i>Edit
                                            </a>
                                            <form method="post" class="d-inline" 
                                                  onsubmit="return confirm('Are you sure you want to delete this item?');">
                                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" name="delete_item" class="btn btn-sm btn-outline-danger">
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
                    <p class="text-muted mt-2">No items found.</p>
                    <?php if (!empty($search)): ?>
                        <p class="text-muted">Try adjusting your search criteria.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
