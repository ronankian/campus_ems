# Campus Event Management System

A web-based event management system designed for educational institutions to manage and organize campus events efficiently.

## Features

- User Authentication (Admin, Organizers, and Attendees)
- Event Management
  - Create, Read, Update, and Delete events
  - Event details including title, description, date, time, and location
  - Event status tracking (upcoming, ongoing, completed)
- User Dashboard
  - View created events
  - Manage event details
- Admin Panel
  - User management
  - Event oversight
  - System configuration
- PDF Generation
  - Event details export
  - Reports generation
  - Using TCPDF library

## Technical Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web Server (Apache/Nginx)
- XAMPP/WAMP/MAMP (for local development)
- Composer (for dependency management)

## Installation

1. Clone the repository to your local machine:

   ```bash
   git clone [repository-url]
   ```

2. Set up your web server (XAMPP/WAMP/MAMP) and ensure it's running

3. Import the database:

   - Open phpMyAdmin
   - Create a new database named `campus_ems`
   - Import the `campus_ems.sql` file

4. Configure the database connection:

   - Open `config/database.php`
   - Update the database credentials if needed:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'campus_ems');
     ```

5. Configure Email (Optional):

   - Follow the guide to configure XAMPP to send mail from localhost
   - This enables email verification for new user registrations

6. PDF Generation Setup:

   - If installed via Composer:
     ```bash
     composer install
     ```
   - If using the included TCPDF folder, no additional steps needed

7. Access the application:
   - Open your web browser
   - Navigate to `http://localhost/campus_ems`

## Available Accounts

### Admin Account

- Username: admin
- Password: abc123

### Organizer Accounts

- Username: organizer_A
- Password: abc123
- Username: organizer_B
- Password: abc123

### Attendee Account

- Username: attendee_A
- Password: abc123

### New User Registration

- Users can sign up using their email
- Email verification is required
- Configure XAMPP mail settings to enable email functionality

## Directory Structure

```
campus_ems/
├── admin/              # Admin panel files
├── assets/            # CSS, JS, and image files
├── config/            # Configuration files
├── includes/          # PHP includes and functions
├── uploads/           # Uploaded files
├── tcpdf/             # TCPDF library files
└── index.php          # Main entry point
```

## Security Features

- Password hashing using PHP's password_hash()
- Prepared statements for SQL queries
- Session management
- Input validation and sanitization
- Email verification for new registrations

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support, please open an issue in the repository or contact the development team.

## Acknowledgments

- Bootstrap for the frontend framework
- jQuery for JavaScript functionality
- Font Awesome for icons
- TCPDF for PDF generation
