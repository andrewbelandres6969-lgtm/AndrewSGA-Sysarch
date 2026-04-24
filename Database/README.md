# Database Import Options

Use the SQL file that matches how you want to set up the project.

## 1. Fresh clean database

File: `sit_in_system_clean.sql`

Use this when you want the easiest setup with no conflicts from old tables.

Creates:
- database `sit_in_system_clean`
- all required tables
- default settings
- starter lab records

After import, update [config.php](../config.php) to:

```php
$database = "sit_in_system_clean";
```

Then create the admin account by visiting:

`http://localhost/AndrewSGA-Sysarch/admin/create_admin.php`

Default admin credentials:
- username: `admin`
- password: `admin123`

## 2. Existing database without dropping tables

File: `sit_in_system_no_drop.sql`

Use this when your current `sit_in_system` database already has other tables and you want a safer import.

## 3. Existing database with full reset

File: `sit_in_system.sql`

Use this only when you are sure you want to replace the app tables in `sit_in_system`.
