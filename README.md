<p align="center">
  <img src="./assets/images/studydesk_logo.svg" alt="StudyDesk Lite Logo" width="300">
</p>

<h1 align="center">StudyDesk</h1>

<p align="center">
  A full-stack student productivity web application designed to help manage academic activities, track studies, and maintain focus.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.1-777BB4?style=flat-square&logo=php&logoColor=white" alt="PHP 8.1">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=flat-square&logo=mysql&logoColor=white" alt="MySQL 8.0">
  <img src="https://img.shields.io/badge/JavaScript-ES6+-F7DF1E?style=flat-square&logo=javascript&logoColor=black" alt="JavaScript ES6+">
  <img src="https://img.shields.io/badge/Environment-Laragon-00B388?style=flat-square" alt="Laragon">
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square" alt="License: MIT">
</p>

---

## ✨ Features

### 👨‍🎓 Student Dashboard

| Feature | Description |
|---|---|
| 🏠 **Home Dashboard** | Real-time stats, interactive calendar, upcoming exams, and pending tasks |
| ✅ **Task Manager** | Create, update, delete, and toggle tasks. Filter by active, completed, or overdue |
| 📅 **Exam Planner** | Schedule exams with countdown timers and track study progress with topic checklists |
| ⏱️ **Pomodoro Timer** | Focus timer with custom settings, audio alarms, and automatic session tracking |
| 📝 **Notes** | Auto-saving markdown-style editor with a seamless master-detail layout |
| 👤 **Profile** | Update personal details and securely change your password |
| ❓ **Help Center** | Built-in guides and troubleshooting |

### 🛡️ Admin Panel

| Feature | Description |
|---|---|
| 📊 **Global Stats** | View platform-wide usage data (total users, tasks, exams, sessions) |
| 👥 **User Management** | Search, view read-only user data, deactivate accounts (instant kick-out), and permanently delete users |
| 🔑 **Admin Management** | Create or delete admins (with safeguards against deleting yourself or the last admin) |
| 📋 **Audit Logs** | Every destructive admin action is logged with a timestamp and target ID |

---

## 🛠️ Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.1 (PDO) |
| **Database** | MySQL 8.0 |
| **Frontend** | HTML5, CSS3, Vanilla JS (ES6+) |
| **Environment** | Laragon (Windows) |

---

## 🔒 Security Highlights

- 🛡️ **SQL Injection** — Prevented using PDO prepared statements for all queries
- 🔐 **Passwords** — Hashed with Bcrypt (`password_hash`)
- 🪙 **CSRF Protection** — All state-changing requests require a session-based CSRF token
- 🔎 **Session Guard** — A dynamic `auth_guard.php` verifies user active status on every request
- 🚦 **Rate Limiting** — Login attempts are tracked; 5 failures trigger a 10-minute lockout
- 🕐 **Timezones** — All timestamps are strictly stored in UTC and converted to local time in the UI

---

## 🚀 How to Run Locally

### Prerequisites

- [Laragon](https://laragon.org/) (or any PHP 8.1+ / MySQL 8.0+ environment)
- Web browser

---

### Step 1 — Clone the Repository

```bash
git clone https://github.com/anuraj-js/studydesk.git
cd studydesk
```

---

### Step 2 — Create the Database

You can do this via the command line, or simply import it through phpMyAdmin.

```bash
mysql -u root -p -e "CREATE DATABASE studydesk"
mysql -u root -p studydesk < database.sql
```

> **Alternatively:** Open phpMyAdmin, create a database named `studydesk`, and use the **Import** tab to upload `database.sql`.

---

### Step 3 — Configure Environment

```bash
cp .env.example .env
```

Fill in your `DB_USER` and `DB_PASS` in the `.env` file.

> **Email (optional):** To enable the "Forgot Password" feature, fill in the `MAIL_*` fields. For Gmail, you cannot use your standard password — generate a 16-character **App Password** from your Google Account security settings.

---

### Step 4 — Run Locally

**Using Laragon (Recommended):**

1. Place the `studydesk` folder inside `laragon/www/`
2. Start **Apache** and **MySQL** from the Laragon control panel
3. Visit `http://localhost/studydesk/` in your browser

**Using any LAMP/WAMP stack:**

Point your web server document root to the project folder and ensure the `BASE_URL` constant in `config/db.php` matches your local setup.

---

### Step 5 — Create an Admin Account

For security reasons, there is no Admin self-registration page. Admins are created directly in the database.

**a) Generate a bcrypt password hash:**

