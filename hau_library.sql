-- Creating the database
CREATE DATABASE IF NOT EXISTS hau_library;
USE hau_library;

-- Creating the 'books' table
CREATE TABLE IF NOT EXISTS books (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) NOT NULL,
    category VARCHAR(100) NOT NULL,
    publication_year INT(11) DEFAULT NULL,
    publisher VARCHAR(255) DEFAULT NULL,
    status ENUM('available', 'borrowed', 'reserved', 'maintenance') NOT NULL DEFAULT 'available',
    condition_status ENUM('Excellent', 'Good', 'Fair', 'Poor', 'Damaged') NOT NULL DEFAULT 'Good',
    shelf_location VARCHAR(50) NOT NULL,
    added_by INT(11) DEFAULT NULL,
    added_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_updated TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Creating the 'borrowings' table
CREATE TABLE IF NOT EXISTS borrowings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    book_id INT(11) NOT NULL,
    user_id INT(11) NOT NULL,
    student_id INT(11) NOT NULL,
    borrow_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    returned TINYINT(1) DEFAULT 0,
    condition_before ENUM('Excellent', 'Good', 'Fair', 'Poor', 'Damaged') DEFAULT NULL,
    condition_after ENUM('Excellent', 'Good', 'Fair', 'Poor', 'Damaged') DEFAULT NULL,
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    fine_paid TINYINT(1) DEFAULT 0,
    notes TEXT DEFAULT NULL,
    handled_by INT(11) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Creating the 'condition_logs' table
CREATE TABLE IF NOT EXISTS condition_logs (
    id INT(11) NOT NULL AUTO_INCREMENT,
    book_id INT(11) NOT NULL,
    previous_condition ENUM('Excellent', 'Good', 'Fair', 'Poor', 'Damaged') DEFAULT NULL,
    new_condition ENUM('Excellent', 'Good', 'Fair', 'Poor', 'Damaged') DEFAULT NULL,
    checked_by INT(11) NOT NULL,
    notes TEXT DEFAULT NULL,
    checked_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- Creating the 'settings' table
CREATE TABLE IF NOT EXISTS settings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    system_name VARCHAR(255) NOT NULL DEFAULT 'HAU Library Management System',
    PRIMARY KEY (id)
);

-- Creating the 'students' table
CREATE TABLE IF NOT EXISTS students (
    id INT(11) NOT NULL AUTO_INCREMENT,
    student_id VARCHAR(20) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    course VARCHAR(100) NOT NULL,
    year_level INT(11) NOT NULL,
    contact_number VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    department VARCHAR(50) NOT NULL,
    PRIMARY KEY (id)
);

-- Creating the 'users' table
CREATE TABLE IF NOT EXISTS users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role ENUM('admin', 'librarian', 'student_assistant') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

-- For borrowings table, linking book_id and user_id to books and users table
-- ALTER TABLE borrowings ADD CONSTRAINT fk_books FOREIGN KEY (book_id) REFERENCES books(id);
-- ALTER TABLE borrowings ADD CONSTRAINT fk_users FOREIGN KEY (user_id) REFERENCES users(id);
-- ALTER TABLE borrowings ADD CONSTRAINT fk_students FOREIGN KEY (student_id) REFERENCES students(id);
