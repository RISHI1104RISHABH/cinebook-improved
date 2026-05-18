# 🎬 CineBook — Movie Ticket Booking System

A full-featured movie ticket booking app built with **PHP 8.2 + MySQL**.

---

## ✨ Features

**User**
- Browse Now Showing & Upcoming movies
- Search by title, genre, language
- Interactive seat map (6 rows × 10 = 60 seats)
- Real-time price calculator
- E-ticket with unique booking code
- Booking history & cancellation

**Admin Panel**
- Dashboard stats (movies, shows, bookings, revenue)
- CRUD: Movies, Theaters, Shows
- View all bookings and users

**Technical**
- CSRF protection on all forms
- bcrypt password hashing
- Prepared statements (SQL injection safe)
- XSS prevention via `e()` helper
- Secure session cookie config
- Mobile-responsive dark UI

---

## 🚀 Local Setup (XAMPP/WAMP)

1. Copy `cinebook/` into your web root (e.g. `htdocs/cinebook`)
2. Import `sql/database.sql` into phpMyAdmin
3. Edit `includes/config.php` if your DB credentials differ
4. Visit `http://localhost/cinebook/install.php` to set admin password
5. Delete `install.php`

**Login**
- Admin: `admin@cinema.com` / `admin123` (after install.php)
- User: register at `/register.php`

---

## 🚂 Deploy to Railway

### Step 1 — Push to GitHub
```bash
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR_USERNAME/cinebook
git push -u origin main
```

### Step 2 — Create Railway project
1. Go to [railway.app](https://railway.app) → **New Project**
2. Select **Deploy from GitHub repo** → pick your repo

### Step 3 — Add MySQL
1. In your Railway project → **+ New** → **Database** → **MySQL**
2. Railway auto-injects: `MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`
3. No manual config needed — `config.php` reads these automatically

### Step 4 — Deploy
- Railway builds from the `Dockerfile` automatically
- The `docker-entrypoint.sh` waits for MySQL and runs the schema on first boot
- Visit your Railway domain — the site is live! 🎉

### Optional env vars
| Variable | Default | Description |
|----------|---------|-------------|
| `SITE_NAME` | `CineBook` | App name shown in navbar |
| `SITE_URL` | Auto-detected | Override if using a custom domain |

---

## 📁 Project Structure

```
cinebook/
├── admin/               → Admin panel (dashboard, CRUD)
├── assets/css/          → Stylesheet
├── includes/
│   ├── config.php       → DB + session (reads env vars)
│   ├── functions.php    → Helpers + CSRF
│   ├── header.php / footer.php
├── sql/database.sql     → Schema + sample data
├── uploads/             → Movie poster uploads
├── index.php            → Homepage
├── movie.php            → Movie detail + showtimes
├── booking.php          → Seat selection
├── ticket.php           → E-ticket
├── my_bookings.php      → Booking history
├── login.php / register.php / logout.php
├── install.php          → First-time admin setup
├── Dockerfile           → Railway/Docker deployment
├── docker-entrypoint.sh → Auto DB setup on boot
├── railway.toml         → Railway config
└── .htaccess            → Apache rules + security headers
```
