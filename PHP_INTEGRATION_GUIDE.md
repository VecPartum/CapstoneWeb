# A4th Forum — PHP Backend Integration Guide

## Overview
This guide explains how to set up and integrate the PHP backend with your A4th forum website.

---

## Files Created

### Backend Files
- **config.php** — Database configuration and connection setup
- **utils.php** — Utility functions (JWT tokens, sanitization, validation)
- **register.php** — User signup endpoint
- **login.php** — User login endpoint
- **get_threads.php** — Fetch forum threads (with filtering)
- **post_thread.php** — Create new forum threads (requires authentication)
- **database_setup.sql** — SQL script to create database tables

### Updated Frontend Files
- **script.js** — Modified to use PHP endpoints via fetch()
- **index.html** — Fixed stylesheet path, wired "New Thread" button

---

## Setup Instructions

### 1. **Set Up Local Server**
You need a local PHP server with MySQL. Options:
- **XAMPP** (Windows/Mac/Linux) — https://www.apachefriends.org/
- **Laragon** (Windows) — https://laragon.org/
- **MAMP** (Mac) — https://www.mamp.info/

#### For XAMPP:
1. Install XAMPP
2. Place your project folder in `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac)
3. Start Apache and MySQL from the XAMPP Control Panel
4. Access via: `http://localhost/WebsiteBago/`

### 2. **Create the Database**

#### Option A: Using phpMyAdmin (Easiest)
1. Open phpMyAdmin: `http://localhost/phpmyadmin/`
2. Create a new database named `a4th_forum`
3. Click on the database, then "Import"
4. Upload `database_setup.sql` and click "Go"

#### Option B: Using MySQL Command Line
```bash
mysql -u root -p < database_setup.sql
```

### 3. **Configure Database Connection**

Edit `config.php` with your database credentials:

```php
define('DB_HOST', 'localhost');  // Usually localhost
define('DB_USER', 'root');       // MySQL username
define('DB_PASS', '');           // MySQL password (empty by default in XAMPP)
define('DB_NAME', 'a4th_forum'); // Database name
define('JWT_SECRET', 'your-secret-key-change-in-production');
```

### 4. **Fix Admin User Password (Important!)**

The sample admin user has a placeholder password. You need to hash a real password:

```php
// Generate a password hash in PHP
echo password_hash('your_password_here', PASSWORD_BCRYPT);
```

1. Run this in a PHP file or terminal
2. Replace the password hash in `database_setup.sql`
3. Re-run the SQL import or update manually in phpMyAdmin

---

## How It Works

### User Registration
**Flow:** Frontend (HTML form) → `register.php` → Database → JWT Token

1. User fills in signup form (email, username, password)
2. JavaScript sends POST to `register.php` with JSON data
3. PHP validates and hashes password
4. User stored in database
5. JWT token generated and returned to frontend
6. Token stored in browser's `localStorage`

### User Login
**Flow:** Frontend (HTML form) → `login.php` → Database → JWT Token

1. User enters email/password
2. JavaScript sends POST to `login.php`
3. PHP finds user and verifies password with `password_verify()`
4. JWT token generated and returned
5. Token stored in `localStorage` for future authenticated requests

### Fetching Threads
**Flow:** Frontend → `get_threads.php` → Database

1. When user navigates to forums, JavaScript calls `renderThreads()`
2. JavaScript fetches from `get_threads.php`
3. Optional: supports filtering by category or search term
4. Returns JSON array of threads
5. Frontend displays threads

### Posting a New Thread
**Flow:** Frontend → `post_thread.php` → Database (Requires Auth)

1. User clicks "New Thread" button
2. Prompts for title, preview, and category
3. JavaScript retrieves token from `localStorage`
4. Sends POST to `post_thread.php` with Authorization header
5. PHP verifies token using JWT
6. If valid, thread created in database
7. Frontend refreshes thread list

---

## API Endpoints Reference

### POST `/register.php`
**Request:**
```json
{
  "username": "SpiritWatcher",
  "email": "user@example.com",
  "password": "secure123",
  "confirm_password": "secure123"
}
```

**Response (Success):**
```json
{
  "message": "Account created successfully",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "username": "SpiritWatcher",
    "email": "user@example.com"
  }
}
```

---

### POST `/login.php`
**Request:**
```json
{
  "email": "user@example.com",
  "password": "secure123"
}
```

**Response (Success):**
```json
{
  "message": "Logged in successfully",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
  "user": {
    "id": 1,
    "username": "SpiritWatcher",
    "email": "user@example.com"
  }
}
```

