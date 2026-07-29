-- LACSO Food Management System Database Schema
CREATE DATABASE IF NOT EXISTS lacso_db;
USE lacso_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('donor', 'volunteer', 'admin', 'ngo') NOT NULL,
    phone VARCHAR(20),
    city VARCHAR(50),
    points INT DEFAULT 0,
    co2_saved DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS donations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    donor_name VARCHAR(100),
    food_name VARCHAR(100) NOT NULL,
    quantity VARCHAR(50) NOT NULL,
    serves VARCHAR(50),
    expiry VARCHAR(50),
    category VARCHAR(50),
    pickup_address TEXT,
    drop_address TEXT,
    notes TEXT,
    image_path VARCHAR(255),
    status ENUM('Pending', 'Accepted', 'Collected', 'Delivered') DEFAULT 'Pending',
    volunteer_id INT DEFAULT NULL,
    volunteer_name VARCHAR(100) DEFAULT NULL,
    pickup_time VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT IGNORE INTO users (name, email, password, role) VALUES 
('Volunteer 1', 'volunteer1', 'vol123', 'volunteer'),
('Volunteer 2', 'volunteer2', 'vol123', 'volunteer'),
('Volunteer 3', 'volunteer3', 'vol123', 'volunteer'),
('Admin', 'admin', 'admin', 'admin');
