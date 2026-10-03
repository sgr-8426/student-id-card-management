CREATE DATABASE IF NOT EXISTS student_id_cards;

USE student_id_cards;

CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(50) PRIMARY KEY,
    setting_value TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_no VARCHAR(20) NOT NULL,
    name VARCHAR(150) NOT NULL,
    dob DATE NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    address TEXT NOT NULL,
    branch VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL,
    blood_group VARCHAR(5) NOT NULL,
    photo VARCHAR(255) NULL,
    is_printed TINYINT(1) NOT NULL DEFAULT 0,
    UNIQUE(enrollment_no),
    UNIQUE(mobile)
);

INSERT IGNORE INTO settings (setting_key, setting_value) VALUES 
('college_name', 'Sagar Institute of Tech');

-- dummy data
INSERT IGNORE INTO students (enrollment_no, name, dob, mobile, address, branch, course, blood_group, photo, is_printed) VALUES 
('202300000001', 'Aarav Kumar Sharma', '2001-05-14', '9876543210', '123, Residency Society, Main Road, Mumbai', 'Computer Engineering', 'Bachelor of Engineering', 'B+', NULL, 0),
('202300000002', 'Priya Rani Patel', '2002-11-20', '9876543211', '45, Green Park Avenue, Navrangpura, Ahmedabad', 'Information Technology', 'Bachelor of Engineering', 'O+', NULL, 1),
('202300000003', 'Rohan Singh Verma', '2000-08-05', '9876543212', '789, MG Road, Pune', 'Mechanical Engineering', 'Diploma', 'A+', NULL, 0),
('202300000004', 'Sneha Kumari Gupta', '2003-02-15', '9876543213', '12, C-Scheme, Jaipur', 'Civil Engineering', 'Bachelor of Engineering', 'AB+', NULL, 0),
('202300000005', 'Vikram Dev Reddy', '1999-12-10', '9876543214', '55, Jubilee Hills, Hyderabad', 'Electronics & Communication Engineering', 'Master of Engineering', 'B-', NULL, 1);