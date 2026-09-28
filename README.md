# StudyDesk

A student productivity web app for managing tasks, exams, study sessions, and notes — with a full admin panel for account/content moderation.

Built as a 4th-semester BCA project under Tribhuvan University (TU). I noticed student productivity tools tend to be scattered across many different apps, so StudyDesk brings task management, exam planning, and study tools together in one place — no switching between multiple apps just to stay organized.

## Demo Videos

📺 [Watch the full feature demo playlist](https://www.youtube.com/playlist?list=PLLJXw_Rlu1ho) — covers registration, login, password reset, core features (dashboard, tasks, exams, pomodoro, notes, activity, themes), profile/feedback/help, and the admin panel.

## Features

### User
- Authentication — register, login, forgot/reset password via email
- Dashboard — progress overview, streaks, recent activity
- Task Manager — create/complete tasks with type, priority, due date
- Exam Planner — exams with topic-level progress tracking
- Pomodoro Timer — logged into activity history
- Quick Notes — auto-saving scratchpad
- Activity History — timeline of everything completed
- Profile — theme (3 colors), dark mode, timezone, personal info
- Feedback — submit bug reports / feature requests / general feedback

### Admin
- Dashboard — site-wide stats (users, tasks, exams, activity)
- User management — search/view all users, view full profiles with usage stats, delete accounts
- Activity log — read-only log of all user activity (completed tasks, exams, pomodoro sessions)
- Feedback management — view, filter, mark read/resolved, delete submissions
- Profile & password — manage the admin's own account
- Themes — same 3 colors and dark mode as the user side
- Help & Support

## Tech Stack

- **Backend:** PHP (vanilla, PDO with prepared statements)
- **Database:** MySQL
- **Frontend:** Vanilla JavaScript, CSS, HTML
- **Email:** PHPMailer (SMTP)

No frontend/backend framework — a deliberate choice to keep the stack simple and dependency-light.

## Setup

1. **Clone the repo**
   ```
   git clone https://github.com/anuraj-js/studydesk.git
   cd studydesk
   ```

2. **Create the database**
   ```
   mysql -u root -p -e "CREATE DATABASE studydesk_db"
   mysql -u root -p studydesk_db < schema.sql
   ```

3. **Configure environment**
   ```
   cp .env.example .env
   ```
   Fill in your DB credentials and SMTP details in `.env` (see comments in the file for Gmail App Password setup).

4. **Run locally**

   Using [Laragon](https://laragon.org/): drop the folder into `laragon/www/studydesk`, start Apache + MySQL from the Laragon control panel, then visit `http://studydesk.test` (or `http://localhost/studydesk`).

5. **Create an admin account**

   Generate a bcrypt password hash:
   ```
   php -r "echo password_hash('your_password', PASSWORD_DEFAULT);"
   ```

   Then insert the admin account directly:
   ```sql
   INSERT INTO users (username, email, password, role, academic_level, dob, gender)
   VALUES ('admin', 'admin@example.com', 'PASTE_HASH_HERE', 'admin', 'bachelor', '2000-01-01', 'male');
   ```

## License

MIT — see [LICENSE](LICENSE)

## Contact

**Anuraj Singh**
GitHub: [@anuraj-js](https://github.com/anuraj-js)
Email: anuraj1q@gmail.com