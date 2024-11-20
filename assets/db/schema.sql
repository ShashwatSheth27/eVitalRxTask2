-- Table 1: User_roles
CREATE TABLE user_roles (
    id SERIAL PRIMARY KEY,
    role_name VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP
);

-- Table 2: Users
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    role_id INTEGER REFERENCES user_roles(id),
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone_number VARCHAR(15)
);

-- Table 3: Categories
CREATE TABLE categories (
    id SERIAL PRIMARY KEY,
    category_name VARCHAR(255) NOT NULL
);

-- Table 4: Products
CREATE TABLE products (
    id SERIAL PRIMARY KEY,
    product_name VARCHAR(255) NOT NULL,
    category_id INTEGER REFERENCES categories(id),
    description TEXT,
    price NUMERIC(10, 2) NOT NULL,
    stock_quantity INTEGER NOT NULL
);

-- Table 5: Shipping_addresses
CREATE TABLE shipping_addresses (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id),
    full_address TEXT NOT NULL,
    primary_address BOOLEAN DEFAULT FALSE,
    state VARCHAR(50),
    city VARCHAR(50),
    zip_code VARCHAR(10)
);

-- Table 6: Orders
CREATE TABLE orders (
    id SERIAL PRIMARY KEY,
    order_number VARCHAR(100) UNIQUE NOT NULL,
    user_id INTEGER REFERENCES users(id),
    -- total_amount NUMERIC(10, 2) DEFAULT 1500,
    -- discount_amount NUMERIC(10, 2) DEFAULT 100,
    -- gross_amount NUMERIC(10, 2) DEFAULT 1400,
    -- shipping_amount NUMERIC(10, 2) DEFAULT 50,
    net_amount NUMERIC(10, 2) DEFAULT 1450,
    order_status INT, --(status IN ('placed', 'processing', 'shipping', 'delivered')),
    payment_status INT, --(payment_status IN ('paid', 'in progress', 'not paid')),
    -- payment_type INT, --(payment_type IN ('netbanking', 'upi', 'cod')),
    -- payment_transaction_id VARCHAR(255)
);

-- Table 7: Order_products
CREATE TABLE order_products (
    id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(id),
    product_id INTEGER REFERENCES products(id),
    quantity INTEGER NOT NULL,
    total_amount NUMERIC(10, 2) NOT NULL
);

-- Table 8: Order_shipping_addresses
CREATE TABLE order_shipping_addresses (
    id SERIAL PRIMARY KEY,
    order_id INTEGER REFERENCES orders(id),
    shipping_address_id INTEGER REFERENCES shipping_addresses(id),
    full_address TEXT NOT NULL,
    state VARCHAR(50),
    city VARCHAR(50),
    zip_code VARCHAR(10)
);

CREATE TYPE role_type AS ENUM ('admin', 'customer');
CREATE TYPE order_status_enum AS ENUM ('placed', 'processing', 'shipping', 'delivered');
CREATE TYPE payment_status_enum AS ENUM ('paid', 'in progress', 'not paid');

ALTER TABLE order_products
ADD CONSTRAINT unique_product_per_order UNIQUE (order_id, product_id);





-- Table 1: User_roles
INSERT INTO user_roles (role_name) VALUES
('admin'),
('customer');

-- Table 2: Users
INSERT INTO users (role_id, full_name, email, password, phone_number) VALUES
(1, 'Admin User', 'admin@example.com', 'password123', '1234567890'),
(2, 'John Doe', 'john.doe@example.com', 'password123', '1234567891'),
(2, 'Jane Smith', 'jane.smith@example.com', 'password123', '1234567892'),
(2, 'Emily Brown', 'emily.brown@example.com', 'password123', '1234567893'),
(2, 'Michael Johnson', 'michael.johnson@example.com', 'password123', '1234567894'),
(2, 'Chris Lee', 'chris.lee@example.com', 'password123', '1234567895'),
(2, 'Sarah Wilson', 'sarah.wilson@example.com', 'password123', '1234567896'),
(2, 'David Moore', 'david.moore@example.com', 'password123', '1234567897'),
(2, 'Sophia Taylor', 'sophia.taylor@example.com', 'password123', '1234567898'),
(2, 'James Anderson', 'james.anderson@example.com', 'password123', '1234567899');

-- Table 3: Categories
INSERT INTO categories (category_name) VALUES
('Electronics'),
('Fashion'),
('Home Appliances'),
('Books'),
('Toys'),
('Sports'),
('Beauty'),
('Automotive'),
('Grocery'),
('Music');

