# USSD Student Management System

This project is a PHP-based USSD application for managing students, marks, modules, appeals, and admin users. It includes a web-based admin dashboard and supports USSD integration for mobile access.

## Features
- Admin registration and login
- Student, module, marks, and appeals management
- USSD endpoint for mobile access
- Modern, responsive admin dashboard
- Database reset and sample data seeding script

## Folder Structure
- `admin.php` — Admin dashboard
- `register.php` — Admin registration page
- `login.php` — Admin login page
- `student.php` — Student USSD endpoint (or management)
- `db.php` — Database connection
- `style.css` — Main stylesheet
- `reset_and_seed.php` — Script to reset and seed the database
- `ussd.sql` — Database schema and sample data
- `README.md` — Project documentation

## Setup Instructions
1. **Clone or copy the project to your XAMPP/htdocs directory.**
2. **Import the database:**
   - Open phpMyAdmin and import `ussd.sql` to create the required tables.
3. **Configure the database connection:**
   - Edit `db.php` with your MySQL credentials if needed.
4. **Start Apache and MySQL in XAMPP.**
5. **Access the app:**
   - Visit `http://localhost/ussd/register.php` to create the first admin account.
   - Login at `http://localhost/ussd/login.php`.
   - Use the admin dashboard at `http://localhost/ussd/admin.php`.
6. **Reset and seed the database (optional):**
   - Run `reset_and_seed.php` in your browser to reset and insert sample data.

## USSD Testing with ngrok
1. Download ngrok and place it in the `ussd` folder.
2. In PowerShell, run:
   ```powershell
   cd C:\xampp\htdocs\ussd
   .\ngrok.exe http 80
   ```
3. Use the public ngrok URL as your USSD endpoint in your simulator or with your mobile operator.

## Security Note
- Remove or secure `reset_and_seed.php` after use to prevent unauthorized database resets.

## License
This project is for educational purposes. Customize and use as needed.
