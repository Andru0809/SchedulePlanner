# Schedule Planner

A comprehensive web-based schedule management application designed to help users efficiently organize their daily activities, manage appointments, and maintain a well-structured timetable.

## 📋 Project Description

Schedule Planner is a feature-rich PHP application that provides users with an intuitive interface for managing their schedules and appointments. The application includes user authentication, conflict detection, weekly/daily views, and email notifications for password reset functionality.

## 🚀 Features

- **User Authentication**: Secure login/registration system with password hashing
- **Schedule Management**: Create, edit, delete, and view personal schedules
- **Conflict Detection**: Automatic detection of overlapping appointments
- **Weekly/Daily Views**: Multiple viewing options for better schedule visualization
- **Password Reset**: Email-based password recovery system
- **Responsive Design**: Mobile-friendly interface using modern CSS and JavaScript
- **Real-time Updates**: Dynamic content updates without page refresh
- **Session Management**: Secure session handling with timeout protection

## 🛠️ Prerequisites

Before you begin, ensure you have the following software installed:

### Required Software
- **PHP 7.4+** (recommended PHP 8.0+)
- **MySQL 5.7+** or **MariaDB 10.2+**
- **Apache Web Server** (XAMPP/WAMP/MAMP recommended for local development)
- **Web Browser** (Chrome, Firefox, Safari, or Edge)

### Optional for Development
- **PHPMyAdmin** (for database management)
- **VS Code** or any code editor
- **Git** (for version control)

## 📦 Installation Steps

Follow these steps to get the Schedule Planner running locally:

### 1. Clone the Repository
```bash
git clone https://github.com/Andru0809/SchedulePlanner.git
cd SchedulePlanner
```

### 2. Set Up Local Server
If using XAMPP:
- Install XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
- Start Apache and MySQL services from XAMPP Control Panel
- Copy the project files to `C:\xampp\htdocs\scheduleplanner\` (Windows) or `/opt/lampp/htdocs/scheduleplanner/` (Linux)

### 3. Database Configuration
The application automatically creates the database and required tables. Ensure:
- MySQL server is running
- Default MySQL credentials are set (username: `root`, password: empty)
- If using different credentials, update `config/database.php`

### 4. Access the Application
Open your web browser and navigate to:
```
http://localhost/scheduleplanner/
```

### 5. Create an Account
- Click "Don't have an account? Register"
- Fill in your details and submit
- Login with your new credentials

## 🗄️ Database Schema

The application creates the following tables automatically:

- **users**: User account information
- **schedules**: User schedules and appointments
- **password_reset_tokens**: Password reset tokens
- **user_sessions**: Session management

## 📁 Project Structure

```
scheduleplanner/
├── api/                    # API endpoints for AJAX requests
├── assets/
│   ├── css/               # Stylesheets
│   ├── js/                # JavaScript files
│   └── images/            # Image assets
├── config/                # Configuration files
├── includes/              # Header and footer templates
├── DB_EXPORT/            # Database exports (excluded from git)
├── logs/                 # Application logs
├── add_schedule.php      # Add schedule page
├── dashboard.php         # Main dashboard
├── forgot_password.php   # Password reset request
├── index.php             # Login page
├── profile.php           # User profile
├── register.php          # User registration
├── reset_password.php    # Password reset form
├── setup_timetable.php   # Timetable configuration
├── timetable.php         # Weekly timetable view
├── validate_token.php    # Token validation
└── logout.php            # User logout
```

## 🎥 Demo Video

Watch the complete demo of the Schedule Planner application:

[📺 Schedule Planner Demo Video](https://www.youtube.com/watch?v=YOUR_VIDEO_ID_HERE)


## 🔧 Configuration

### Database Configuration
Edit `config/database.php` to modify database settings:
```php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'schedule_planner';
```

## 🌐 API Endpoints

The application provides RESTful API endpoints in the `api/` directory:

- `POST /api/add_schedule.php` - Create new schedule
- `GET /api/get_schedule.php` - Retrieve schedules
- `PUT /api/update_schedule.php` - Update existing schedule
- `DELETE /api/delete_schedule.php` - Delete schedule
- `POST /api/check_conflicts.php` - Check for time conflicts

## 🔒 Security Features

- **Password Hashing**: Uses PHP's `password_hash()` with bcrypt
- **SQL Injection Prevention**: Prepared statements for all database queries
- **XSS Protection**: Input sanitization and output escaping
- **Session Security**: Secure session management with timeout
- **CSRF Protection**: Token-based request validation

## 📱 Browser Compatibility

The application is tested and compatible with:
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 📞 Support

For support, please open an issue on the GitHub repository or contact the project maintainer.

## 🙏 Acknowledgments

- [Font Awesome](https://fontawesome.com/) for icons
- [SweetAlert2](https://sweetalert2.github.io/) for beautiful alerts
- [Bootstrap](https://getbootstrap.com/) for responsive design components

---
