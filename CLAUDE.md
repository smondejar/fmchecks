# FM Checks - Facilities Management Periodic Check System

## Project Overview
PHP-based facilities management system for periodic safety and maintenance checks.
Managers upload CAD floor plans (PDF), define check points on them (fixtures, electrical boards,
junctions, fire equipment, etc.), and staff walk the venue performing checks on a defined schedule.
Results are logged, failures are reported, and a full audit trail is maintained.

## Tech Stack
- PHP 8.x
- MySQL 8.x
- Vanilla JavaScript (no framework)
- HTML5 / CSS3
- PDF.js for rendering CAD plans on canvas

## Architecture Patterns
This project follows the same patterns as the VPS (Vehicle Parking System) codebase:

- **Front controller**: All requests route through `public/index.php` via switch/case on `$path` and `$method`
- **Static models**: All model classes use `public static` methods, no instantiation. `Database::connect()` singleton PDO.
- **Static controllers**: All controller methods are `public static`. Auth/permission checks first, then logic, then view.
- **PHP template views**: Views in `src/views/` with `header.php`/`footer.php` layout wrapper. Variables passed by scope.
- **CSRF protection**: `Csrf::field()` in forms, `Csrf::validate()` + `Csrf::regenerate()` in controllers.
- **Session flash messages**: `$_SESSION['flash_success']` and `$_SESSION['flash_error']`, displayed once then cleared.
- **Role-based permissions**: `Permission::can(action, resource)` and `Permission::requirePerm(action, resource)`.
- **Prepared statements everywhere**: No raw user input in queries. All queries use `?` placeholders.
- **Dark mode**: CSS class `.dark-mode` on `<html>`, toggled via localStorage and `app.js`.
- **PDF.js canvas rendering**: CAD plans rendered to canvas with content bounds detection and cropping.
- **Calibration system**: Two-point reference line on PDF, stored as fractional coordinates, used to compute px-to-metres scale.

## Database
- Config: `config/database.php` (singleton PDO, fallback to `database.local.php` then `database.example.php`)
- Schema: `database/schema.sql`
- Migrations: `database/migrations/` (numbered `NNN_description.sql`)

## File Structure

/
├── config/
│ ├── database.php # Database singleton class
│ └── database.example.php # Template credentials
├── src/
│ ├── controllers/
│ │ ├── AuthController.php
│ │ ├── VenueController.php
│ │ ├── AreaController.php
│ │ ├── CheckController.php
│ │ ├── CheckTypeController.php
│ │ ├── ReportController.php
│ │ ├── UserController.php
│ │ └── SettingsController.php
│ ├── models/
│ │ ├── User.php
│ │ ├── Permission.php
│ │ ├── Setting.php
│ │ ├── Venue.php
│ │ ├── Area.php
│ │ ├── CheckPoint.php # Defined check locations on a plan
│ │ ├── CheckType.php # User-defined types (electrical, fire, plumbing, etc.)
│ │ ├── CheckLog.php # Individual check results (pass/fail + notes)
│ │ └── Report.php # Failure reports register
│ ├── middleware/
│ │ ├── Auth.php
│ │ └── Csrf.php
│ └── views/
│ ├── layout/
│ │ ├── header.php # Sidebar + topbar + main wrapper
│ │ └── footer.php # Close wrappers + app.js
│ ├── login.php
│ ├── register.php
│ ├── dashboard.php
│ ├── help.php
│ ├── error.php
│ ├── venues/
│ │ ├── index.php
│ │ ├── show.php # Venue detail with assigned areas
│ │ └── form.php
│ ├── areas/
│ │ ├── index.php
│ │ ├── show.php # PDF plan with check point circles
│ │ └── form.php
│ ├── checks/
│ │ ├── index.php # Check log history
│ │ └── perform.php # Staff check-off interface
│ ├── check-types/
│ │ ├── index.php
│ │ └── form.php
│ ├── reports/
│ │ ├── index.php # Reports register
│ │ ├── show.php
│ │ └── form.php
│ ├── users/
│ │ ├── index.php
│ │ ├── form.php
│ │ └── permissions.php
│ └── settings/
│ └── index.php
├── public/
│ ├── index.php # Front controller / router
│ ├── .htaccess # Apache rewrite rules
│ ├── css/
│ │ └── style.css # All styles (dark mode, sidebar, forms, etc.)
│ ├── js/
│ │ └── app.js # Sidebar toggle, dark mode toggle
│ └── uploads/ # PDF plan storage
│ └── .gitkeep
├── database/
│ ├── schema.sql # Full schema for fresh install
│ └── migrations/ # Incremental changes
├── CLAUDE.md # This file
├── README.md
└── .gitignore


