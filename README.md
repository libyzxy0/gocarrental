# Go Car Rental (PHP + MySQL, XAMPP)

## Setup

1. Copy the `gocarrental` folder into:

   `C:\xampp\htdocs\`

2. Start **Apache** and **MySQL** in XAMPP Control Panel.

3. Open http://localhost/phpmyadmin.

4. Go to **Import** and choose:

   `final_database.sql`

5. Click **Go** to import the database.

6. Open:

   http://localhost/gocarrental/

## Admin Login

* **Email:** `admin@gocar.test`
* **Password:** `admin123`

> Change the admin password after the first login.

Admin panel:

http://localhost/gocarrental/admin/

## Configuration

Edit `config.php` if your folder name or database credentials are different.

Main settings include:

* `BASE_URL`
* `DB_USER`
* `DB_PASS`

The default database name is:

`gocarrental`

## File Uploads

If file uploads fail:

* Make sure the `uploads/` folder is writable.
* Make sure `upload_max_filesize` is at least `5M` in `php.ini`.

Customer ID photos are stored in:

`private_uploads/`

The directory is blocked from direct web access using `.htaccess`.

## Database

The complete database setup is contained in:

`final_database.sql`

It includes:

* Users
* Admin accounts
* Cars
* Bookings
* Favorites
* Reviews

For a fresh installation, **only `final_database.sql` needs to be imported**.