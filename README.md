# Go Car Rental (PHP + MySQL, XAMPP)

## Setup
1. Copy this folder `gocarrental` into `C:\xampp\htdocs\`
2. Start **Apache** and **MySQL** in XAMPP Control Panel
3. Open http://localhost/phpmyadmin -> Import -> choose `database.sql` -> Go
4. Open http://localhost/gocarrental/

## Admin login
- Email: admin@gocar.test
- Password: admin123  (change it in Account Details!)
- Admin panel: http://localhost/gocarrental/admin/

## Config
Edit `config.php` if your folder name or DB credentials differ (BASE_URL, DB_USER, DB_PASS).
If uploads fail, make sure `uploads/` is writable and upload_max_filesize >= 5M in php.ini.

## Booking update
Already imported database.sql before? Import `migration_bookings.sql` too (creates the `bookings` table).
Fresh install: `database.sql` already includes it.
ID photos are saved in `private_uploads/` (blocked from the web by .htaccess).

## Favorites + reviews update
Import `migration_favorites_reviews.sql` once (needs the bookings table first).
Admin: /gocarrental/admin/bookings.php - set a booking to *completed* so the customer can write a review.
