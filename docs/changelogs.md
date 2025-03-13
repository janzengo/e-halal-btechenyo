# Changelog

## [Unreleased]

### Features
- **Election Status Management**: Introduced a new 'pending' status for elections, allowing administrators to set up elections without requiring start and end times.
- **Dynamic Status Validation**: Enhanced the validation logic to ensure that start and end times are only required when the election status is not 'pending'.
- **Improved User Experience**: Added clear messages and rules regarding election statuses, guiding administrators on what actions are permissible based on the current status.

### Enhancements
- **AJAX Functionality**: Improved AJAX handling for OTP requests, providing real-time feedback to users during the login process.
- **Email Receipt Generation**: Updated the email receipt generation to ensure that the correct date and time are included in the email content.
- **Logging Enhancements**: Added detailed logging for election configuration changes, capturing the admin's actions and the state of the election.

### Bug Fixes
- **Redirection Issues**: Fixed redirection logic in `election_configure.php` to ensure it follows the routing defined in the `.htaccess` file, preventing incorrect paths that disrupt navigation.
- **Fatal Errors**: Resolved issues with `mysqli_stmt::bind_param()` by ensuring that parameters are correctly passed by reference, preventing fatal errors during database operations.

### Database Changes
- **SQL Schema Updates**: Modified the `election_status` table to accommodate the new 'pending' status and adjusted the logic for automatic status changes based on election timings.
- **Event Management**: Implemented event scheduling for automatic election status updates, ensuring elections transition between 'on', 'off', and 'paused' states based on defined time conditions.

### Code Refactoring
- **Modularization**: Refactored the `Elections.php` class to improve readability and maintainability, separating concerns and enhancing the overall structure of the code.
- **Consistent Error Handling**: Standardized error handling across the application to provide clearer feedback to users and maintain a consistent user experience.

### Documentation
- **Updated Documentation**: Enhanced inline documentation and comments throughout the codebase to clarify the purpose and functionality of various components, aiding future development and maintenance.

## [Previous Releases]
- **Initial Release**: Launched the E-Halal Voting System with basic functionalities for managing elections, candidates, and voters.
