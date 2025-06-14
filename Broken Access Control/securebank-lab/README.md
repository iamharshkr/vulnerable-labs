# 🏦 SecureBank - Broken Access Control Lab

## 📋 Lab Overview

**SecureBank** is a deliberately vulnerable online banking application designed to teach penetration testers about Broken Access Control vulnerabilities. This hands-on lab simulates real-world security flaws commonly found in web applications, allowing students to practice identifying and exploiting access control weaknesses.

---

## 🎯 Learning Objectives

By completing this lab, you will learn to:

- Identify and exploit Horizontal Privilege Escalation vulnerabilities
- Understand Vertical Privilege Escalation attacks
- Discover Insecure Direct Object Reference (IDOR) flaws
- Bypass weak authentication and authorization mechanisms
- Practice session management attacks
- Use common penetration testing tools and techniques

---

## 🏗️ Lab Architecture

- **Frontend:** PHP with Tailwind CSS  
- **Backend:** PHP 8.2 with Apache  
- **Database:** MySQL 8.0  
- **Containerization:** Docker & Docker Compose  
- **Additional Tools:** PHPMyAdmin for database management

---

## 🚀 Quick Start

### Prerequisites

- Docker and Docker Compose installed
- Basic knowledge of web application security
- Familiarity with HTTP requests and browser developer tools

### Setup Instructions

```bash
# Clone or download the lab files
cd securebank_lab

# Build and start the lab environment
docker-compose up -d --build

# Access the application
# SecureBank: http://localhost:8000
# PHPMyAdmin: http://localhost:8081
```

### Test Accounts

| Username    | Password | Role     | Description                      |
|-------------|----------|----------|----------------------------------|
| admin       | password | Admin    | System administrator account     |
| john_doe    | password | Premium  | Premium customer with high balance |
| jane_smith  | password | Regular  | Regular customer account         |
| bob_wilson  | password | Regular  | Regular customer account         |

---

## 🎯 Lab Challenges

### Challenge 1: Horizontal Privilege Escalation

**Difficulty:** ⭐⭐☆☆☆ (Beginner)

**Scenario:**  
You are logged in as a regular user but want to access other users' account information.

**Questions to Answer:**

- Can you access another user's account details without their credentials?
- What URL parameter controls which user's data is displayed?
- How would you systematically enumerate all user accounts?
- What sensitive information can you extract from other users' profiles?

**Learning Goals:**

- Understanding IDOR vulnerabilities
- Parameter manipulation techniques
- Data enumeration methods

---

### Challenge 2: Vertical Privilege Escalation

**Difficulty:** ⭐⭐⭐☆☆ (Intermediate)

**Scenario:**  
As a regular user, you've discovered an admin panel but are denied access. Find ways to bypass the authorization checks.

**Questions to Answer:**

- What HTTP response code do you receive when accessing the admin panel?
- Can you identify any HTTP headers that might bypass the restriction?
- Are there any URL parameters that could override the access control?
- What administrative functions become available after successful bypass?

**Learning Goals:**

- Authorization bypass techniques
- HTTP header manipulation
- Admin panel exploitation

---

### Challenge 3: Transaction IDOR Vulnerability

**Difficulty:** ⭐⭐☆☆☆ (Beginner-Intermediate)

**Scenario:**  
The banking system allows users to view transaction details, but the implementation may have flaws.

**Questions to Answer:**

- How are transaction IDs structured in the application?
- Can you view transactions that don't belong to your account?
- What happens when you try sequential transaction IDs?
- Are there any hidden or sensitive transactions you can discover?

**Learning Goals:**

- Sequential ID enumeration
- Transaction data exposure
- Business logic flaws

---

### Challenge 4: API Access Control Bypass

**Difficulty:** ⭐⭐⭐☆☆ (Intermediate)

**Scenario:**  
The application has API endpoints that may not properly validate user permissions.
`/api/user-data.php`

**Questions to Answer:**

- What API endpoints are available in the application?
- Do the APIs properly validate user authentication?
- Can you access admin-only API functions as a regular user?
- What sensitive data can be extracted through API calls?

**Learning Goals:**

- API security testing
- Authentication bypass in APIs
- Data extraction techniques

---

### Challenge 5: Session Management Vulnerabilities

**Difficulty:** ⭐⭐⭐⭐☆ (Advanced)

**Scenario:**  
Investigate how the application manages user sessions and look for weaknesses.

**Questions to Answer:**

- How are session tokens generated and validated?
- Can you predict or forge session tokens?
- Is there a way to hijack another user's session?
- How does the application handle session expiration?

**Learning Goals:**

- Session token analysis
- Session hijacking techniques
- Predictable token generation flaws

---

### Challenge 6: Password Reset Vulnerability

