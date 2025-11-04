CREATE DATABASE IF NOT EXISTS clickcart CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clickcart;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  email VARCHAR(255) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('buyer','seller','admin') DEFAULT 'seller',
  brand_name VARCHAR(255),
  brand_logo VARCHAR(500),
  profile_image VARCHAR(500),
  bio TEXT,
  phone VARCHAR(50),
  total_sales INT DEFAULT 0,
  platform_fee_paid TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS password_resets (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  token VARCHAR(255) NOT NULL,
  expires_at DATETIME NOT NULL,
  used_at DATETIME NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  INDEX (token)
);

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(150) UNIQUE,
  parent_id INT DEFAULT NULL,
  FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  seller_id INT NOT NULL,
  sku VARCHAR(100),
  title VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL,
  sale_price DECIMAL(10,2) DEFAULT NULL,
  quantity INT DEFAULT 0,
  status ENUM('published','draft') DEFAULT 'published',
  category_id INT DEFAULT NULL,
  weight DECIMAL(10,2) DEFAULT NULL,
  dimensions VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS product_images (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT,
  image_url VARCHAR(500),
  is_primary TINYINT(1) DEFAULT 0,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_number VARCHAR(50) UNIQUE,
  buyer_id INT,
  total_amount DECIMAL(10,2),
  platform_fee DECIMAL(10,2) DEFAULT 0,
  payment_method VARCHAR(50),
  payment_status ENUM('pending','paid','failed') DEFAULT 'pending',
  status ENUM('new','processing','shipped','delivered','cancelled') DEFAULT 'new',
  shipping_address TEXT,
  shipping_phone VARCHAR(50),
  shipping_courier VARCHAR(100) NULL,
  tracking_number VARCHAR(100) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT,
  product_id INT,
  seller_id INT,
  qty INT,
  price DECIMAL(10,2),
  platform_fee_applied TINYINT(1) DEFAULT 0,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id),
  FOREIGN KEY (seller_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS payouts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  seller_id INT,
  amount DECIMAL(10,2),
  status ENUM('pending','completed') DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (name, email, password, role) VALUES
('Platform Admin', 'admin@clickcart.pk', '$2y$10$8i6B7Pj8G9mV6YxZL8prU.nwGg1Ych7R3oi5v4SjFFBXd5pRlJuyi', 'admin');

INSERT INTO users (name, email, password, role, brand_name, bio, phone) VALUES
('Ayesha Khan', 'seller@clickcart.pk', '$2y$10$8i6B7Pj8G9mV6YxZL8prU.nwGg1Ych7R3oi5v4SjFFBXd5pRlJuyi', 'seller', 'Ayesha Crafts', 'Handcrafted decor from Lahore.', '+92 300 1112233');

INSERT INTO users (name, email, password, role, phone) VALUES
('Bilal Ahmed', 'buyer@clickcart.pk', '$2y$10$8i6B7Pj8G9mV6YxZL8prU.nwGg1Ych7R3oi5v4SjFFBXd5pRlJuyi', 'buyer', '+92 300 4445566');

INSERT INTO categories (name, slug) VALUES
('Home & Living', 'home-living'),
('Jewellery', 'jewellery');

INSERT INTO products (seller_id, sku, title, description, price, sale_price, quantity, status, category_id, weight, dimensions) VALUES
(2, 'CC-0001', 'Handmade Cushion Cover', 'Premium cotton cushion cover with traditional patterns.', 1200.00, 999.00, 25, 'published', 1, 0.50, '40x40 cm');

INSERT INTO product_images (product_id, image_url, is_primary) VALUES
(1, '/assets/img/placeholder.svg', 1);
