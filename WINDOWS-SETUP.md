# ECMS — Windows Setup (XAMPP)

A quick guide to run the project on Windows. Takes about 10 minutes.

---

## Step 1 — Install XAMPP

1. Download XAMPP from **https://www.apachefriends.org**
2. Install it (keep the default folder `C:\xampp`).
3. Make sure **Apache** and **MySQL** are ticked during install.

> XAMPP gives you PHP, the Apache web server, and the MySQL database — all in one.

---

## Step 2 — Copy the project

Put the **`electricity-consumption-monitor`** folder inside XAMPP's web folder:

```
C:\xampp\htdocs\electricity-consumption-monitor
```

(So the file `C:\xampp\htdocs\electricity-consumption-monitor\index.php` exists.)

---

## Step 3 — Start the servers

1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache**.
3. Click **Start** next to **MySQL**.

Both should turn green.

---

## Step 4 — Set the database login

On XAMPP, MySQL uses the user **root** with **no password**. Open this file in Notepad:

```
C:\xampp\htdocs\electricity-consumption-monitor\config\db.php
```

Make these two lines look like this, then save:

```php
define('DB_USER', 'root');
define('DB_PASS', '');
```

---

## Step 5 — Create the database

1. Open **http://localhost/phpmyadmin** in your browser.
2. Click the **Import** tab at the top.
3. Click **Choose File** and pick:
   `C:\xampp\htdocs\electricity-consumption-monitor\database\ecms.sql`
4. Click **Go** (bottom). You should see a green success message.

---

## Step 6 — Create the admin account

Open this once in your browser:

```
http://localhost/electricity-consumption-monitor/setup.php
```

This creates the admin login.

---

## Step 7 — (Optional) Add demo data

Want sample appliances and charts to look at? Open this once:

```
http://localhost/electricity-consumption-monitor/seed.php
```

---

## Done! Open the app

### 👉 http://localhost/electricity-consumption-monitor/

**Logins:**

| Account | Email | Password |
|---------|-------|----------|
| Demo household | `demo@electricity-consumption-monitor.local` | `demo123` |
| Admin | `admin@electricity-consumption-monitor.local` | `admin123` |

Or click **Register** to make your own account.

---

## If something goes wrong

- **Page not found?** Check the folder is exactly `C:\xampp\htdocs\electricity-consumption-monitor`.
- **"Database connection failed"?** Make sure **MySQL** is green in XAMPP, and that
  you did Step 4 and Step 5.
- **Apache won't start?** Another app may be using port 80 (often Skype). Close it,
  or change Apache's port in XAMPP.
- **Port 3306 busy?** Another MySQL is running — stop it, then start XAMPP's MySQL.

That's it — enjoy ECMS!