```bash
php -r "echo password_hash('your_strong_password', PASSWORD_BCRYPT);"
```

You will get output like:

```
$2y$10$Ss7DOKbqe/2HSKToppYiRupLCwkUM3iWSyjZhU3rJ4AFw//HhVMtu
```

Copy the entire hash (it starts with `$2y$10$`).

**b) Insert the admin account:**

Open phpMyAdmin, select the `studydesk` database, go to the **SQL** tab, and run the following query — replacing `YOUR_HASH_HERE` with the hash you generated:

```sql
INSERT INTO admins (username, email, password, full_name) 
VALUES ('admin', 'admin@example.com', '$2y$10$YOUR_HASH_HERE', 'System Admin');
```

> **Note:** The `admins` table only requires `username`, `email`, `password`, and `full_name` — not `academic_level`, `dob`, or `gender`.

**c) Log in:**

Go to `http://localhost/studydesk/`, select the **Admin** role on the login screen, and use the credentials you just created.

---

## 📁 Project Structure

```
studydesk/
│
├── api/
│   ├── admin/
│   │   ├── admins.php
│   │   ├── home.php
│   │   ├── profile.php
│   │   ├── users.php
│   │   └── view_user.php
│   │
│   ├── auth/
│   │   ├── forgot_password.php
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── register.php
│   │   └── reset_password.php
│   │
│   ├── exams.php
│   ├── home.php
│   ├── notes.php
│   ├── pomodoro.php
│   ├── profile.php
│   ├── tasks.php
│   └── topics.php
│
├── assets/
│   ├── audio/
│   │   └── sound.mp3
│   └── images/
│       └── studydesk_logo.svg
│
├── config/
│   └── db.php
│
├── css/
│   ├── auth.css
│   ├── components.css
│   ├── features.css
│   ├── layout.css
│   ├── responsive.css
│   └── variables.css
│
├── includes/
│   ├── admin_layout.php
│   ├── auth_guard.php
│   ├── csrf.php
│   ├── mailer.php
│   ├── response.php
│   ├── user_layout.php
│   └── validate.php
│
├── js/
│   ├── admin/
│   │   ├── admins.js
│   │   ├── home.js
│   │   ├── profile.js
│   │   ├── users.js
│   │   └── view_user.js
│   │
│   ├── api.js
│   ├── calendar.js
│   ├── dialog.js
│   ├── exams.js
│   ├── forgot_password.js
│   ├── home.js
│   ├── login.js
│   ├── nav.js
│   ├── notes.js
│   ├── pomodoro.js
│   ├── profile.js
│   ├── register.js
│   ├── reset_password.js
│   ├── tasks.js
│   ├── theme.js
│   ├── toast.js
│   ├── utils.js
│   └── validate.js
│
├── libs/
│   └── phpmailer/
│       ├── Exception.php
│       ├── PHPMailer.php
│       └── SMTP.php
│
├── pages/
│   ├── admin/
│   │   ├── admins.php
│   │   ├── help.php
│   │   ├── home.php
│   │   ├── profile.php
│   │   ├── users.php
│   │   └── view_user.php
│   │
│   ├── exams.php
│   ├── forgot_password.php
│   ├── help.php
│   ├── home.php
│   ├── login.php
│   ├── notes.php
│   ├── pomodoro.php
│   ├── profile.php
│   ├── register.php
│   ├── reset_password.php
│   └── tasks.php
│
├── .env
├── .env.example
├── .gitignore
├── .htaccess
├── database.sql
├── index.php
├── LICENSE
└── README.md
```

---

## 📄 License

MIT — see [LICENSE](LICENSE)

---

## 📬 Contact

**Anuraj Singh**

- 🐙 GitHub: [@anuraj-js](https://github.com/anuraj-js)
- 📧 Email: [anuraj1q@gmail.com](mailto:anuraj1q@gmail.com)