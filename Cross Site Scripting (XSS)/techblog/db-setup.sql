-- TechBlog Database Setup
-- Run this file to create the database and tables

-- Create Database
CREATE DATABASE IF NOT EXISTS techblog_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE techblog_db;

-- Drop tables if they exist (for clean setup)
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS blogs;
DROP TABLE IF EXISTS users;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Blogs Table
CREATE TABLE blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    author_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_author (author_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments Table (VULNERABLE - stores unsanitized content for XSS demo)
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blog_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_blog (blog_id),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Demo Users
-- Password for both users: password123
INSERT INTO users (username, email, password, role) VALUES 
('admin', 'admin@techblog.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('sarah_tech', 'sarah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Insert Demo Blogs
INSERT INTO blogs (title, content, author_id) VALUES 
(
    'Understanding Web Security Fundamentals',
    'Web security is the cornerstone of modern application development. As developers, we must understand various attack vectors including Cross-Site Scripting (XSS), SQL Injection, Cross-Site Request Forgery (CSRF), and many others. This comprehensive guide explores each vulnerability in depth and provides practical mitigation strategies. We will examine real-world scenarios where these vulnerabilities have been exploited and discuss best practices for building secure web applications. Understanding the threat landscape is the first step toward creating robust and secure digital experiences for users worldwide.',
    1
),
(
    'Introduction to JWT Authentication',
    'JSON Web Tokens (JWT) have become the de facto standard for stateless authentication in modern web applications. A JWT consists of three parts: header, payload, and signature, each encoded in Base64. The beauty of JWT lies in its simplicity and portability - tokens can be easily passed between services and validated without database lookups. However, improper implementation can lead to serious security vulnerabilities. This article explores JWT structure, implementation best practices, token storage strategies, and common pitfalls to avoid. We will also discuss the debate between storing JWTs in localStorage versus cookies, and the security implications of each approach.',
    1
),
(
    'Building Modern RESTful APIs with PHP',
    'RESTful APIs form the backbone of modern web applications, enabling seamless communication between frontend and backend systems. PHP, despite being one of the oldest web languages, remains a powerful choice for API development. In this tutorial, we explore how to build scalable, maintainable RESTful APIs using pure PHP. We cover routing, request handling, response formatting, error handling, authentication, and rate limiting. Learn how to structure your API endpoints following REST principles, implement proper HTTP status codes, and create comprehensive API documentation. By the end of this guide, you will have a solid foundation for building production-ready APIs.',
    1
),
(
    'Database Design Best Practices',
    'A well-designed database is crucial for application performance and scalability. This article delves into normalization, indexing strategies, relationship modeling, and query optimization. We discuss when to denormalize for performance, how to choose appropriate data types, and the importance of constraints and foreign keys. Real-world examples demonstrate how proper database design can dramatically improve query performance and data integrity. Whether you are working with MySQL, PostgreSQL, or other relational databases, these principles will help you create efficient and maintainable database schemas.',
    1
),
(
    'Frontend Security: Protecting Users in the Browser',
    'Frontend security often gets overlooked, but it is just as critical as backend security. This comprehensive guide covers Content Security Policy (CSP), secure cookie handling, XSS prevention, CSRF protection, and secure data storage in the browser. We explore the risks of using innerHTML, the importance of input validation on both client and server sides, and how to implement proper authentication flows. Learn about secure coding practices that protect your users from malicious attacks and data breaches. From localStorage vulnerabilities to clickjacking prevention, we cover all aspects of frontend security.',
    1
);

-- Insert Demo Comments
INSERT INTO comments (blog_id, user_id, comment_text) VALUES 
(1, 2, 'Great article! Very informative about web security basics.'),
(1, 3, 'This helped me understand XSS vulnerabilities better. Thanks for sharing!'),
(2, 2, 'JWT authentication can be tricky. This article clarified a lot of doubts.'),
(3, 3, 'Love the practical approach to building APIs. Will implement this in my project.'),
(4, 2, 'Database design is often underestimated. This is a must-read for developers.'),
(5, 3, 'Frontend security is so important yet often neglected. Thanks for highlighting this!');

-- Verify the setup
SELECT 'Database setup completed successfully!' AS Status;
SELECT COUNT(*) AS user_count FROM users;
SELECT COUNT(*) AS blog_count FROM blogs;
SELECT COUNT(*) AS comment_count FROM comments;
