// admin.js - Admin Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    loadStats();
    loadAdminBlogs();
    
    const blogForm = document.getElementById('blogForm');
    if (blogForm) {
        blogForm.addEventListener('submit', handleBlogSubmit);
    }
});

async function loadStats() {
    try {
        const response = await fetch(`${API_BASE}/blogs.php`);
        const data = await response.json();
        
        if (data.success) {
            const totalBlogs = data.blogs.length;
            const totalComments = data.blogs.reduce((sum, blog) => sum + parseInt(blog.comment_count), 0);
            
            document.getElementById('statsSection').innerHTML = `
                <div class="stat-card">
                    <div class="stat-value">${totalBlogs}</div>
                    <div class="stat-label">Total Blogs</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${totalComments}</div>
                    <div class="stat-label">Total Comments</div>
                </div>
            `;
        }
    } catch (error) {
        console.error('Error loading stats:', error);
    }
}

async function loadAdminBlogs() {
    try {
        const response = await fetch(`${API_BASE}/blogs.php`);
        const data = await response.json();
        
        if (data.success) {
            renderAdminBlogs(data.blogs);
        }
    } catch (error) {
        console.error('Error loading blogs:', error);
    }
}

function renderAdminBlogs(blogs) {
    const container = document.getElementById('adminBlogsList');
    
    container.innerHTML = blogs.map(blog => `
        <div class="admin-blog-item">
            <div class="admin-blog-info">
                <h3>${escapeHtml(blog.title)}</h3>
                <div class="blog-meta">
                    ${formatDate(blog.created_at)} • ${blog.comment_count} comments
                </div>
            </div>
            <div class="admin-blog-actions">
                <button onclick="editBlog(${blog.id})" class="btn btn-secondary btn-sm">Edit</button>
                <button onclick="deleteBlog(${blog.id})" class="btn btn-outline btn-sm">Delete</button>
            </div>
        </div>
    `).join('');
}

function showCreateBlog() {
    document.getElementById('blogFormTitle').textContent = 'Create New Blog';
    document.getElementById('blogId').value = '';
    document.getElementById('blogTitle').value = '';
    document.getElementById('blogContent').value = '';
    document.getElementById('blogFormAlert').innerHTML = '';
    document.getElementById('blogFormModal').classList.add('active');
}

async function editBlog(blogId) {
    try {
        const response = await fetch(`${API_BASE}/blogs.php?id=${blogId}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('blogFormTitle').textContent = 'Edit Blog';
            document.getElementById('blogId').value = data.blog.id;
            document.getElementById('blogTitle').value = data.blog.title;
            document.getElementById('blogContent').value = data.blog.content;
            document.getElementById('blogFormAlert').innerHTML = '';
            document.getElementById('blogFormModal').classList.add('active');
        }
    } catch (error) {
        alert('Failed to load blog');
    }
}

async function deleteBlog(blogId) {
    if (!confirm('Are you sure you want to delete this blog?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/blogs.php`, {
            method: 'DELETE',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({id: blogId})
        });
        
        const data = await response.json();
        
        if (data.success) {
            loadStats();
            loadAdminBlogs();
        } else {
            alert(data.message);
        }
    } catch (error) {
        alert('Failed to delete blog');
    }
}

async function handleBlogSubmit(e) {
    e.preventDefault();
    
    const blogId = document.getElementById('blogId').value;
    const title = document.getElementById('blogTitle').value;
    const content = document.getElementById('blogContent').value;
    
    const method = blogId ? 'PUT' : 'POST';
    const payload = blogId ? {id: parseInt(blogId), title, content} : {title, content};
    
    try {
        const response = await fetch(`${API_BASE}/blogs.php`, {
            method: method,
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        
        if (data.success) {
            closeBlogForm();
            loadStats();
            loadAdminBlogs();
        } else {
            showAlert('blogFormAlert', data.message, 'error');
        }
    } catch (error) {
        showAlert('blogFormAlert', 'Failed to save blog', 'error');
    }
}

function closeBlogForm() {
    document.getElementById('blogFormModal').classList.remove('active');
}
