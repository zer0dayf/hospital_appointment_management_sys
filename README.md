# MediCare - Hospital Appointment Management System

MediCare is a modern, lightweight, and secure Hospital Management System designed to streamline patient scheduling and doctor management. Built with a modular PHP backend and a responsive vanilla JavaScript frontend.

![MediCare Dashboard Mockup](https://raw.githubusercontent.com/zer0dayf/hospital_appointment_management_sys/main/preview.png)

## 🚀 Key Features

- **Modular Architecture**: Refactored using an MVC-inspired controller pattern for maintainability.
- **Enhanced Security**: Server-side input validation and sanitization to prevent XSS and injection attacks.
- **Modern UI/UX**:
  - ✨ **Dark Mode**: Native support with persistent theme selection.
  - 🔍 **Real-time Search**: Instant filtering for appointments, patients, and doctors.
- **RESTful API**: Clean API endpoints for CRUD operations.
- **PostgreSQL Support**: Optimized for high-performance data management with indexed queries.

## 🛠️ Tech Stack

- **Frontend**: HTML5, CSS3 (Modern Flex/Grid), Vanilla JavaScript (ES6+).
- **Backend**: PHP 8.x (Controller pattern).
- **Database**: PostgreSQL 17+.
- **Server**: Compatible with Apache, Nginx, or PHP's built-in server.

## 📦 Installation

### 1. Clone the repository
```bash
git clone https://github.com/zer0dayf/hospital_appointment_management_sys.git
cd hospital_appointment_management_sys
```

### 2. Database Setup
Ensure PostgreSQL is running, then create the database and import the schema:
```bash
createdb hospital_db
psql -d hospital_db -f database.sql
```

### 3. Configuration
Update your database credentials in `db_connect.php`:
```php
$host = 'localhost';
$dbname = 'hospital_db';
$username = 'your_username';
$password = 'your_password';
```

### 4. Run the Application
You can use the built-in PHP server for testing:
```bash
php -S localhost:8000
```
Then visit `http://localhost:8000` in your browser.

## 📂 Project Structure

```text
├── api.php           # Main router for API requests
├── app.js            # Frontend logic and state management
├── controllers/      # Modular business logic (Patient, Doctor, Appointment)
├── database.sql      # PostgreSQL schema and seeding script
├── db_connect.php    # Database connection logic
├── index.html        # Main dashboard UI
└── style.css         # Modern styling and theme variables
```

## 📄 License
This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---
Developed with ❤️ by [zer0dayf](https://github.com/zer0dayf)