---

### GET `/get_threads.php`
**Optional Query Parameters:**
- `category` — Filter by category (e.g., `Dev Updates`, `Lore & Story`)
- `search` — Search in title/preview

**Example:**
```
/get_threads.php?category=Dev Updates&search=combat
```

**Response:**
```json
[
  {
    "id": 1,
    "title": "Official: Welcome to the A4th Community Forums!",
    "author": "A4th Team",
    "avatar": "🌿",
    "replies": 42,
    "views": 1204,
    "category": "Dev Updates",
    "pinned": true,
    "hot": false,
    "preview": "Hey everyone!...",
    "time": "2 days ago"
  }
]
```

---

### POST `/post_thread.php`
**Headers:**
```
Authorization: Bearer <JWT_TOKEN>
Content-Type: application/json
```

**Request:**
```json
{
  "title": "Fan art — I drew the forest spirit",
  "preview": "Here's my rough sketch of the forest guardian...",
  "category": "Fan Art"
}
```

**Response (Success):**
```json
{
  "message": "Thread created successfully",
  "threadId": 42,
  "thread": {
    "id": 42,
    "title": "Fan art — I drew the forest spirit",
    "preview": "Here's my rough sketch...",
    "category": "Fan Art"
  }
}
```

---

## Error Handling

### Common Errors

| Status Code | Meaning | Solution |
|---|---|---|
| 400 | Bad Request (missing/invalid fields) | Check form inputs |
| 401 | Unauthorized (invalid credentials) | Verify email/password or token |
| 404 | File not found | Ensure PHP files are in correct location |
| 500 | Server error | Check database connection in `config.php` |

### Example Error Response:
```json
{
  "message": "Invalid email or password"
}
```

---

## Security Best Practices

### For Production:
1. **Use HTTPS** — Always encrypt connections
2. **Change JWT Secret** — Update `JWT_SECRET` in `config.php`
3. **Use .env file** — Store credentials outside of code
4. **Disable error reporting** — Set `ini_set('display_errors', 0);` in production
5. **Rate limiting** — Prevent brute force attacks on login
6. **Input validation** — Already implemented, but add more as needed
7. **Use a real JWT library** — Consider `firebase/php-jwt` for production

### Current Security Features:
✅ Password hashing with bcrypt  
✅ JWT tokens for authentication  
✅ Input sanitization  
✅ Email validation  
✅ CORS headers  
✅ SQL prepared statements (prevents SQL injection)  

---

## Testing the Setup

### 1. Test Database Connection
Create a test file `test_connection.php`:
```php
<?php
require_once 'config.php';
echo "Connected to database: " . DB_NAME;
?>
```
Visit: `http://localhost/WebsiteBago/test_connection.php`

### 2. Test Registration
1. Go to login page
2. Click "Sign Up"
3. Fill in username, email, password
4. Submit and check for success banner

### 3. Test Login
1. Use the credentials from signup
2. Submit and check for success banner
3. Check browser console (F12) → Application → Local Storage for token

### 4. Test Forums
1. Go to forums page
2. Should load sample threads
3. Try creating a new thread (requires login)

---

## Troubleshooting

### "Database connection failed"
- Check MySQL is running (XAMPP Control Panel)
- Verify credentials in `config.php`
- Ensure database `a4th_forum` exists

### "Method not allowed" (405)
- Ensure you're using correct HTTP method (POST vs GET)
- Check frontend fetch() is sending correct method

### "Unauthorized" (401) on post thread
- Ensure logged in (check localStorage has token)
- Verify token isn't expired (7-day limit)

### "Thread list not loading"
- Check browser console for errors (F12)
- Verify `get_threads.php` is accessible
- Check database has threads

### CORS errors
- Already handled in `config.php`
- If issues persist, verify headers are being sent

---

## Next Steps (Advanced)

1. **Add profile pages** — Show user's posts and threads
2. **Add replies/comments** — Use the `posts` table
3. **Add moderation** — Delete/edit threads (admin only)
4. **Add notifications** — Email or in-app alerts
5. **Add file uploads** — For avatar/art attachments
6. **Add pagination** — For large thread lists
7. **Add caching** — Redis for performance
8. **Migrate to framework** — Consider Laravel/Symfony later

---

## Questions?

Refer to:
- PHP Documentation: https://www.php.net/docs.php
- MySQL Documentation: https://dev.mysql.com/doc/
- JWT Info: https://jwt.io/
- CORS Guide: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
