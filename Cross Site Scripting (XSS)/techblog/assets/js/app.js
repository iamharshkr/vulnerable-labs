// app.js - Main Frontend JavaScript (VULNERABLE)

const API_BASE = '/api'; // Adjust path based on your setup

document.addEventListener('DOMContentLoaded', function() {
    loadBlogs();
    
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
    
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', handleRegister);
    }
});

async function loadBlogs() {
    try {
        const response = await fetch(`${API_BASE}/blogs.php`);
        const data = await response.json();
        
        if (data.success) {
            renderBlogs(data.blogs);
        }
    } catch (error) {
        console.error('Error loading blogs:', error);
        document.getElementById('blogPosts').innerHTML = '<p class="error">Failed to load blogs</p>';
    }
}

function renderBlogs(blogs) {
    const container = document.getElementById('blogPosts');
    
    if (blogs.length === 0) {
        container.innerHTML = '<p>No blogs available yet.</p>';
        return;
    }
    
    container.innerHTML = blogs.map(blog => `
        <div class="blog-card" onclick="openBlog(${blog.id})">
            <div class="blog-card-header">
                <h3>${escapeHtml(blog.title)}</h3>
                <div class="blog-meta">
                    <span>📝 ${escapeHtml(blog.author_name)}</span>
                    <span>📅 ${formatDate(blog.created_at)}</span>
                    <span>💬 ${blog.comment_count} comments</span>
                </div>
            </div>
            <div class="blog-card-body">
                <p>${escapeHtml(blog.content.substring(0, 200))}...</p>
                <button class="btn btn-secondary">Read More →</button>
            </div>
        </div>
    `).join('');
}

async function openBlog(blogId) {
    try {
        const response = await fetch(`${API_BASE}/blogs.php?id=${blogId}`);
        const data = await response.json();
        
        if (data.success) {
            const blog = data.blog;
            document.getElementById('modalBlogTitle').textContent = blog.title;
            document.getElementById('modalBlogMeta').innerHTML = `
                <span>📝 By ${escapeHtml(blog.author_name)}</span>
                <span>📅 ${formatDate(blog.created_at)}</span>
            `;
            document.getElementById('modalBlogContent').innerHTML = `
                <p>${escapeHtml(blog.content)}</p>
            `;
            
            renderComments(blog.comments, blogId);
            document.getElementById('blogModal').classList.add('active');
        }
    } catch (error) {
        console.error('Error loading blog:', error);
    }
}

// VULNERABLE FUNCTION - Renders comments without sanitization
function renderComments(comments, blogId) {
    const container = document.getElementById('commentsContainer');
    
    if (comments.length === 0) {
        container.innerHTML = '<p class="no-comments">No comments yet. Be the first to comment!</p>';
    } else {
        // VULNERABLE: Direct innerHTML insertion without escaping comment_text
        container.innerHTML = comments.map(comment => `
            <div class="comment">
                <div class="comment-header">
                    <span class="comment-author">👤 ${escapeHtml(comment.author_name)}</span>
                    <span class="comment-date">${formatDate(comment.created_at)}</span>
                </div>
                <div class="comment-body">
                    ${comment.comment_text}
                </div>
            </div>
        `).join('');
    }
    
    const formContainer = document.getElementById('commentFormContainer');
    const jwt = getCookie('jwt_token');
    
    if (jwt) {
        formContainer.innerHTML = `
            <form id="commentForm" class="comment-form">
                <div class="form-group">
                    <label for="commentText">Add your comment</label>
                    <textarea id="commentText" class="form-control" rows="3" 
                        placeholder="Share your thoughts..." required></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Post Comment</button>
            </form>
        `;
        
        document.getElementById('commentForm').addEventListener('submit', (e) => addComment(e, blogId));
    } else {
        formContainer.innerHTML = `
            <div class="login-prompt">
                <p>Please <a href="#" onclick="showLogin(); return false;">login</a> to comment</p>
            </div>
        `;
    }
}

async function addComment(e, blogId) {
    e.preventDefault();
    const text = document.getElementById('commentText').value.trim();
    
    if (!text) return;
    
    try {
        const response = await fetch(`${API_BASE}/comments.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({blog_id: blogId, comment_text: text})
        });
        
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('commentText').value = '';
            openBlog(blogId);
        } else {
            alert(data.message || 'Failed to add comment');
        }
    } catch (error) {
        console.error('Error adding comment:', error);
        alert('Failed to add comment');
    }
}

async function handleLogin(e) {
    e.preventDefault();
    const username = document.getElementById('loginUsername').value;
    const password = document.getElementById('loginPassword').value;
    
    try {
        const response = await fetch(`${API_BASE}/login.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({username, password})
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            showAlert('loginAlert', data.message, 'error');
        }
    } catch (error) {
        showAlert('loginAlert', 'Login failed. Please try again.', 'error');
    }
}

async function handleRegister(e) {
    e.preventDefault();
    const username = document.getElementById('registerUsername').value;
    const email = document.getElementById('registerEmail').value;
    const password = document.getElementById('registerPassword').value;
    
    try {
        const response = await fetch(`${API_BASE}/register.php`, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({username, email, password})
        });
        
        const data = await response.json();
        
        if (data.success) {
            location.reload();
        } else {
            showAlert('registerAlert', data.message, 'error');
        }
    } catch (error) {
        showAlert('registerAlert', 'Registration failed. Please try again.', 'error');
    }
}

function logout() {
    document.cookie = 'jwt_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;';
    location.reload();
}

function showHome() {
    document.getElementById('homeView').classList.remove('hidden');
    document.getElementById('loginView').classList.add('hidden');
    document.getElementById('registerView').classList.add('hidden');
}

function showLogin() {
    document.getElementById('homeView').classList.add('hidden');
    document.getElementById('loginView').classList.remove('hidden');
    document.getElementById('registerView').classList.add('hidden');
    closeBlogModal();
}

function showRegister() {
    document.getElementById('homeView').classList.add('hidden');
    document.getElementById('loginView').classList.add('hidden');
    document.getElementById('registerView').classList.remove('hidden');
    closeBlogModal();
}

function closeBlogModal() {
    document.getElementById('blogModal').classList.remove('active');
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function showAlert(elementId, message, type) {
    const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
    document.getElementById(elementId).innerHTML = `
        <div class="alert ${alertClass}">${message}</div>
    `;
}

function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
    return null;
}
