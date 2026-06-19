# A4th Forum — Deployment Guide

## Overview
This guide covers deploying your forum application to a production server, including database migration and configuration.

---

## Step 1: Choose a Hosting Provider

### Recommended Options:
| Provider | Best For | Cost | Database |
|----------|----------|------|----------|
| **Bluehost** | Beginners, WordPress-friendly | $2.95-$12/mo | MySQL included |
| **SiteGround** | Reliability, good support | $3.99-$7.99/mo | MySQL included |
| **HostGator** | Budget-friendly, unlimited | $2.75-$8/mo | MySQL included |
| **Kinsta** | Performance, managed WordPress | $35+/mo | MySQL included |
| **Digital Ocean** | Developers, more control | $4-$6/mo | MySQL (separate) |

**For beginners**, I recommend **SiteGround** or **Bluehost** — they handle server setup automatically.

---

## Step 2: Prepare Your Database for Production

### 2.1 Export Your Current Database (if you have test data)
If you have test data in your local database and want to migrate it:

```bash
# Using mysqldump from command line
mysqldump -u root -p a4th_forum > a4th_forum_backup.sql
```

Or via **phpMyAdmin**:
1. Go to `http://localhost/phpmyadmin/`
2. Select the `a4th_forum` database
3. Click "Export" → "Go"

### 2.2 Clean Database (RECOMMENDED for fresh deployment)
If you want to start fresh on production without test data, just use `database_setup.sql` as-is.

---

## Step 3: Update Configuration for Production

### 3.1 Create Production Config

Edit [config.php](config.php) and replace it with production credentials:

```php
<?php
// Database Configuration
define('DB_HOST', 'your-server-hostname'); // e.g., 'mysql.yourhost.com'
define('DB_USER', 'your_db_username');
define('DB_PASS', 'your_secure_password');
define('DB_NAME', 'a4th_forum');

// Secret key for JWT tokens (CHANGE THIS!)
define('JWT_SECRET', 'generate-a-random-secure-string');

// Disable error reporting in production
ini_set('display_errors', 0);
error_reporting(E_ALL);
ini_set('log_errors', 1);

// Set response headers
header('Access-Control-Allow-Origin: *'); // Consider restricting this
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Content-Type: application/json');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['message' => 'Database connection failed']));
}

$conn->set_charset('utf8mb4');
?>
```

### 3.2 Generate JWT Secret
Run this in PHP to create a secure JWT secret:

```php
<?php
echo bin2hex(random_bytes(32));
?>
```

Replace `'your-secret-key-change-in-production'` with this output.

---

## Step 4: Deploy Your Code

### Option A: Using FTP/SFTP (Most Common)

1. **Get FTP credentials from your hosting provider**
2. **Download an FTP client:**
   - Windows: FileZilla, WinSCP
   - Mac: Transmit, Cyberduck
   - VS Code: Remote - SSH extension

3. **Connect and upload:**
   - Connect to your hosting server via FTP
   - Upload all files to the `public_html` folder (or your web root)
   - Keep the same folder structure

4. **File structure on server:**
   ```
   public_html/
   ├── index.html
   ├── config.php ← UPDATE WITH PRODUCTION CREDENTIALS
   ├── database_setup.sql
   ├── login.php
   ├── register.php
   ├── get_threads.php
   ├── post_thread.php
   ├── utils.php
   ├── script.js
   ├── style.css
   └── api/
   ```

### Option B: Using Git (More Advanced)

1. **Push your code to GitHub**
2. **SSH into your server** and clone the repository
3. **Pull updates** with a simple `git pull`

---

## Step 5: Set Up Database on Production Server

### 5.1 Access Server's phpMyAdmin or Database Management

Most hosting providers provide **cPanel** or **Plesk**:
- Open **phpMyAdmin** from your control panel
- Or use your hosting provider's database management tool

### 5.2 Create Database and User

1. **Create a new database:**
   - Name: `a4th_forum` (or your preferred name)