-- Table 4: Products
INSERT INTO products (product_name, category_id, description, price, stock_quantity) VALUES
('Smartphone', 1, 'Latest model smartphone with advanced features', 499.99, 100),
('Laptop', 1, 'High-performance laptop for gaming and productivity', 899.99, 50),
('Washing Machine', 3, 'Efficient washing machine with multiple settings', 299.99, 20),
('Air Conditioner', 3, 'Cooling air conditioner with energy-saving mode', 399.99, 15),
('T-Shirt', 2, 'Comfortable cotton T-shirt', 19.99, 200),
('Running Shoes', 6, 'Lightweight running shoes', 49.99, 80),
('Guitar', 10, 'Acoustic guitar with excellent sound quality', 149.99, 30),
('Novel Book', 4, 'Bestselling novel with intriguing plot', 9.99, 120),
('Toy Car', 5, 'Toy car for kids', 14.99, 150),
('Face Cream', 7, 'Moisturizing face cream', 24.99, 75);

-- Table 5: Shipping_addresses
INSERT INTO shipping_addresses (user_id, full_address, primary_address, state, city, zip_code) VALUES
(2, '123 Main St, Apt 4B', TRUE, 'California', 'Los Angeles', '90001'),
(3, '456 Elm St, Suite 300', FALSE, 'Texas', 'Houston', '77001'),
(4, '789 Maple Ave, Unit 12', TRUE, 'New York', 'New York', '10001'),
(5, '321 Oak Dr, Floor 2', FALSE, 'Florida', 'Miami', '33101'),
(6, '654 Pine Ln, Apt 5C', TRUE, 'Illinois', 'Chicago', '60601'),
(7, '987 Cedar Blvd', FALSE, 'Pennsylvania', 'Philadelphia', '19101'),
(8, '135 Birch St, Apt 2A', TRUE, 'Georgia', 'Atlanta', '30301'),
(9, '246 Walnut Rd', FALSE, 'Ohio', 'Columbus', '43201'),
(10, '369 Spruce Ct', TRUE, 'Michigan', 'Detroit', '48201'),
(2, '1355 Ash Ave', FALSE, 'Virginia', 'Richmond', '23201');

-- Table 6: Orders
INSERT INTO orders (order_number, user_id, net_amount, order_status, payment_status) VALUES
('52431728', 2, 1200.00, 1, 1),
('84920651', 3, 800.00, 2, 2),
('37481062', 4, 450.00, 3, 3),
('29643578', 5, 1000.00, 1, 1),
('75861234', 6, 750.00, 2, 2),
('63041985', 7, 600.00, 3, 1),
('14578329', 8, 900.00, 1, 3),
('98165247', 9, 650.00, 2, 1),
('41728369', 10, 1400.00, 3, 2),
('20354871', 2, 1100.00, 1, 3);

-- Table 7: Order_products
INSERT INTO order_products (order_id, product_id, quantity, total_amount) VALUES
(1, 1, 2, 999.98),
(1, 3, 1, 299.99),
(2, 2, 1, 899.99),
(3, 5, 3, 59.97),
(4, 7, 1, 149.99),
(5, 4, 1, 399.99),
(6, 6, 2, 99.98),
(7, 10, 4, 99.96),
(8, 9, 3, 44.97),
(9, 8, 2, 19.98);

-- Table 8: Order_shipping_addresses
INSERT INTO order_shipping_addresses (order_id, shipping_address_id, full_address, state, city, zip_code) VALUES
(1, 1, '123 Main St, Apt 4B', 'California', 'Los Angeles', '90001'),
(2, 2, '456 Elm St, Suite 300', 'Texas', 'Houston', '77001'),
(3, 3, '789 Maple Ave, Unit 12', 'New York', 'New York', '10001'),
(4, 4, '321 Oak Dr, Floor 2', 'Florida', 'Miami', '33101'),
(5, 5, '654 Pine Ln, Apt 5C', 'Illinois', 'Chicago', '60601'),
(6, 6, '987 Cedar Blvd', 'Pennsylvania', 'Philadelphia', '19101'),
(7, 7, '135 Birch St, Apt 2A', 'Georgia', 'Atlanta', '30301'),
(8, 8, '246 Walnut Rd', 'Ohio', 'Columbus', '43201'),
(9, 9, '369 Spruce Ct', 'Michigan', 'Detroit', '48201'),
(10, 10, '1355 Ash Ave', 'Virginia', 'Richmond', '23201');
