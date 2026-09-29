# StudyDesk

A student productivity web app for managing tasks, exams, study sessions, and notes — with a full admin panel for user and content moderation.

Built as a 4th-semester BCA project under Tribhuvan University. Most student productivity tools are scattered across multiple apps, so StudyDesk consolidates task management, exam planning, Pomodoro timing, and note-taking into one place.

---

## Demo

📺 [Watch the full feature demo playlist](https://www.youtube.com/playlist?list=PLLJXw_Rlu1ho) — covers registration, login, password reset, core features, profile, feedback, help, and the admin panel.

---

## Features

### User
- **Authentication** — register, login, logout, forgot/reset password via email
- **Dashboard** — progress overview, activity streaks, recent activity
- **Task Manager** — create, complete, edit, delete tasks with type, priority, and due date
- **Exam Planner** — plan exams with topic-level progress tracking
- **Pomodoro Timer** — focus sessions logged to activity history
- **Quick Note** — auto-saving scratchpad with character/word counter
- **Activity History** — timeline of completed actions (last 90 days)
- **Profile** — update personal info, change password, manage preferences
- **Feedback** — submit bug reports, feature requests, and general feedback
- **Themes** — 3 color themes (blue/purple/red) + dark mode
- **Help & Support** — searchable in-app documentation

### Admin
- **Dashboard** — site-wide stats: total users, tasks, exams, activity
- **User Management** — search, filter, paginate users; view profiles; delete accounts
- **User Profile View** — full account details + activity summary per user
- **Activity Log** — read-only log of all user activity across the platform
- **Feedback Management** — view, filter, mark read/resolved, delete submissions
- **Admin Profile** — manage your own admin account and password
- **Themes** — same theme + dark mode support as the user side
- **Help & Support** — admin-specific documentation

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP (vanilla, PDO with prepared statements) |
| Database | MySQL 8.0 |
| Frontend | Vanilla JavaScript (ES6+), CSS, HTML |
| Email | PHPMailer (SMTP) |

**No frameworks, no Composer, no npm.** A deliberate choice to keep the stack simple and dependency-light.

---

## Setup

### 1. Clone the repository

```bash
git clone https://github.com/anuraj-js/studydesk.git
cd studydesk
```

### 2. Create the database

```bash
mysql -u root -p -e "CREATE DATABASE studydesk_db"
mysql -u root -p studydesk_db < schema.sql
```

### 3. Configure environment

```bash
cp .env.example .env
```

Fill in your DB credentials and SMTP details in `.env`. See comments inside the file for Gmail App Password setup.

### 4. Run locally

**Using Laragon:**
- Drop the folder into `laragon/www/studydesk`
- Start Apache + MySQL from the Laragon control panel
- Visit `http://localhost/studydesk` (or `http://studydesk.test`)

**Using any LAMP/WAMP stack:** Point your web server to the project root and visit the corresponding URL.

### 5. Create an admin account

Admins are created manually for security — there is no admin self-registration.

**a. Generate a bcrypt password hash:**

```bash
php -r "echo password_hash('your_strong_password', PASSWORD_DEFAULT);"
```

You'll get output like:

```
$2y$10$Ss7DOKbqe/2HSKToppYiRupLCwkUM3iWSyjZhU3rJ4AFw//HhVMtu
```

Copy the entire hash (starts with `$2y$10$`).

**b. Insert the admin account:**

```sql
INSERT INTO users (username, email, password, role, academic_level, dob, gender)
VALUES ('admin', 'admin@example.com', '$2y$10$YOUR_HASH_HERE', 'admin', 'bachelor', '2000-01-01', 'male');
```

Replace `YOUR_HASH_HERE` with the generated hash. Username and email must be unique across the `users` table.

**c. Log in with the credentials you set.**

---

## Project Structure

```
studydesk/
├── api/              # Backend API endpoints (PHP)
│   ├── admin/        # Admin-only endpoints
│   ├── auth/         # Login, register, forgot/reset password
│   ├── tasks/        # Task CRUD
│   ├── exams/        # Exam and topic management
│   └── ...
├── config/           # DB connection, gatekeepers, theme, mail, env
├── css/              # Stylesheets (user + admin)
├── forms/            # Auth pages (login, register, forgot, reset)
├── images/           # Logos and theme variants
├── js/               # Frontend scripts (user + admin)
├── pages/            # Protected pages
│   ├── admin/        # Admin panel
│   └── includes/     # Shared header, sidebar
├── phpmailer/        # Email library
├── index.php         # Entry point (role-based routing)
└── schema.sql         # Database schema
```

---

## Security Notes

- Passwords hashed with `password_hash()` (bcrypt)
- SQL injection prevented via PDO prepared statements
- Session-based authentication with role-based access control
- Separate gatekeepers for user and admin routes
- Email credentials stored in `.env` (not committed)
- Feedback submission has anti-spam: rate limiting (3/hour), 5-minute cooldown, duplicate detection

---

## License

MIT — see [LICENSE](LICENSE)

---

## Contact

**Anuraj Singh**
GitHub: [@anuraj-js](https://github.com/anuraj-js)
Email: anuraj1q@gmail.com