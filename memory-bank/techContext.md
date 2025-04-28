# Technical Context

## Development Environment

### 1. Server Requirements
- Apache/Nginx web server
- PHP 7.4 or higher
- MySQL 5.7/MariaDB 10.4+
- mod_rewrite enabled
- SMTP server access

### 2. Development Tools
- XAMPP/WAMP for local development
- Visual Studio Code
- Git for version control
- Composer for dependency management
- npm for frontend packages

## Dependencies

### 1. Backend (Composer)
```json
{
    "require": {
        "phpmailer/phpmailer": "^6.0",
        "vlucas/phpdotenv": "^5.0"
    }
}
```

### 2. Frontend (npm)
```json
{
    "dependencies": {
        "bootstrap": "3.3.7",
        "jquery": "2.2.4",
        "chart.js": "^4.4.8",
        "sweetalert2": "^11.17.2",
        "datatables.net": "1.10.15",
        "font-awesome": "4.7.0"
    }
}
```

## Directory Structure
```
e-halal/
├── admin/                 # Administrative interface
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
├── sql/                 # Database scripts
└── vendor/              # Composer packages
```

## Configuration

### 1. Environment Variables (.env)
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

### 2. Database Configuration
- InnoDB engine
- UTF8MB4 character set
- Strict mode enabled
- Transaction support
- Foreign key constraints

### 3. Server Configuration
```apache
<VirtualHost *:80>
    DocumentRoot "/path/to/e-halal"
    ServerName e-halal.local
    
    <Directory "/path/to/e-halal">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

## Security Measures

### 1. File Permissions
```bash
# Directories
chmod 755 /path/to/e-halal
chmod 755 /path/to/e-halal/images