## Core Concepts

### Venues
- Top-level organisational unit (replaces "Events" from VPS)
- A venue is a building or site (e.g. "Main Building", "Warehouse A")
- No dates — venues are permanent
- Each venue has one or more assigned areas
- Fields: id, name, address, notes, created_by, created_at

### Areas (CAD Plans)
- Same as VPS: PDF upload, calibration, canvas rendering
- Instead of parking spaces, areas contain **check points** (small circles on the plan)
- Calibration stored as fractional coordinates (cal_x1, cal_y1, cal_x2, cal_y2, cal_distance_m)
- Fields: id, area_name, pdf_path, uploaded_by, cal_*, sort_order, created_at

### Check Points
- Defined locations on an area plan (drawn as small circles, not rectangles)
- Each has: reference code, label, check type, x/y position, periodicity
- Periodicity: how often the check must be performed (daily, weekly, monthly, quarterly, annually)
- Fields: id, area_id, reference, label, check_type_id, x_coord, y_coord, periodicity, notes, created_at
- Visual: small coloured circles on the canvas (green = up to date, amber = due soon, red = overdue, grey = not yet checked)

### Check Types
- User-defined categories (stored in DB, manageable via UI)
- Examples: Electrical, Fire Safety, Plumbing, HVAC, Structural, Security, Emergency Lighting, PAT
- Fields: id, name, colour, icon (optional), created_at
- Colour is used for the circle fill on the plan

### Check Logs
- The actual check records — one row per check performed
- Fields: id, check_point_id, performed_by (user_id), status (pass/fail), notes, photo_path (optional), performed_at
- Status: 'pass' or 'fail'
- When status is 'fail', a report can be auto-created or linked

### Reports Register
- Log of all failures and issues found during checks
- Fields: id, check_log_id (nullable — can be manual), check_point_id, venue_id, area_id, title, description, severity (low/medium/high/critical), status (open/in_progress/resolved/closed), assigned_to (user_id, nullable), resolved_at, resolved_by, resolution_notes, created_by, created_at
- Reports can exist independently of checks (manual reports)
- Track resolution workflow

### Periodicity Logic
- Each check point has a periodicity: daily, weekly, monthly, quarterly, annually
- The system calculates the next due date based on the last check log for that point
- Colour coding on the plan:
  - **Green**: checked within the current period
  - **Amber**: due within 24 hours (or approaching period end)
  - **Red**: overdue (past the period deadline)
  - **Grey**: never checked

## User Roles & Permissions
| Role    | Description | Permissions |
|---------|-------------|-------------|
| admin   | Full system access | All CRUD on everything |
| manager | Day-to-day management | CRUD venues, areas, check points, check types, reports. View users. |
| staff   | Perform checks on-site | View venues/areas/check points. Perform checks (create check logs). View reports. |
| viewer  | Read-only stakeholder | View everything, change nothing |

Permission resources: venues, areas, checks, check_types, reports, users

## Key Workflows

### Manager: Setup
1. Create a venue (e.g. "Exhibition Hall")
2. Upload area plans (PDF) and calibrate scale
3. Define check types (if new ones needed)
4. Place check points on the plan as circles — set reference, type, periodicity
5. Assign areas to the venue

### Staff: Performing Checks
1. Open the venue from the sidebar/dashboard
2. Open an area to see the plan with check point circles
3. Circles are colour-coded by status (green/amber/red/grey)
4. Click a circle to open a quick check dialog:
   - Shows: reference, label, type, last checked date, periodicity
   - Actions: **Pass** (one-click) or **Fail** (requires notes, optional photo)
5. Passing a check logs it immediately and turns the circle green
6. Failing a check logs it and creates a report entry

### Reports
1. All failures appear in the reports register
2. Managers can assign reports to users, update status, add resolution notes
3. Reports can be filtered by venue, area, severity, status, date range

## CSS Variables (match VPS palette)
```css
:root {
    --primary: #2563eb;
    --primary-dark: #1d4ed8;
    --primary-light: #dbeafe;
    --danger: #dc2626;
    --success: #16a34a;
    --warning: #d97706;
    --gray-50 through --gray-900;
    --sidebar-width: 240px;
    --topbar-height: 56px;
    --radius: 8px;
    --shadow: 0 1px 3px rgba(0,0,0,0.08);
}

Sidebar Navigation Order
Dashboard
Venues
---
Areas
Check Types
---
Checks (log/history)
Reports
---
[Admin section: Users, Permissions, Settings]
