CREATE DATABASE IF NOT EXISTS barberhub;
USE barberhub;

-- Table for all users (Admin, Barber, Customer)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'barber', 'customer') NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    city VARCHAR(50) NOT NULL,
    profile_pic VARCHAR(255),
    status ENUM('pending', 'active', 'rejected') DEFAULT 'active',
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table for Barber specific details
CREATE TABLE IF NOT EXISTS barber_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    age INT,
    bio TEXT,
    experience_years INT,
    salon_name VARCHAR(100),
    salon_type ENUM('men', 'women', 'unisex') NOT NULL,
    work_start TIME,
    work_end TIME,
    lat DECIMAL(10, 8),
    lon DECIMAL(11, 8),
    salon_logo VARCHAR(255),
    salon_img VARCHAR(255),
    diploma_or_video VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for Services offered by Barbers
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    barber_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    duration INT NOT NULL, -- in minutes
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    FOREIGN KEY (barber_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for Bookings
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    barber_id INT NOT NULL,
    booking_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    status ENUM('pending', 'accepted', 'rejected', 'completed', 'no-show') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (barber_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for Services included in a Booking
CREATE TABLE IF NOT EXISTS booking_services (
    booking_id INT NOT NULL,
    service_id INT NOT NULL,
    PRIMARY KEY (booking_id, service_id),
    FOREIGN KEY (booking_id) REFERENCES bookings(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
);

-- Table for Reviews
CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    barber_id INT NOT NULL,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    image VARCHAR(255),
    likes INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (barber_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table for Blacklisted Emails
CREATE TABLE IF NOT EXISTS blacklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) UNIQUE NOT NULL,
    reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert Default Admin (plain-text password)
INSERT INTO users (full_name, email, phone, password, role, gender, city, status) 
VALUES ('Main Admin', 'admin@barberhub.com', '0600000000', 'Admin_00393690', 'admin', 'male', 'Casablanca', 'active');
