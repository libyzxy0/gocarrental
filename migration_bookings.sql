-- Run this ONLY if you already imported database.sql before (adds the bookings table)
USE gocarrental;
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