2. **Create a database user:**
   - Username: `a4th_user` (or your preferred username)
   - Password: Use a strong password (25+ characters with mix of letters, numbers, symbols)
   - Privileges: Select ALL on the `a4th_forum` database

3. **Save these credentials** — you'll need them for `config.php`

### 5.3 Import Database Structure

1. **Open the `a4th_forum` database in phpMyAdmin**
2. **Click "Import"**
3. **Upload `database_setup.sql`**
4. **Click "Go"**

This creates all tables and sample data automatically.

---

## Step 6: Verify Deployment

### Test Your Site

1. **Open your domain:** `https://yourdomain.com`
2. **Test registration:** Create a new account
3. **Test login:** Log in with your account
4. **Test posting:** Create a new thread
5. **Check database:** Log in to phpMyAdmin and verify data is being saved

### Common Issues & Fixes

| Issue | Solution |
|-------|----------|
| **"Database connection failed"** | Check DB credentials in config.php match your server |
| **PHP files show as text** | Server doesn't have PHP installed (contact hosting support) |
| **403 Forbidden error** | Check file permissions (should be 644 for files, 755 for folders) |
| **404 on API endpoints** | Verify `.php` files are in the web root, not in subfolders |
| **CORS errors in console** | Check `Access-Control-Allow-Origin` header in config.php |

---

## Step 7: Post-Deployment Security

### Essential Steps

1. **Remove test/sample data** (optional)
   - Delete the sample admin user and threads from phpMyAdmin if desired

2. **Disable directory listing** — Create `.htaccess` in root:
   ```
   Options -Indexes
   ```

3. **Enable HTTPS**
   - Most hosts provide free SSL via Let's Encrypt
   - Redirect HTTP to HTTPS in `.htaccess`:
   ```
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

4. **Set proper file permissions** via FTP:
   - PHP files: `644`
   - Folders: `755`
   - `config.php`: `600` (most secure)

5. **Restrict CORS** — Update `config.php`:
   ```php
   header('Access-Control-Allow-Origin: https://yourdomain.com');
   ```

6. **Hide sensitive files** — Add to `.htaccess`:
   ```
   <Files "config.php">
       Order allow,deny
       Deny from all
   </Files>
   ```

---

## Step 8: Maintenance & Backups

### Regular Backups

Set up automatic backups (most hosts provide this in cPanel):
1. Export database monthly
2. Backup code files

### Update PHP (if needed)
- Most hosts default to current PHP versions
- Ask hosting provider about PHP version updates

### Monitor Activity
- Check server error logs in cPanel if issues arise
- Use phpMyAdmin to verify database integrity

---

## Deployment Checklist

- [ ] Choose hosting provider
- [ ] Export/prepare database
- [ ] Update `config.php` with production credentials
- [ ] Generate JWT secret
- [ ] Upload files via FTP to production server
- [ ] Create database and user on server
- [ ] Import `database_setup.sql` on production
- [ ] Test registration/login/posting
- [ ] Enable HTTPS
- [ ] Configure `.htaccess` for security
- [ ] Set file permissions
- [ ] Set up regular backups
- [ ] Monitor error logs

---

## Need Help?

**Common Questions:**

- **Q: Which database credentials do I use?**
  - A: The ones you created on your production server, not your local ones.

- **Q: Can I use my local database in production?**
  - A: No — your local server isn't accessible from the internet. You must use the hosting provider's database.

- **Q: Can I deploy without changing config.php?**
  - A: No — your local credentials won't work on production. You must update it.

- **Q: How do I know if deployment worked?**
  - A: Visit your domain, create an account, create a thread, and check phpMyAdmin to confirm data saved.

---

## Alternative: Deploy to Free Tier Services

### Render / Railway / Heroku (Free/Cheap)
- More complex setup, requires Git
- Good for practice, not recommended for production sites

### Recommended for beginners: Use traditional hosting (SiteGround, Bluehost)
- Easier setup
- Better support
- More reliable for beginners
