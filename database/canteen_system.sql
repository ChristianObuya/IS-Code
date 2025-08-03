-- Use the correct database
DROP DATABASE IF EXISTS campusbite;
CREATE DATABASE campusbite;
USE campusbite;

-- Table: Users (Base entity)
CREATE TABLE Users (
    userID INT PRIMARY KEY,           -- Now set by user during registration (e.g., student/staff ID)
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    passwordHash VARCHAR(255) NOT NULL,
    role ENUM('student', 'staff') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: Student
CREATE TABLE Student (
    studentID INT PRIMARY KEY,
    FOREIGN KEY (studentID) REFERENCES Users(userID) ON DELETE CASCADE
);

-- Table: CanteenStaff
CREATE TABLE CanteenStaff (
    staffID INT PRIMARY KEY,
    FOREIGN KEY (staffID) REFERENCES Users(userID) ON DELETE CASCADE
);

-- Table: MenuItem
CREATE TABLE MenuItem (
    itemID INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    category VARCHAR(50),
    available TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table: Inventory
CREATE TABLE Inventory (
    itemID INT PRIMARY KEY,
    stockQuantity INT NOT NULL,
    lowStockThreshold INT DEFAULT 5,
    FOREIGN KEY (itemID) REFERENCES MenuItem(itemID) ON DELETE CASCADE
);

-- Table: `Order`
CREATE TABLE `Order` (
    orderID INT AUTO_INCREMENT PRIMARY KEY,
    studentID INT NOT NULL,
    totalAmount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'preparing', 'ready', 'collected') DEFAULT 'pending',
    orderTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updatedByStaffID INT,
    FOREIGN KEY (studentID) REFERENCES Student(studentID),
    FOREIGN KEY (updatedByStaffID) REFERENCES CanteenStaff(staffID)
);

-- Table: OrderItem
CREATE TABLE OrderItem (
    orderID INT,
    itemID INT,
    quantity INT NOT NULL,
    PRIMARY KEY (orderID, itemID),
    FOREIGN KEY (orderID) REFERENCES `Order`(orderID) ON DELETE CASCADE,
    FOREIGN KEY (itemID) REFERENCES MenuItem(itemID)
);

-- Table: Receipt
CREATE TABLE Receipt (
    receiptID INT AUTO_INCREMENT PRIMARY KEY,
    orderID INT UNIQUE NOT NULL,
    totalAmount DECIMAL(10,2) NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (orderID) REFERENCES `Order`(orderID) ON DELETE CASCADE
);