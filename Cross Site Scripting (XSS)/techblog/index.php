<?php
// index.php - Main Frontend Page

require_once 'config/database.php';
require_once 'includes/jwt.php';

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechBlog - Modern Tech Insights & Tutorials</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="header-content">
            <div class="logo">
                <span class="logo-icon">🚀</span>
                <span class="logo-text">TechBlog</span>
            </div>
            <nav class="nav-links">
                <a href="index.php" class="nav-link active">Home</a>
                <?php if ($currentUser): ?>
                    <span class="user-info">
                        <span class="user-badge <?= $currentUser['role'] === 'admin' ? 'admin-badge' : 'user-badge' ?>">
                            <?= $currentUser['role'] === 'admin' ? '👑' : '👤' ?>
                            <?= htmlspecialchars($currentUser['username']) ?>
                        </span>
                        <?php if ($currentUser['role'] === 'admin'): ?>
                            <a href="admin.php" class="nav-link">Dashboard</a>
                        <?php endif; ?>
                        <button onclick="logout()" class="btn btn-outline btn-sm">Logout</button>
                    </span>
                <?php else: ?>
                    <button onclick="showLogin()" class="btn btn-primary btn-sm">Login</button>
                    <button onclick="showRegister()" class="btn btn-outline btn-sm">Register</button>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container">
        <!-- Home View -->
        <div id="homeView">
            <div class="welcome-banner">
                <h1>Welcome to TechBlog</h1>
                <p>Discover insightful articles about technology, web security, and modern development practices</p>
            </div>
            
            <div class="content-section">
                <div class="section-header">
                    <h2>Latest Articles</h2>
                </div>
                <div id="blogPosts" class="blog-grid">
                    <div class="loading">Loading articles...</div>
                </div>
            </div>
        </div>

        <!-- Login View -->
        <div id="loginView" class="hidden">
            <div class="auth-container">
                <h2>🔐 Login to TechBlog</h2>
                <p class="auth-subtitle">Access your account to comment and engage with our community</p>
                <div id="loginAlert"></div>
                <form id="loginForm" class="auth-form">
                    <div class="form-group">
                        <label for="loginUsername">Username</label>
                        <input type="text" id="loginUsername" class="form-control" placeholder="Enter your username" required>
                    </div>
                    <div class="form-group">
                        <label for="loginPassword">Password</label>
                        <input type="password" id="loginPassword" class="form-control" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                    <p class="auth-switch">Don't have an account? <a href="#" onclick="showRegister(); return false;">Register here</a></p>
                </form>
                <button onclick="showHome()" class="btn btn-outline btn-block">Back to Home</button>
            </div>
        </div>

        <!-- Register View -->
        <div id="registerView" class="hidden">
            <div class="auth-container">
                <h2>✨ Create Account</h2>
                <p class="auth-subtitle">Join our community and start engaging with content</p>
                <div id="registerAlert"></div>
                <form id="registerForm" class="auth-form">
                    <div class="form-group">
                        <label for="registerUsername">Username</label>
                        <input type="text" id="registerUsername" class="form-control" placeholder="Choose a username" required>
                    </div>
                    <div class="form-group">
                        <label for="registerEmail">Email</label>
                        <input type="email" id="registerEmail" class="form-control" placeholder="Enter your email" required>
                    </div>
                    <div class="form-group">
                        <label for="registerPassword">Password</label>
                        <input type="password" id="registerPassword" class="form-control" placeholder="Create a password (min 6 chars)" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Create Account</button>
                    <p class="auth-switch">Already have an account? <a href="#" onclick="showLogin(); return false;">Login here</a></p>
                </form>
                <button onclick="showHome()" class="btn btn-outline btn-block">Back to Home</button>
            </div>
        </div>
    </main>

    <!-- Blog Modal -->
    <div id="blogModal" class="modal">
        <div class="modal-overlay" onclick="closeBlogModal()"></div>
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalBlogTitle"></h2>
                <button class="close-modal" onclick="closeBlogModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div id="modalBlogMeta" class="blog-meta"></div>
                <div id="modalBlogContent" class="blog-content"></div>
                
                <div class="comments-section">
                    <h3>💬 Comments</h3>
                    <div id="commentsContainer"></div>
                    <div id="commentFormContainer"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <p>&copy; 2025 TechBlog. Created for educational security demonstration purposes.</p>
        </div>
    </footer>

    <script src="assets/js/app.js"></script>
</body>
</html>
