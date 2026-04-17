-- Create database
CREATE DATABASE inventory_db;
USE inventory_db;

-- Creating products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    current_stock INT NOT NULL DEFAULT 0,
    price DECIMAL(10,2) NOT NULL
);

-- Creating orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_number VARCHAR(50) NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Inserting sample products 
INSERT INTO products (product_name, current_stock, price) VALUES
('Laptop', 50, 55	999.99),
('Mouse', 200, 1499.99),
('Keyboard', 75, 4899.99),
('Monitor', 30, 9999.99),
('USB Cable', 500, 299.99);

-- Inserting sample orders
INSERT INTO orders (order_number, product_id, quantity) VALUES
('ORD-001', 1, 5),
('ORD-002', 2, 10),
('ORD-003', 3, 3);

-- Create TRIGGER that automatically updates stock when order is inserted
DELIMITER //
CREATE TRIGGER update_stock_after_order
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    UPDATE products 
    SET current_stock = current_stock - NEW.quantity
    WHERE id = NEW.product_id;
END//
DELIMITER ;

-- Verify trigger is created
SHOW TRIGGERS;

-- Updating stocks
UPDATE products 
SET current_stock = 50 
WHERE id = 1;

-- STEP 1: Customer places an order
INSERT INTO orders (order_number, product_id, quantity) 
VALUES ('ORD-100', 1, 3);

-- STEP 2: Trigger automatically runs
-- It finds the product with id = 1 (Laptop)

-- STEP 3: Trigger updates stock
UPDATE products
SET current_stock = current_stock - 3 
WHERE id = 1;

-- RESULT: Laptop stock decreases from 50 to 47
