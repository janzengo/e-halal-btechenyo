# E-Halal BTECHenyo Voting System

E-Halal BTECHenyo is a secure web-based voting system designed for Dalubhasaang Politekniko ng Lungsod ng Baliwag. The system enables fair and transparent student council elections through a modern, secure, and user-friendly platform.

## Core Features

### Voter Features
- Two-factor authentication with OTP verification
- Card-based voting interface with real-time validation
- Vote receipt generation and verification
- Mobile-responsive design

### Administrative Features
- Role-based access control (Head Admin/Officers)
- Complete election lifecycle management
- Real-time monitoring and status tracking
- Comprehensive audit logging

## Technology Stack

### Backend
- PHP 7.4+
- MySQL/MariaDB
- Object-Oriented Architecture

### Frontend
- Bootstrap 3
- jQuery 2.2.4
- Chart.js
- SweetAlert2
- Font Awesome 4.7.0

### Server Requirements
- Apache/Nginx web server
- PHP 7.4 or higher
- MySQL 5.7/MariaDB 10.4+
- mod_rewrite enabled
- SMTP server access

## Installation

1. **Database Setup**
   - Create a MySQL database
   - Import schema from `db/schema.sql`

2. **Environment Configuration**
   ```env
   DB_HOST=localhost
   DB_NAME=e-halal
   DB_USERNAME=root
   DB_PASSWORD=

   MAIL_MAILER=smtp
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=
   MAIL_PASSWORD=
   MAIL_ENCRYPTION=tls
   ```

## Directory Structure
```
e-halal/
├── administrator/         # Administrative interface
│   ├── classes/          # Admin-specific classes
│   ├── includes/         # Admin components
│   └── modals/          # Admin modal forms
├── classes/              # Core system classes
│   ├── Database.php     # Database connection
│   ├── Election.php     # Election management
│   ├── OTPMailer.php    # Email system
│   ├── User.php         # User management
│   └── Votes.php        # Vote processing
├── dist/                 # Distribution files
├── images/              # Uploaded images
├── modals/              # Voter interface modals
└── vendor/              # Composer packages
```

## Security Features
- Two-factor authentication with OTP
- Session management and security
- Audit logging of all activities
- Encrypted vote storage
- Role-based access control
- Rate limiting for OTP requests

## Contributors
- Project Manager: Katrina Dela Cruz
- Lead Developer: Janzen Go
- UI/UX: Michael Domo
- Testing: Mike Adrian Dela Cruz
- Data Gathering Lead: Beatrice Valisno
- Research and Documentation Lead: Jennylyn Vinuya

