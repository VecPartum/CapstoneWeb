# PHP Integration — Quick Start Checklist

## ✅ What's Been Done

### Backend Files Created:
- ✅ `config.php` — Database configuration
- ✅ `utils.php` — JWT tokens and utilities
- ✅ `register.php` — User signup endpoint
- ✅ `login.php` — User login endpoint
- ✅ `get_threads.php` — Fetch threads from database
- ✅ `post_thread.php` — Create new threads
- ✅ `database_setup.sql` — Database schema

### Frontend Updated:
- ✅ `script.js` — Now uses `fetch()` to call PHP endpoints
  - `handleSubmit()` → POSTs to `/register.php` or `/login.php`
  - `renderThreads()` → Fetches from `/get_threads.php`
  - `postNewThread()` → POSTs to `/post_thread.php` with auth token
- ✅ `index.html` — Fixed stylesheet path, wired New Thread button
- ✅ Added JWT token storage in browser localStorage

### Documentation:
- ✅ `PHP_INTEGRATION_GUIDE.md` — Complete setup and API reference

---

## 📋 Your Next Steps (Do This Now!)

### Step 1: Install a Local Server
Choose ONE and install:
- **XAMPP** (easiest) — https://www.apachefriends.org/
- **Laragon** (Windows only, great alternative)
- **MAMP** (Mac)

### Step 2: Move Project to Server Root
- Copy `WebsiteBago` folder to:
  - **XAMPP:** `C:\xampp\htdocs\WebsiteBago`
  - **MAMP:** `/Applications/MAMP/htdocs/WebsiteBago`
  - **Laragon:** `C:\laragon\www\WebsiteBago`

### Step 3: Start Server & Create Database
1. Start Apache and MySQL (XAMPP Control Panel / Laragon app)
2. Open **phpMyAdmin** → `http://localhost/phpmyadmin/`
3. Import `database_setup.sql`:
   - Click "New" → Create database `a4th_forum`
   - Click database → "Import"
   - Select `database_setup.sql` from your project folder
   - Click "Go"

### Step 4: Update Database Credentials (if needed)
Edit `config.php`:
```php
define('DB_HOST', 'localhost');  // Leave as is (usually correct)
define('DB_USER', 'root');       // Might be different in MAMP/Laragon
define('DB_PASS', '');           // Add password if MAMP requires one
define('DB_NAME', 'a4th_forum'); // Leave as is
```

### Step 5: Fix Admin Password (IMPORTANT!)
Run this in browser console or a PHP file to generate a hash:
```javascript
// Or create a file called generate_hash.php with this:
<?php echo password_hash('your_password', PASSWORD_BCRYPT); ?>
```

Then update the admin user in phpMyAdmin with the hash.

### Step 6: Test It Out!
Navigate to: `http://localhost/WebsiteBago/`

1. **Test signup** → Go to Login page, click "Sign Up"
2. **Test login** → Log in with credentials
3. **Test forums** → Visit forums page (should load threads)
4. **Test new thread** → Click "New Thread" button

---

## 🔗 Key Flows Now Working

### Registration Flow:
```
User fills form → JavaScript sends POST → register.php → MySQL
→ Hash password → Generate JWT → Return token → Store in localStorage
```

### Login Flow:
```
User enters credentials → login.php → Verify password
→ Generate JWT token → Return to frontend → Store in localStorage
```

### Forums Flow:
```
User visits forums → renderThreads() → Fetch from get_threads.php
→ MySQL returns threads → Display on page
```

### Post Thread Flow:
```
User clicks "New Thread" → Prompts for input → postNewThread()
→ POST with JWT token → post_thread.php verifies auth
→ Creates in MySQL → Refreshes thread list
```

---

## 🚀 Deployment (Later)

When ready for production:
1. Get a hosting plan with PHP & MySQL (Namecheap, Bluehost, etc.)
2. Upload files via FTP/SFTP
3. Update database credentials in `config.php`
4. Change `JWT_SECRET` to something random
5. Set `display_errors` to 0 in `config.php`
6. Enable HTTPS (most hosts provide free SSL)

---

## 📞 Need Help?

| Issue | Check |
|-------|-------|
| "Database connection failed" | Is MySQL running? Correct credentials in `config.php`? |
| "File not found" (404) | Are `.php` files in the right folder? |
| "Method not allowed" (405) | Check POST vs GET in fetch() calls |
| Forums not loading | Check browser console (F12) for errors |
| New Thread requires login | Feature is intentionally restricted to logged-in users |

See `PHP_INTEGRATION_GUIDE.md` for full troubleshooting.

---

## 💡 What's Available Now

### For Users:
- ✅ Create account (register.php)
- ✅ Log in (login.php)
- ✅ View all threads (get_threads.php)
- ✅ Search and filter threads by category
- ✅ Create new threads (requires login)

### For You (Developers):
- ✅ JWT authentication system
- ✅ Database schema with users, threads, posts tables
- ✅ Input validation and sanitization
- ✅ Password hashing with bcrypt
- ✅ API structure ready for more features

---

**Questions?** Read `PHP_INTEGRATION_GUIDE.md` or review the code comments in each `.php` file.

Happy coding! 🌿