**Difficulty:** ⭐⭐⭐☆☆ (Intermediate)

**Scenario:**  
The application has a password reset feature that may have security flaws. `/api/reset-password.php`

**Questions to Answer:**

- What parameters are required for password reset requests?
- Can you reset another user's password without proper authorization?
- How are password reset tokens generated and validated?
- Is there any rate limiting on password reset attempts?

**Learning Goals:**

- Password reset flow analysis
- Authorization bypass in sensitive functions
- Token security assessment

---

## 🏆 Flag Collection

Throughout the lab, you'll discover 5 hidden flags that prove successful exploitation:

- **FLAG 1:** Horizontal privilege escalation success
- **FLAG 2:** Vertical privilege escalation bypass
- **FLAG 3:** IDOR transaction discovery
- **FLAG 4:** API authorization bypass
- **FLAG 5:** Advanced session manipulation

**Flag Format:** `FLAG{descriptive_text_here}`

---

## 🛠️ Recommended Tools

### Browser-Based Tools

- Browser Developer Tools: Network tab, Console, Storage inspection
- Browser Extensions: Cookie editors, header modifiers

### Penetration Testing Tools

- Burp Suite Community: HTTP proxy and request manipulation
- OWASP ZAP: Automated vulnerability scanning
- Postman/Insomnia: API testing and manipulation
- curl: Command-line HTTP requests

### Optional Advanced Tools

- SQLMap: SQL injection testing (if applicable)
- Gobuster/Dirb: Directory and file enumeration
- Nikto: Web server vulnerability scanner

---

## 📚 Study Materials

### Before Starting

Review these concepts:

- OWASP Top 10 - Broken Access Control
- HTTP request/response structure
- Session management principles
- Authentication vs. Authorization
- REST API security basics

### Recommended Reading

- OWASP Web Security Testing Guide
- "The Web Application Hacker's Handbook" by Dafydd Stuttard
- OWASP Access Control Cheat Sheet
- CWE-22: Improper Limitation of a Pathname to a Restricted Directory

---

## 🔍 Testing Methodology

### Phase 1: Reconnaissance

- Explore the application functionality
- Map out user roles and permissions
- Identify input parameters and endpoints
- Document the application's access control model

### Phase 2: Vulnerability Discovery

- Test for horizontal privilege escalation
- Attempt vertical privilege escalation
- Look for IDOR vulnerabilities
- Analyze API security
- Examine session management

### Phase 3: Exploitation

- Develop proof-of-concept exploits
- Extract sensitive data
- Document the business impact
- Collect all flags

### Phase 4: Documentation

- Document all vulnerabilities found
- Provide step-by-step reproduction steps
- Assess the risk level of each finding
- Suggest remediation strategies

---

## 📝 Submission Requirements

For each vulnerability discovered, provide:

- **Vulnerability Title:** Clear, descriptive name
- **Risk Level:** Critical/High/Medium/Low
- **Description:** What the vulnerability is and why it exists
- **Steps to Reproduce:** Detailed exploitation steps
- **Evidence:** Screenshots, HTTP requests/responses, flags
- **Business Impact:** Real-world consequences
- **Remediation:** How to fix the vulnerability

---

## ⚠️ Important Notes

### Scope and Ethics

- This lab is for educational purposes only
- Only test within the provided lab environment
- Do not attempt these techniques on systems you don't own
- Always obtain proper authorization before testing

### Lab Environment

- The application is intentionally vulnerable
- Some security features are disabled for learning purposes
- Real banking applications have additional security layers
- This lab focuses specifically on access control flaws

### Getting Help

- Read error messages carefully—they often contain hints
- Use browser developer tools to inspect requests/responses
- Try different user accounts to understand permission differences
- Think like an attacker: what would you want to access?

---

## 🎓 Completion Criteria

To successfully complete this lab:

- Discover and exploit all 5 main vulnerabilities
- Collect all 5 hidden flags
- Document each vulnerability with proper evidence
- Understand the business impact of each flaw
- Provide realistic remediation recommendations

**Estimated Completion Time:** 2-4 hours (depending on experience level)

---

## 🔧 Troubleshooting

### Common Issues

- Can't access the application: Check if Docker containers are running
- Database connection errors: Wait for MySQL to fully initialize
- Permission denied errors: Ensure proper file permissions in Docker
- Port conflicts: Modify ports in `docker-compose.yml` if needed

### Getting Unstuck

- Review the application's functionality as a normal user first
- Pay attention to URL parameters and form fields
- Use different user accounts to compare access levels
- Check HTTP response codes and error messages for clues

---

Good luck, and happy hacking! 🚀

_Remember: The goal is to learn, understand, and ultimately help make applications more secure._
