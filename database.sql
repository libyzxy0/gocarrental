CREATE DATABASE IF NOT EXISTS gocarrental CHARACTER SET utf8mb4;
USE gocarrental;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  phone VARCHAR(30) DEFAULT NULL,
  address VARCHAR(255) DEFAULT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('user','admin') NOT NULL DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cars (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  category ENUM('Economy','Compact','SUV','Van','Luxury','Sports') NOT NULL,
  price_per_day DECIMAL(10,2) NOT NULL,
  transmission VARCHAR(30) NOT NULL DEFAULT 'Automatic',
  seats INT NOT NULL DEFAULT 5,
  fuel VARCHAR(30) NOT NULL DEFAULT 'Gasoline',
  description TEXT,
  location VARCHAR(120) DEFAULT NULL,
  image VARCHAR(255) DEFAULT NULL,
  badge VARCHAR(30) DEFAULT NULL,         -- e.g. 'Best Seller', 'Popular'
  featured TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: admin@gocar.test / admin123  (change after first login!)
INSERT IGNORE INTO users (full_name,email,password,role) VALUES
('Admin','admin@gocar.test','$2y$10$08gVyI9mrwkFYmvKHDtAt.mvBMrBVM.nIPpj4hM/DrkVGqUOYByuS','admin');

INSERT INTO cars (name,category,price_per_day,transmission,seats,fuel,description,location,badge,featured)
SELECT * FROM (
 SELECT 'Toyota Vios' a,'Economy' b,1500 c,'Automatic' d,5 e,'Gasoline' f,'Reliable and fuel-efficient sedan, perfect for city drives and long trips.' g,'Makati' h,'Best Seller' i,1 j
 UNION ALL SELECT 'Honda CR-V','SUV',3000,'Automatic',5,'Gasoline','Spacious and comfortable SUV for the whole family.','Quezon City','Popular',1
 UNION ALL SELECT 'Toyota Fortuner','SUV',3500,'Automatic',7,'Diesel','Rugged 7-seater SUV for group trips.','Clark',NULL,1
 UNION ALL SELECT 'Ford Mustang','Sports',8000,'Automatic',4,'Gasoline','Feel the thrill of a classic muscle car.','Makati','Best Seller',1
 UNION ALL SELECT 'Toyota Hiace','Van',4500,'Automatic',12,'Diesel','Big van for group travel.','Angeles City',NULL,0
) t WHERE NOT EXISTS (SELECT 1 FROM cars);

-- Bookings
CREATE TABLE IF NOT EXISTS bookings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reference VARCHAR(20) NOT NULL UNIQUE,
  user_id INT NOT NULL,
  car_id INT NOT NULL,
  pickup_date DATE NOT NULL,
  dropoff_date DATE NOT NULL,
  pickup_location VARCHAR(150) NOT NULL,
  dropoff_location VARCHAR(150) NOT NULL,
  days INT NOT NULL,
  price_per_day DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  id_type VARCHAR(40) NOT NULL,
  id_file VARCHAR(255) NOT NULL,
  payment_method VARCHAR(30) NOT NULL,
  payment_status ENUM('pending','paid') NOT NULL DEFAULT 'pending',
  status ENUM('confirmed','completed','cancelled') NOT NULL DEFAULT 'confirmed',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE RESTRICT
);

-- Favorites + reviews
CREATE TABLE IF NOT EXISTS favorites (
  user_id INT NOT NULL,
  car_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, car_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE
);
CREATE TABLE IF NOT EXISTS reviews (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  car_id INT NOT NULL,
  booking_id INT NOT NULL UNIQUE,
  rating TINYINT NOT NULL,
  comment TEXT,
  anonymous TINYINT(1) NOT NULL DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (car_id) REFERENCES cars(id) ON DELETE CASCADE,
  FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE
);
