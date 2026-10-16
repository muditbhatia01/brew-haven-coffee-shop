# Brew Haven Coffee Ordering App

Beginner-friendly PHP + MySQL coffee shop ordering demo.

## Requirements
- Windows with XAMPP (Apache + MySQL)
- PHP 8.0+ recommended
- Browser and editor such as VS Code

## Install
1. Copy the `brew-haven` folder to `C:\\xampp\\htdocs\\brew-haven`.
2. Start Apache and MySQL in XAMPP.
3. Open `http://localhost/phpmyadmin/`.
4. Select the SQL tab and import/run `database.sql`.
5. Check `config/db.php`; the defaults assume XAMPP MySQL user `root` with an empty password.
6. Open `http://localhost/brew-haven/`.
7. Create an admin once by opening `http://localhost/brew-haven/setup_admin.php`. Then DELETE `setup_admin.php`.
8. Log in with `admin@brewhaven.local` / `AdminChangeMe123!`, then change the password before using this beyond a local demo.

## Features
- Customer registration and login with password hashing
- Session-based guest and member cart
- 10% discount for authenticated customer accounts (not guests or admin)
- Admin product create/read/update/deactivate
- Guest and member checkout
- Order history and admin order status management
- Server-side prices, prepared statements, output escaping, CSRF form tokens

## Notes
- This is a local educational demo. It records orders but does not accept online payments.
- Do not deploy with the default admin password or setup_admin.php present.
- For production, add rate limiting, email verification, HTTPS, secure cookie configuration, audit logging, and a proper payment provider.
- The starter product images are remote image URLs; internet access is needed to load them.
