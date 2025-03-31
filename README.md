# Duty Time Record System

[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)
[![PRs Welcome](https://img.shields.io/badge/PRs-welcome-brightgreen.svg)](https://github.com/Joshuasoco/hk-duty-tracker/pulls)
![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-blue)
![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-orange)

A web-based Daily Time Record (DTR) management system built with PHP and MySQL, designed to handle attendance tracking for educational institutions.

## Features

- Admin Dashboard
- Teacher Management
- Student Profile Management
- Attendance Tracking
- User Authentication
- Responsive Design

## Requirements

- PHP 7.0 or higher
- MySQL/MariaDB
- XAMPP/Apache Web Server
- Modern Web Browser

## Installation

1. Clone this repository to your XAMPP's htdocs folder:
```bash
git clone [repository-url] duty
```

2. Import the database schema (located in the `database` folder) to your MySQL server

3. Configure your database connection in the configuration file

4. Access the application through your web browser:
```
http://localhost/duty
```

## Project Structure

- `/admin` - Administrator interface and functions
- `/admin_teacher` - Teacher management interface
- `/assets` - CSS, JavaScript, and other static resources
- `/docs` - Project documentation

## Security

- Passwords are securely hashed
- Input validation and sanitization
- Session-based authentication

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and queries, please create an issue in the repository.
