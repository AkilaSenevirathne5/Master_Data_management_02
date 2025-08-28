<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
requireLogin();

// Get counts for dashboard
$brand_count = $pdo->query("SELECT COUNT(*) FROM master_brand")->fetchColumn();
$category_count = $pdo->query("SELECT COUNT(*) FROM master_category")->fetchColumn();
$item_count = $pdo->query("SELECT COUNT(*) FROM master_item")->fetchColumn();
?>

<?php include 'includes/header.php'; ?>

<div class="fade-in">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-speedometer2 text-primary me-3" style="font-size: 2.5rem;"></i>
                <div>
                    <h1 class="h2 mb-0 text-dark fw-bold">Dashboard</h1>
                    <p class="text-muted mb-0">Welcome to the Master Data Management System</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-4 col-md-6">
            <div class="card text-center slide-in-left" style="animation-delay: 0.1s;">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="bi bi-tags text-white" style="font-size: 3rem; opacity: 0.8;"></i>
                    </div>
                    <h5 class="card-title">Total Brands</h5>
                    <h3 class="card-text"><?php echo $brand_count; ?></h3>
                    <a href="brands.php" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>
                        Manage Brands
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="card text-center slide-in-left" style="animation-delay: 0.2s;">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="bi bi-collection text-white" style="font-size: 3rem; opacity: 0.8;"></i>
                    </div>
                    <h5 class="card-title">Total Categories</h5>
                    <h3 class="card-text"><?php echo $category_count; ?></h3>
                    <a href="categories.php" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>
                        Manage Categories
                    </a>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4 col-md-6">
            <div class="card text-center slide-in-left" style="animation-delay: 0.3s;">
                <div class="card-body">
                    <div class="mb-3">
                        <i class="bi bi-box-seam text-white" style="font-size: 3rem; opacity: 0.8;"></i>
                    </div>
                    <h5 class="card-title">Total Items</h5>
                    <h3 class="card-text"><?php echo $item_count; ?></h3>
                    <a href="items.php" class="btn btn-light btn-sm">
                        <i class="bi bi-arrow-right me-1"></i>
                        Manage Items
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card slide-in-left" style="animation-delay: 0.4s;">
                <div class="card-header d-flex align-items-center">
                    <i class="bi bi-clock-history text-primary me-2"></i>
                    <h5 class="mb-0">Recent Items</h5>
                </div>
                <div class="card-body">
                    <?php
                    $stmt = $pdo->query("
                        SELECT i.*, b.name as brand_name, c.name as category_name 
                        FROM master_item i 
                        LEFT JOIN master_brand b ON i.brand_id = b.id 
                        LEFT JOIN master_category c ON i.category_id = c.id 
                        ORDER BY i.created_at DESC LIMIT 5
                    ");
                    $recent_items = $stmt->fetchAll();
                    ?>
                    
                    <?php if (count($recent_items) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="bi bi-hash me-1"></i>Code</th>
                                        <th><i class="bi bi-box me-1"></i>Name</th>
                                        <th><i class="bi bi-tag me-1"></i>Brand</th>
                                        <th><i class="bi bi-collection me-1"></i>Category</th>
                                        <th><i class="bi bi-circle me-1"></i>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_items as $item): ?>
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
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2">No items found.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>