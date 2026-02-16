# FM Checks - Facilities Management System

A comprehensive PHP-based facilities management system for periodic safety and maintenance checks. Managers upload CAD floor plans (PDF), define check points on them, and staff walk the venue performing checks on a defined schedule. Results are logged, failures are reported, and a full audit trail is maintained.

## Features

- **Venue Management** - Organize facilities by venues and areas
- **PDF Floor Plans** - Upload and calibrate CAD drawings
- **Check Points** - Define check locations on plans with visual markers
- **Periodic Checks** - Daily, weekly, monthly, quarterly, and annual schedules
- **Visual Status** - Color-coded check points (green/amber/red/grey)
- **Check Logging** - Pass/fail recording with notes and photos
- **Reports Register** - Track failures and issues with severity levels
- **User Roles** - Admin, Manager, Staff, and Viewer permissions
- **Dark Mode** - Toggle between light and dark themes
- **Responsive Design** - Works on desktop, tablet, and mobile

## Tech Stack

- PHP 8.x
- MySQL 8.x
- Vanilla JavaScript
- HTML5 / CSS3
- PDF.js for rendering

## Architecture

- **Front controller** - All requests route through `public/index.php`
- **Static models** - All model classes use `public static` methods
- **Static controllers** - All controller methods are `public static`
- **PHP template views** - Simple PHP templates with layout wrapper
- **CSRF protection** - Built-in CSRF tokens on all forms
- **Role-based permissions** - Permission system with 4 roles

## Installation

### Requirements

- PHP 8.0 or higher
- MySQL 8.0 or higher
- Apache with mod_rewrite enabled
- Composer (optional)

### Step 1: Clone the Repository

```bash
git clone <repository-url> fmchecks
cd fmchecks
```

### Step 2: Configure Database

1. Create a MySQL database:

```sql
CREATE DATABASE fmchecks CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Copy the database configuration:

```bash
cp config/database.example.php config/database.local.php
```

3. Edit `config/database.local.php` with your credentials:

```php
return [
    'host' => 'localhost',
    'port' => 3306,
    'database' => 'fmchecks',
    'username' => 'your_username',
    'password' => 'your_password',
];
```

### Step 3: Import Database Schema

```bash
mysql -u your_username -p fmchecks < database/schema.sql
```

This creates all tables and inserts:
- Default admin user (username: `admin`, password: `admin123`)
- 8 default check types
- System settings

### Step 4: Set Permissions

```bash
chmod -R 755 public/uploads
chown -R www-data:www-data public/uploads
```

### Step 5: Configure Apache

Point your Apache document root to the `public` directory.

Example Apache virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName fmchecks.local
    DocumentRoot /path/to/fmchecks/public

    <Directory /path/to/fmchecks/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/fmchecks_error.log
    CustomLog ${APACHE_LOG_DIR}/fmchecks_access.log combined
</VirtualHost>
```

Enable the site and restart Apache:

```bash
sudo a2ensite fmchecks
sudo systemctl restart apache2
```

### Step 6: Access the Application

Navigate to your configured domain (e.g., http://fmchecks.local)

**Default Login:**
- Username: `admin`
- Password: `admin123`

**IMPORTANT:** Change the default admin password immediately after first login!

## User Roles

| Role    | Permissions |
|---------|-------------|
| **Admin** | Full system access - manage everything |
| **Manager** | Manage venues, areas, checks, reports. View users. |
| **Staff** | Perform checks, view reports |
| **Viewer** | Read-only access to everything |

## Usage Guide

### 1. Create a Venue

- Navigate to **Venues** → **Add Venue**
- Enter venue name, address, and notes
- Click **Create Venue**

### 2. Upload Area Plans

- Navigate to **Areas** → **Add Area**
- Select the venue
- Enter area name
- Upload a PDF floor plan
- Click **Create Area**

### 3. Calibrate the Plan

- Open the area
- The PDF will be displayed on a canvas
- Click **Calibrate** to set the scale
- Draw a reference line on a known distance
- Enter the actual distance in meters

### 4. Define Check Types

- Navigate to **Check Types** → **Add Check Type**
- Enter type name (e.g., "Electrical", "Fire Safety")
- Choose a color and icon
- Click **Create Check Type**

### 5. Add Check Points

- Open an area
- Click **Add Check Point**
- Click on the PDF where you want to place the check point
- Fill in the form:
  - Reference code (e.g., "E-001")
  - Label (e.g., "Main Circuit Breaker")
  - Check type
  - Periodicity (daily/weekly/monthly/quarterly/annually)
  - Optional notes
- Click **Add Check Point**

### 6. Perform Checks

- Open an area
- Check points are displayed as colored circles:
  - **Green** - Up to date
  - **Amber** - Due soon (within 24 hours)
  - **Red** - Overdue
  - **Grey** - Never checked
- Click on a check point
- Click **Pass** or **Fail**
- If fail, enter notes and severity

### 7. Manage Reports

- Navigate to **Reports**
- View all failure reports
- Filter by venue, severity, or status
- Click on a report to view details
- Update status, assign to users, or resolve

## Settings

Access settings via **Settings** in the sidebar (admin only):

- **Site Name** - Application name
- **Timezone** - Default timezone
- **Check Reminder Hours** - Hours before due to show amber warning
- **Allow Registration** - Enable/disable public registration

## Development

### File Structure

```
/
├── config/              # Database configuration
├── src/
│   ├── controllers/     # Request handlers
│   ├── models/          # Data models
│   ├── middleware/      # Auth and CSRF
│   └── views/           # PHP templates
├── public/              # Web root
│   ├── index.php        # Front controller
│   ├── .htaccess        # Apache rewrite rules
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript
│   └── uploads/        # PDF storage
└── database/           # Schema and migrations
```

### Adding New Features

1. Create model in `src/models/`
2. Create controller in `src/controllers/`
3. Add routes in `public/index.php`
4. Create views in `src/views/`

## Security

- All passwords are hashed with PHP's `password_hash()`
- CSRF tokens on all forms
- Prepared statements for all database queries
- File upload validation
- Role-based access control
- XSS protection via `htmlspecialchars()`

## Troubleshooting

### Cannot connect to database

- Check `config/database.local.php` credentials
- Verify MySQL is running
- Ensure database exists

### 404 errors on all pages

- Verify Apache mod_rewrite is enabled
- Check .htaccess is present in public directory
- Verify document root points to public directory

### PDF not rendering

- Check browser console for errors
- Verify PDF.js CDN is accessible
- Ensure PDF file exists in uploads directory

### Permission denied on uploads

```bash
chmod -R 755 public/uploads
chown -R www-data:www-data public/uploads
```

## License

Proprietary - All rights reserved

## Support

For technical support or questions, please contact your system administrator.
