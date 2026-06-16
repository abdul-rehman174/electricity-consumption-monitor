# ECMS — Electricity Consumption Monitoring System

A localhost web app for Pakistani households to track appliances and **estimate their
monthly electricity bill** against WAPDA residential tariff slabs — before the official
bill arrives. Built per the project SRS (IEEE Std 830-1998).

**Stack:** PHP 7.4+ · MySQL 5.7+ · XAMPP (Apache) · Bootstrap 5 · Chart.js · MySQLi (prepared statements)

---

## Features

| Module | Requirements covered |
|--------|----------------------|
| Auth | Register, login, logout, sessions (FR-01–03) — passwords hashed with `password_hash()` |
| Appliances | Add / edit / delete / list with per-appliance kWh (FR-04–08) |
| Billing | WAPDA slab bill, per-appliance + slab breakdown, monthly history (FR-09–11) |
| Dashboard | Summary cards, trend chart, breakdown pie, top consumers (FR-12–14) |
| Budget alerts | Set limit, auto-alert when exceeded, alert history (FR-15–17) |
| Admin | Role-based access, view all users, delete accounts (FR-18–20) |

Security (NFR-02): prepared statements everywhere, `htmlspecialchars` on all output,
server-side role checks on admin pages, hashed passwords.

---

## Setup (XAMPP on Windows)

1. **Copy** the `electricity-consumption-monitor` folder into your XAMPP `htdocs` directory
   (e.g. `C:\xampp\htdocs\electricity-consumption-monitor`).
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. **Create the database:** open <http://localhost/phpmyadmin>, click *Import*,
   choose `database/ecms.sql`, and run it. (Or: `mysql -u root < database/ecms.sql`.)
4. **Seed the admin account:** visit <http://localhost/electricity-consumption-monitor/setup.php> once.
5. **Use the app:** go to <http://localhost/electricity-consumption-monitor/>.

### Default admin login
```
email:    admin@electricity-consumption-monitor.local
password: admin123
```
Change the password after first login, then delete `setup.php`.

> If you install under a different folder name, update `BASE_URL` in
> `includes/functions.php` and the DB credentials in `config/db.php`.

---
