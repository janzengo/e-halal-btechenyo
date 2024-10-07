# E-Halal BTECHenyo Voting System

## Overview
E-Halal BTECHenyo is a voting system designed to facilitate fair and transparent voting processes within the local environment of Dalubhasaang Politekniko ng Lungsod ng Baliwag. This system operates exclusively on a local server or intranet, ensuring that it can only be accessed within the institution's network.

## Features
- User-friendly interface for both voters and administrators.
- Secure and efficient voting process.
- Real-time results display.
- PDF generation for vote receipts using TCPDF.

## Technologies Used
- **Backend Language**: PHP
- **Framework**: AdminLTE
- **Libraries**:
  - JQuery
  - Bootstrap
  - dataTables
  - Font Awesome
  - morris.js
  - Datepicker
  - Slider
  - Timepicker
  - Colorpicker
  - FullCalendar

## Installation

### Prerequisites
- XAMPP (or any PHP server)
- A web browser

### Steps
1. **Download and Install XAMPP**:
   - [XAMPP Download](https://www.apachefriends.org/index.html)
   - Follow the installation instructions for your operating system.

2. **Clone or Download the Repository**:
   - You can clone this repository using Git:
     ```bash
     git clone <repository-url>
     ```
   - Or download the ZIP file and extract it.

3. **Copy the Project Files**:
   - Copy the project files to the `htdocs` directory of your XAMPP installation. The default location is usually `C:\xampp\htdocs\`.

4. **Set Up the Database**:
   - Open your web browser and navigate to `http://localhost/phpmyadmin`.
   - Create a new database (e.g., `e_halal_btechenyo`).
   - Import the SQL database file provided in the project directory:
     - Click on the database name you created.
     - Click on the "Import" tab.
     - Choose the SQL file from the project directory and click "Go."

5. **Configure the Application**:
   - Update the database connection settings in the project files if necessary (usually found in a `config.php` or similar file).

6. **Start the XAMPP Server**:
   - Open the XAMPP Control Panel and start the Apache module.

7. **Access the Application**:
   - Open your web browser and go to `http://localhost/<project-folder-name>` to access the voting system.

## Open Contribution
We welcome contributions to improve the E-Halal BTECHenyo Voting System! If you have suggestions, bug fixes, or new features, feel free to fork the repository and submit a pull request. You can also open issues to discuss potential enhancements or report bugs.

## License
This project is open-source and available under the [MIT License](LICENSE).

