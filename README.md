# Cybersecurity Incident Management System

A fully functional PHP + MySQL web application for managing cybersecurity incidents and systems/assets. This system demonstrates CRUD operations, secure coding practices, search functionality, and AJAX integration.

## Features

### Core Functionality
- **Full CRUD Operations** for Incidents:
  - Create, Read, Update, Delete incidents
  - Track incident type, date/time, affected system, severity, status, and resolution notes
  
- **Full CRUD Operations** for Systems/Assets:
  - Manage systems (Servers, Applications, Databases, Mail Gateways)
  - Track system status (Active, Inactive, Maintenance)

- **Incident Status Tracking**:
  - Three status levels: Detected, Investigating, Resolved
  - Live status updates via AJAX (no page reload required)

- **Advanced Search Functionality**:
  - Search by incident type, severity, status, affected system
  - Date range filtering (from/to dates)
  - Multiple criteria can be combined

- **Dashboard with Auto-Refresh**:
  - Real-time statistics (Total, Detected, Investigating, Resolved)
  - Auto-refreshes every 30 seconds via AJAX
  - Recent incidents display

### Security Features
- **SQL Injection Prevention**: All database queries use prepared statements
- **XSS Protection**: All output is escaped using `htmlspecialchars()`
- **CSRF Protection**: CSRF tokens implemented on all update/delete operations
- **Input Validation**: Server-side validation on all forms

### AJAX Features
- **Live Status Updates**: Update incident status without page reload
- **Auto-Refresh Dashboard**: Dashboard counters automatically refresh every 30 seconds
- **Real-time Feedback**: Success/error messages displayed dynamically

## Project Structure

```
project_root/
│── config/
│   └── db.php                 # Database configuration
│── public/
│   ├── index.php              # Dashboard
│   ├── incidents.php          # List all incidents
│   ├── add_incident.php       # Add new incident
│   ├── edit_incident.php      # Edit incident
│   ├── view_incident.php      # View incident details
│   ├── delete_incident.php    # Delete incident
│   ├── systems.php            # List all systems
│   ├── add_system.php         # Add new system
│   ├── edit_system.php        # Edit system
│   ├── delete_system.php      # Delete system
│   └── search.php             # Search incidents
│── api/
│   ├── dashboard_stats.php    # AJAX endpoint for dashboard stats
│   └── update_status.php      # AJAX endpoint for status updates
│── includes/
│   ├── header.php             # Header with navigation
│   ├── footer.php             # Footer
│   └── functions.php          # Security and utility functions
│── assets/
│   ├── css/
│   │   └── style.css          # Stylesheet
│   └── js/
│       └── main.js            # JavaScript for AJAX
│── database.sql               # Database structure and sample data
└── README.md                  # This file
```

## Setup Instructions

### Prerequisites
- XAMPP (or similar) with PHP and MySQL
- Web server (Apache)
- phpMyAdmin (optional, for database management)

### Installation Steps

1. **Database Setup**:
   - Open phpMyAdmin (usually at `http://localhost/phpmyadmin`)
   - Import the `database.sql` file:
     - Click on "Import" tab
     - Choose file: `database.sql`
     - Click "Go"
   - Alternatively, you can run the SQL file directly in phpMyAdmin SQL tab

2. **Database Configuration**:
   - Open `config/db.php`
   - Update database credentials if needed (default XAMPP settings):
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'cybersecurity_incidents');
     ```

3. **File Placement**:
   - Place the entire project folder in your web server directory:
     - XAMPP: `C:\xampp\htdocs\` (or your current location)
     - The project should be accessible at: `http://localhost/my-website-project/`

4. **Verify Installation**:
   - Open your browser and navigate to: `http://localhost/my-website-project/`
   - You should see the dashboard with statistics

## Login Credentials

**Note**: This system does not require login credentials. It's designed as a management system that can be accessed directly. For production use, you would need to add authentication.

## Database Information

- **Database Name**: `cybersecurity_incidents`
- **Tables**:
  - `incidents`: Stores all incident records
  - `systems`: Stores all system/asset records

### Sample Data
The database includes sample data:
- 8 sample systems (servers, applications, databases, mail gateways)
- 8 sample incidents with various statuses and severities

## Features Implemented

### ✅ Core Requirements
- [x] Full CRUD for Incidents
- [x] Full CRUD for Systems/Assets
- [x] Incident Status Tracking (Detected, Investigating, Resolved)
- [x] Search functionality (by type, severity, date, system)
- [x] SQL Injection prevention (prepared statements)
- [x] XSS protection (output escaping)
- [x] CSRF protection (on updates/deletes)
- [x] AJAX for live status updates
- [x] AJAX for auto-refresh dashboard counters

### ✅ Additional Features
- [x] Responsive design
- [x] User-friendly interface
- [x] Form validation
- [x] Error handling
- [x] Success/error messages
- [x] Recent incidents on dashboard

## Usage Guide

### Managing Incidents
1. **View All Incidents**: Navigate to "Incidents" from the menu
2. **Add Incident**: Click "Add New Incident" button
3. **Edit Incident**: Click "Edit" button next to an incident
4. **Update Status**: Use the dropdown in the incidents list (AJAX - no page reload)
5. **Delete Incident**: Click "Delete" button (requires confirmation)
6. **View Details**: Click "View" to see full incident information

### Managing Systems
1. **View All Systems**: Navigate to "Systems" from the menu
2. **Add System**: Click "Add New System" button
3. **Edit System**: Click "Edit" button next to a system
4. **Delete System**: Click "Delete" button (cannot delete if associated with incidents)

### Searching
1. Navigate to "Search" from the menu
2. Fill in any combination of search criteria:
   - Incident Type (partial match)
   - Severity
   - Status
   - Affected System
   - Date Range (from/to)
3. Click "Search" to view results
4. Results can be viewed or edited directly

### Dashboard
- View real-time statistics
- See recent incidents
- Statistics auto-refresh every 30 seconds
- Click "View All Incidents" to see complete list

## Security Notes

1. **SQL Injection**: All queries use prepared statements with parameter binding
2. **XSS**: All user input is escaped using `htmlspecialchars()` before display
3. **CSRF**: Update and delete operations require CSRF tokens
4. **Input Validation**: All forms validate required fields and data types

## Known Issues

None at this time. The system is fully functional and ready for use.

## Browser Compatibility

- Chrome (recommended)
- Firefox
- Edge
- Safari

## Technical Details

- **PHP Version**: 7.4+ (compatible with PHP 8.x)
- **MySQL Version**: 5.7+ or MariaDB 10.2+
- **JavaScript**: Vanilla JS (no frameworks required)
- **CSS**: Custom stylesheet (no frameworks)

## Future Enhancements (Optional)

- User authentication and authorization
- File uploads for incident attachments
- Email notifications
- Export to PDF/Excel
- Advanced reporting and analytics
- Template engine integration (Twig/Smarty)

## Support

For issues or questions, please refer to the code comments or contact your instructor.

---

**Developed for**: Assignment - Task 2  
**Date**: January 2026  
**Version**: 1.0
