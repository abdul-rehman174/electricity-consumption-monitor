x# ECMS — Simple Guide

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