# Files
chmod 644 /path/to/e-halal/.env
chmod 644 /path/to/e-halal/*.php
```

### 2. Access Control
- `.htaccess` protection
- Session timeout (30 minutes)
- IP-based rate limiting
- File type restrictions
- Directory listing prevention

## Deployment Process

### 1. Pre-deployment Checklist
- [ ] Update dependencies
- [ ] Run security checks
- [ ] Backup database
- [ ] Test mail configuration
- [ ] Verify file permissions

### 2. Deployment Steps
1. Pull latest code
2. Install/update dependencies
3. Run database migrations
4. Clear caches
5. Update environment variables
6. Verify configurations
7. Test core functionality

## Monitoring & Maintenance

### 1. Error Logging
- PHP error logs
- Application logs
- Mail system logs
- Database logs
- Security logs

### 2. Performance Monitoring
- Server resources
- Database queries
- Response times
- Session management
- Mail queue

## Backup Strategy

### 1. Database Backups
- Daily full backup
- Transaction logs
- Stored procedures
- User permissions
- Configuration data

### 2. File Backups
- Code repository
- Uploaded images
- Configuration files
- Log files
- Environment files

## Technical Constraints

### 1. System Limitations
- Single active election
- Maximum file upload size: 5MB
- Session timeout: 30 minutes
- OTP validity: 10 minutes
- Maximum login attempts: 5

### 2. Browser Support
- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+
- Opera 67+

## Integration Points

### 1. Email System
- SMTP configuration
- Template management
- Queue handling
- Error recovery
- Rate limiting

### 2. Database
- Connection pooling
- Query optimization
- Index management
- Backup coordination
- Replication support

## Database Design

### Core Tables

#### 1. Admin Table (`admin`)
```sql
CREATE TABLE `admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(60) NOT NULL, -- Bcrypt hashed
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL, -- New column for OTP and notifications
  `photo` varchar(150) NOT NULL,
  `created_on` date NOT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'officer',
  `gender` varchar(10) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```

#### 2. Election Status Table (`election_status`)
```sql
- id (PK, AUTO_INCREMENT)
- status (enum) - ['setup','pending','active','paused','completed']
- election_name (varchar(255))
- created_at (datetime)
- end_time (datetime)
- last_status_change (datetime)
- control_number (varchar(20)) - Auto-generated
```

#### 3. Election History Table (`election_history`)
```sql
- id (PK, AUTO_INCREMENT)
- election_name (varchar(255))
- status (enum) - ['setup','pending','active','paused','completed']
- end_time (datetime)
- last_status_change (datetime)
- details_pdf (varchar(255))
- results_pdf (varchar(255))
- created_at (timestamp)
- control_number (varchar(20))
```

### Voting System Tables

#### 1. Voters Table (`voters`)
```sql
- id (PK, AUTO_INCREMENT)
- course_id (FK to courses)
- student_number (varchar(20), UNIQUE)
- has_voted (tinyint(1))
- created_at (timestamp)
```

#### 2. Votes Table (`votes`)
```sql
- id (PK, AUTO_INCREMENT)
- election_id (FK to election_status)
- vote_ref (varchar(20), UNIQUE)
- votes_data (JSON)
- created_at (timestamp)
```

#### 3. OTP Requests Table (`otp_requests`)
```sql
- id (PK, AUTO_INCREMENT)
- student_number (FK to voters)
- otp (varchar(6))
- attempts (int)
- created_at (timestamp)
- expires_at (datetime)
```

### Election Configuration Tables

#### 1. Positions Table (`positions`)
```sql
- id (PK, AUTO_INCREMENT)
- description (varchar(50))
- max_vote (int)
- priority (int)
```

#### 2. Candidates Table (`candidates`)
```sql
- id (PK, AUTO_INCREMENT)
- position_id (FK to positions)
- firstname (varchar(30))
- lastname (varchar(30))
- partylist_id (FK to partylists)
- photo (varchar(150))
- platform (text)
- votes (int)
```

#### 3. Partylists Table (`partylists`)
```sql
- id (PK, AUTO_INCREMENT)
- name (varchar(255))
```

#### 4. Courses Table (`courses`)
```sql
- id (PK, AUTO_INCREMENT)
- description (varchar(255))
```

### Database Triggers

#### 1. OTP Request Triggers
- `before_otp_insert`: Rate limits OTP requests (max 5 per hour)
- `tr_delete_max_attempts`: Handles max attempt violations

#### 2. Votes Trigger
- `before_vote_insert`: Auto-generates vote reference numbers

### Key Constraints

1. **Referential Integrity**:
   - Candidates → Positions (ON DELETE CASCADE)
   - Candidates → Partylists (ON DELETE CASCADE)
   - Voters → Courses (ON DELETE CASCADE)
   - OTP Requests → Voters (ON DELETE CASCADE)
   - Votes → Election Status

2. **Unique Constraints**:
   - Student numbers in voters table
   - Vote references in votes table
   - Control numbers in election tables

### Indexes

1. **Primary Indexes**:
   - All tables have auto-incrementing primary keys

2. **Secondary Indexes**:
   - Election status and history tables: status + end_time
   - OTP requests: student_number + otp, expires_at
   - Votes: election_id + created_at


## Data Management

### 1. File Storage
- Admin photos: `assets/images/administrators/`
- Election PDFs: `archives/{control_number}/`
- Candidate photos: Path structure in database

### 2. Automated Processes
- OTP request cleanup
- Vote reference generation
- Election control number generation
- Status change tracking

### 3. Data Validation
- Enum constraints for election status
- JSON validation for vote data
- Foreign key constraints
- Unique key constraints

### Election Reset Process
- Complete table reset functionality implemented in `Elections::resetElection()`
- Tables affected during reset:
  - `votes` - Truncated
  - `voters` - Truncated
  - `candidates` - Truncated
  - `courses` - Truncated
  - `partylists` - Truncated
  - `positions` - Truncated
  - `admin` - Officers removed (head admin retained)
  - `election_status` - Reset with new control number

### Log Management
- Structured log archival system
- Log files:
  1. `admin_logs.json` - Administrative actions
  2. `voters_logs.json` - Voter activities (private)
- Archive location: `/archives/{control_number}/logs/`
- Log handling:
  - Archived with each election completion
  - Reset to empty after archival
  - Maintains historical record while ensuring clean state for new elections

### Data Archival
- Election data archived before reset
- Control number based archival system
- Archive structure:
  ```
  /archives/
    /{control_number}/
      /logs/
        - admin_logs.json
        - voters_logs.json
      - summary.pdf
      - results.pdf
  ```

## CSS Implementation

### Core Stylesheets
- `bootstrap.min.css`: Base Bootstrap 5.x framework
- `style.css`: Global application styles
- `setup.css`: Setup phase specific styles (conditionally loaded)

### Setup Interface Styles
The `setup.css` file implements specific styles for the election setup interface:

1. **Button Styling**
   - Consistent styling for primary and default buttons
   - State-specific styles (hover, disabled)
   - Responsive sizing for mobile devices

2. **Navigation**
   - Tab-based navigation styling
   - Active state indicators
   - Mobile-responsive adjustments

3. **Form Elements**
   - Standardized form group spacing
   - Focus state enhancements
   - Validation feedback styling

4. **Progress Tracking**
   - Visual progress indicators
   - Status item styling
   - Icon integration

5. **Responsive Design**
   - Mobile-first approach
   - Breakpoint-specific adjustments
   - Flexible navigation handling

### Loading Strategy
- Base stylesheets loaded globally
- `setup.css` loaded conditionally on setup and configure pages
- Mobile viewport optimization implemented

### Dependencies
- Bootstrap 5.x framework
- Font Awesome icons
- Custom styles for setup interface

### Performance Considerations
- Conditional loading reduces unnecessary CSS
- Modern viewport tags for responsive rendering
- Minimal use of complex selectors
- Efficient state management through CSS classes

### Known Issues
- ~~Global loading of setup.css~~ (Fixed)
- Some duplicate Bootstrap class overrides
- CSS minification needed for production

### Recommended Improvements
- Implement CSS modules for better encapsulation
- Add CSS variables for theme consistency
- Regular audit of unused CSS rules
- Implement critical CSS loading

### Known Issues
1. **Global Loading**: `setup.css` is currently loaded on all pages through `renderHeader()`, impacting performance
2. **Potential Conflicts**: Some styles may conflict with other pages due to global scope
3. **Bootstrap Overrides**: Contains duplicate Bootstrap classes that may cause specificity issues

### Recommended Improvements
1. **Conditional Loading**: Only load `setup.css` on setup-related pages
2. **Modular Structure**: Separate common styles from setup-specific styles
3. **Specificity Management**: Use proper namespacing to prevent style conflicts
4. **Performance Optimization**: Minify CSS and implement proper caching 

## JavaScript Implementation

### Core Libraries
- jQuery 3.6.x for DOM manipulation and AJAX
- Bootstrap 5.x for UI components
- SweetAlert2 for notifications and dialogs

### Table Implementation
- Server-side rendering for all data tables
- Direct PHP integration with database queries
- Bootstrap table classes for consistent styling
- Custom sorting and filtering through backend APIs

### Form Handling
- Client-side validation using jQuery
- AJAX form submissions with progress indicators
- SweetAlert2 for user feedback and confirmations
- Standardized error handling and display

### Event Handling
- Delegated event handlers for dynamic content
- Consistent CRUD operation handlers
- Modal management for forms and dialogs
- Progress tracking and state management

### Performance Optimizations
- Removed client-side data processing (DataTables)
- Minimized AJAX calls through server-side rendering
- Efficient DOM updates using jQuery
- Lazy loading of modal content

### Security Measures
- CSRF token validation
- Input sanitization
- XSS prevention
- Secure form submissions

### Known Issues
- Need to implement client-side caching
- Form validation needs enhancement
- Mobile responsiveness improvements needed
- Loading states not consistently implemented

### Future Improvements
1. Implement progressive enhancement
2. Add service worker for offline support
3. Enhance client-side validation
4. Implement WebSocket for real-time updates

## Technical Stack

### Database
- MariaDB 10.4.32
- UTF8MB4 Character Set
- InnoDB Engine

### Server Requirements
- PHP 8.2.12
- Apache/Nginx Web Server
- MySQL/MariaDB Database

## Security Features

### 1. Password Security
- Bcrypt hashing for admin passwords
- 60-character hash storage

### 2. OTP System
- 6-digit OTP codes
- Rate limiting (5 attempts per hour)
- Auto-expiry system
- Attempt tracking

### 3. Vote Security
- Unique vote references
- JSON vote data storage
- Audit trail via timestamps
- Election status tracking

