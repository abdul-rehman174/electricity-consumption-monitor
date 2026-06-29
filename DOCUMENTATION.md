# ECMS — Simple Guide

**ECMS (Electricity Consumption Monitoring System)** is a small website that helps a
household guess its **monthly electricity bill before WAPDA sends the real one**.

You tell it which appliances you use and for how long each day. It does the math and
shows you the estimated units (kWh) and the estimated bill in rupees — with charts.

---

## What it can do

- 🔑 **Sign up & log in** — every household has its own private account.
- 🔌 **Add appliances** — name, watts, and hours used per day (add, edit, delete).
- 💡 **Estimate units** — calculates how many units each appliance uses per month.
- 🧾 **Estimate the bill** — applies electricity prices and shows the total in PKR.
- 📊 **Dashboard** — summary numbers + a trend chart and a pie chart.
- 🔔 **Budget alert** — warns you when the estimated bill goes over your set limit.
- 🛠️ **Admin panel** — an admin can see all users and change the electricity prices.

---

## Two kinds of users

| User | What they do |
|------|--------------|
| **Household user** | Adds appliances, sees their own bill estimate and charts. |
| **Admin** | Manages all user accounts and edits the unit prices (tariff). |

---

## How the bill is worked out

**Step 1 — units per appliance (per month):**

```
Units (kWh) = Watts × Hours per day × 30 ÷ 1000
```
Example: a 1500 W AC used 6 hours a day → 1500 × 6 × 30 ÷ 1000 = **270 units**.

**Step 2 — turn units into rupees:**
Each unit has a price. The price can be the same for all units, or higher as you use
more (this is how WAPDA bills work). The app adds it all up to give the estimated bill.

> The prices can be changed any time from **Admin → Tariff** — no coding needed.

---

## How to start the app

The easiest way is XAMPP (see **WINDOWS-SETUP.md** for the full step-by-step):

1. Open the **XAMPP Control Panel** and click **Start** next to **Apache** and **MySQL**.
2. The first time only, import `database/ecms.sql` in phpMyAdmin and visit `setup.php` once.

Then open this in your browser:

### 👉 http://localhost/electricity-consumption-monitor/

(Leave Apache and MySQL running in XAMPP while you use the app.)

---

## How to log in

**Try the ready-made demo account (already has data and charts):**
```
Email:    demo@ecms.local
Password: demo123
```

**Admin account (manage users & prices):**
```
Email:    admin@ecms.local
Password: admin123
```

Or click **Register** to make your own new household account.

---

## How to use it (household user)

1. **Log in** (or register).
2. Go to **Appliances → Add Appliance**. Enter a name, its watts, and hours per day.
3. Add a few more appliances the same way.
4. Open the **Dashboard** to see your total units, estimated bill, and charts.
5. Open **Bills** to see the bill broken down per appliance.
6. If your bill goes over your budget, a **red alert** appears on the dashboard and in **Alerts**.
7. Change your budget limit any time in **Profile**.

---

## What the admin can do

- **Admin** page — see every registered user and delete accounts.
- **Tariff** page — change the price of one unit (or set up price slabs). New prices
  apply to bills straight away.

---

## Built with

PHP · MySQL (MariaDB) · Bootstrap (design) · Chart.js (charts). Runs on a normal
computer — no internet needed.

---

## Need help?

- App won't open? Make sure **both** commands above are running.
- Forgot it's there? The web address is always **http://localhost:8000/electricity-consumption-monitor/**.
- More technical details are in **README.md**.
