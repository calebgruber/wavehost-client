<?php
// blog.php - Blog Page
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

// Get current user if logged in
$currentUser = null;
if (isLoggedIn()) {
    $currentUser = getCurrentUser();
}

// Get page number from query string
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, $page); // Ensure page is at least 1

// Get category filter if specified
$categoryFilter = isset($_GET['category']) ? $_GET['category'] : null;

// Number of posts per page
$postsPerPage = 6;

// Calculate offset
$offset = ($page - 1) * $postsPerPage;

// Initialize database connection
$db = db();

// Build query based on filter
$params = [];
$whereClause = "WHERE status = 'published'";

if ($categoryFilter) {
    $whereClause .= " AND category = ?";
    $params[] = $categoryFilter;
}

// Count total blog posts that match the filter
$totalPosts = $db->selectOne(
    "SELECT COUNT(*) as count FROM blog_posts $whereClause",
    $params
)['count'];

// Calculate total pages
$totalPages = ceil($totalPosts / $postsPerPage);

// Adjust page if it exceeds total pages
$page = min($page, max(1, $totalPages));

// Fetch blog posts with pagination
$blogPosts = $db->select(
    "SELECT p.*, u.first_name, u.last_name 
     FROM blog_posts p 
     LEFT JOIN users u ON p.author_id = u.id 
     $whereClause
     ORDER BY p.published_at DESC 
     LIMIT $postsPerPage OFFSET $offset",
    $params
);

// Fetch categories for the filter dropdown
$categories = $db->select(
    "SELECT DISTINCT category FROM blog_posts WHERE status = 'published' ORDER BY category"
);

// Set page title
$pageTitle = 'Blog';
?>
<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php require_once __DIR__ . '/includes/loader.php'; ?>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold mb-0">WaveHost Blog</h1>
        
        <!-- Category Filter -->
        <div class="dropdown">
            <button class="btn btn-outline-primary dropdown-toggle" type="button" id="categoryDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <?php echo $categoryFilter ? 'Category: ' . $categoryFilter : 'All Categories'; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="categoryDropdown">
                <li><a class="dropdown-item <?php echo !$categoryFilter ? 'active' : ''; ?>" href="?">All Categories</a></li>
                <?php foreach ($categories as $cat): ?>
                    <li><a class="dropdown-item <?php echo $categoryFilter === $cat['category'] ? 'active' : ''; ?>" href="?category=<?php echo urlencode($cat['category']); ?>"><?php echo $cat['category']; ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    
    <p class="lead text-muted mb-5">Tips, tutorials, and updates from the WaveHost team.</p>
    
    <!-- Blog Posts -->
    <?php if (empty($blogPosts)): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i> No blog posts found. Please check back later.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($blogPosts as $post): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card bg-dark h-100">
                        <?php if (!empty($post['featured_image'])): ?>
                            <img src="<?php echo $post['featured_image']; ?>" class="card-img-top" alt="<?php echo $post['title']; ?>">
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <span class="badge bg-primary-subtle text-primary me-2"><?php echo $post['category']; ?></span>
                                <small class="text-muted"><?php echo date('M j, Y', strtotime($post['published_at'])); ?></small>
                            </div>
                            
                            <h3 class="card-title h5 mb-3"><?php echo $post['title']; ?></h3>
                            
                            <p class="card-text text-muted mb-3"><?php echo substr(strip_tags($post['excerpt']), 0, 120) . '...'; ?></p>
                            
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <img src="/assets/images/avatars/default.png" alt="Author" class="rounded-circle me-2" width="24" height="24">
                                    <small class="text-muted"><?php echo $post['first_name'] . ' ' . $post['last_name']; ?></small>
                                </div>
                                
                                <a href="/blog/post/<?php echo $post['slug']; ?>" class="btn btn-sm btn-outline-primary">Read More</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Blog pagination" class="mt-5">
                <ul class="pagination justify-content-center">
                    <!-- Previous Page -->
                    <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                        <a class="page-link bg-dark border-secondary" href="?<?php echo $categoryFilter ? 'category=' . urlencode($categoryFilter) . '&' : ''; ?>page=<?php echo $page - 1; ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                    
                    <!-- Page Numbers -->
                    <?php
                    $startPage = max(1, $page - 2);
                    $endPage = min($startPage + 4, $totalPages);
                    
                    if ($endPage - $startPage < 4 && $startPage > 1) {
                        $startPage = max(1, $endPage - 4);
                    }
                    
                    for ($i = $startPage; $i <= $endPage; $i++):
                    ?>
                        <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                            <a class="page-link bg-dark border-secondary" href="?<?php echo $categoryFilter ? 'category=' . urlencode($categoryFilter) . '&' : ''; ?>page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <!-- Next Page -->
                    <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                        <a class="page-link bg-dark border-secondary" href="?<?php echo $categoryFilter ? 'category=' . urlencode($categoryFilter) . '&' : ''; ?>page=<?php echo $page + 1; ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
    
    <!-- Newsletter Signup -->
    <div class="card bg-darker mt-5">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h3 class="mb-3">Subscribe to Our Newsletter</h3>
                    <p class="text-muted mb-0">Stay updated with the latest hosting tips, product updates, and special offers.</p>
                </div>
                
                <div class="col-lg-6">
                    <form class="row g-3">
                        <div class="col-md-8">
                            <input type="email" class="form-control bg-dark border-secondary text-white" placeholder="Enter your email" required>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">Subscribe</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>