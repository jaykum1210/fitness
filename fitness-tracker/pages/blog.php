<?php 
$pageTitle = 'Blog - Fitness Tracker';
include '../includes/header.php';

$posts = readJSON(DATA_DIR . 'blog.json');
usort($posts, function($a, $b) {
    return strtotime($b['date']) - strtotime($a['date']);
});

$selectedCategory = $_GET['category'] ?? 'all';
?>

<section class="section">
    <div class="container">
        <h1 class="section-title">📚 Fitness Blog</h1>
        <p style="text-align: center; color: var(--text-light); max-width: 700px; margin: 0 auto 3rem;">
            Expert tips, science-backed advice, and motivation to help you reach your fitness goals.
        </p>

        <div class="workout-tabs" style="margin-bottom: 2rem;">
            <a href="?category=all" class="tab-btn <?php echo $selectedCategory === 'all' ? 'active' : ''; ?>">All Posts</a>
            <a href="?category=Tips" class="tab-btn <?php echo $selectedCategory === 'Tips' ? 'active' : ''; ?>">Tips</a>
            <a href="?category=Nutrition" class="tab-btn <?php echo $selectedCategory === 'Nutrition' ? 'active' : ''; ?>">Nutrition</a>
            <a href="?category=Workouts" class="tab-btn <?php echo $selectedCategory === 'Workouts' ? 'active' : ''; ?>">Workouts</a>
            <a href="?category=Motivation" class="tab-btn <?php echo $selectedCategory === 'Motivation' ? 'active' : ''; ?>">Motivation</a>
        </div>

        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
                <?php if ($selectedCategory === 'all' || $post['category'] === $selectedCategory): ?>
                <article class="blog-card">
                    <div class="blog-header">
                        <span class="blog-category"><?php echo $post['category']; ?></span>
                        <span class="blog-date"><?php echo date('M d, Y', strtotime($post['date'])); ?></span>
                    </div>
                    <h2><?php echo htmlspecialchars($post['title']); ?></h2>
                    <div class="blog-meta">
                        <span>✍️ <?php echo $post['author']; ?></span>
                        <span>📖 <?php echo $post['readTime']; ?> min read</span>
                    </div>
                    <p class="blog-excerpt"><?php echo htmlspecialchars($post['excerpt']); ?></p>
                    <button onclick="showBlogModal(<?php echo htmlspecialchars(json_encode($post)); ?>)" class="btn btn-primary">
                        Read More
                    </button>
                </article>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div id="blogModal" class="modal">
    <div class="modal-content modal-large">
        <span class="modal-close" onclick="closeBlogModal()">&times;</span>
        <div id="blogModalContent"></div>
    </div>
</div>

<script>
function showBlogModal(post) {
    const modal = document.getElementById('blogModal');
    const content = document.getElementById('blogModalContent');
    
    content.innerHTML = `
        <article class="blog-article">
            <span class="blog-category">${post.category}</span>
            <h1>${post.title}</h1>
            <div class="blog-meta" style="margin-bottom: 2rem;">
                <span>✍️ ${post.author}</span>
                <span>📅 ${new Date(post.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}</span>
                <span>📖 ${post.readTime} min read</span>
            </div>
            <div class="blog-content">
                ${post.content}
            </div>
        </article>
    `;
    
    modal.style.display = 'flex';
}

function closeBlogModal() {
    document.getElementById('blogModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('blogModal');
    if (event.target == modal) {
        modal.style.display = 'none';
    }
}
</script>

<?php include '../includes/footer.php'; ?>