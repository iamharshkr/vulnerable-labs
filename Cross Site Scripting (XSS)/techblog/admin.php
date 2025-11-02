<?php
require_once 'config/database.php';
require_once 'includes/jwt.php';

$currentUser = getCurrentUser();
if (!$currentUser || $currentUser['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TechBlog</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div class="logo">
                <span class="logo-icon">🚀</span>
                <span class="logo-text">TechBlog Admin</span>
            </div>
            <nav class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <span class="user-badge admin-badge">
                    👑 <?= htmlspecialchars($currentUser['username']) ?>
                </span>
                <button onclick="logout()" class="btn btn-outline btn-sm">Logout</button>
            </nav>
        </div>
    </header>

    <main class="container">
        <div class="admin-header">
            <h1>👑 Admin Dashboard</h1>
            <button onclick="showCreateBlog()" class="btn btn-primary">+ Create New Blog</button>
        </div>

        <div id="statsSection" class="stats-grid"></div>
        <div id="adminBlogsSection">
            <h2>Manage Blogs</h2>
            <div id="adminBlogsList" class="admin-blogs-list"></div>
        </div>
    </main>

    <!-- Create/Edit Blog Modal -->
    <div id="blogFormModal" class="modal">
        <div class="modal-overlay" onclick="closeBlogForm()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="blogFormTitle">Create New Blog</h2>
                <button class="close-modal" onclick="closeBlogForm()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="blogFormAlert"></div>
                <form id="blogForm">
                    <input type="hidden" id="blogId">
                    <div class="form-group">
                        <label for="blogTitle">Title</label>
                        <input type="text" id="blogTitle" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="blogContent">Content</label>
                        <textarea id="blogContent" class="form-control" rows="10" required></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Blog</button>
                        <button type="button" onclick="closeBlogForm()" class="btn btn-outline">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>
