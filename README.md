# E-Halal BTECHenyo Voting System
12345678
E-Halal BTECHenyo is a voting system designed to facilitate fair and transparent voting processes within the local environment of Dalubhasaang Politekniko ng Lungsod ng Baliwag. This system operates in the web, making it accessible to every bonafide BTECH students to vote.

## Features

### Voter Features
- **Modern Card-Based Voting Interface**
  - Intuitive candidate selection
  - Real-time vote validation
  - Maximum vote enforcement
  - Mobile-responsive design

- **Secure Authentication**
  - Unique voter ID system
  - Password protection
  - Session management
  - Automatic logout on election end

### Administrative Features
- **Election Management**
  - Configure election parameters
  - Set voting period
  - Real-time election monitoring
  - Post-election results processing

- **Candidate Management**
  - Add/Edit candidates
  - Upload candidate photos
  - Manage party lists
  - Position prioritization

- **Voter Management**
  - Bulk voter registration via CSV
  - Individual voter registration
  - Voter status tracking
  - Access control

- **Results & Analytics**
  - Real-time vote counting
  - Generate PDF reports
  - Election statistics
  - Vote audit logs

## Technology Stack

- **Frontend**
  - HTML5, CSS3, JavaScript
  - Bootstrap 3
  - Font Awesome 5
  - Chart.js
  - jQuery

- **Backend**
  - PHP 7.4+
  - MySQL/MariaDB
  - Object-Oriented Programming
  - MVC Architecture

- **Server Requirements**
  - Apache/Nginx
  - PHP 7.4 or higher
  - MySQL 5.7 or higher
  - mod_rewrite enabled

## Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/yourusername/e-halal.git
   ```

2. **Database Setup**
   - Create a MySQL database
   - Import the provided SQL file from `database/e-halal.sql`

3. **Configuration**
   - Configure database connection in `classes/Database.php`
   - Set timezone in `init.php`
   - Configure election parameters in `admin/config.ini`

4. **Server Configuration**
   - Ensure mod_rewrite is enabled
   - Set proper file permissions
   - Configure virtual host (optional)

## Project Structure

```
e-halal/
├── admin/                 # Administrative interface
│   ├── classes/          # Admin-specific classes
│   ├── includes/         # Admin components
│   └── modals/          # Admin modal forms
├── classes/              # Core system classes
├── database/             # Database scripts
├── dist/                 # Distribution files
├── docs/                # Documentation
├── images/              # Uploaded images
└── modals/              # Voter interface modals
```

## Security Features

- Password hashing using bcrypt
- SQL injection prevention
- XSS protection
- CSRF protection
- Session security
- Input validation
- Access control
- Audit logging

## User Roles

### Admin
- Full system access
- Election configuration
- User management
- Results processing

### Voters
- One-time voting access
- View candidate information
- Track voting status
- View election results

## Recent Updates (January 21-25, 2025)

- Implemented modern card-based voting interface
- Transformed codebase to OOP architecture
- Enhanced security measures
- Improved user experience
- Added real-time vote validation
- See [January 21-25 Documentation](docs/January%2021-25%20Documentation.md) for details

## Development

### Prerequisites
- XAMPP/WAMP/LAMP
- Git
- Text editor (VS Code recommended)
- Basic PHP knowledge

### Setup Development Environment
1. Install XAMPP
2. Clone repository to htdocs
3. Configure virtual host
4. Import database
5. Start development

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Contributors

- Project Manager: Katrina Dela Cruz
- Lead Developer: Janzen Go
- UI/UX: Michael Domo
- Testing: Mike Adrian Dela Cruz
- Data Gathering Lead: Beatrice Valisno
- Research and Docu Lead: Jennylyn Vinuya

## Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## Support

For support, please email [your-email@domain.com]

---

Made with ❤️ by BTECHenyo Team
