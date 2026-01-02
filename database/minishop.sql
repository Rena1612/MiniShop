-- ============================================
-- MyShop Database Creation Script
-- Enhanced with more sample data
-- ============================================

-- Create database if it doesn't exist
CREATE DATABASE IF NOT EXISTS minishop;

-- Use the database
USE minishop;

-- ============================================
-- TABLE: categories
-- Stores product categories (e.g., Electronics, Clothing, Books)
-- ============================================
CREATE TABLE IF NOT EXISTS categories (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: products
-- Stores all product information
-- ============================================
CREATE TABLE IF NOT EXISTS products (
    id INT(11) NOT NULL AUTO_INCREMENT,
    category_id INT(11) NOT NULL,
    name VARCHAR(200) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    description TEXT,
    image VARCHAR(255) DEFAULT 'placeholder.jpg',
    stock INT(11) NOT NULL DEFAULT 0,
    featured TINYINT(1) DEFAULT 0 COMMENT '1 = featured on homepage, 0 = not featured',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: orders
-- Stores customer order information
-- ============================================
CREATE TABLE IF NOT EXISTS orders (
    id INT(11) NOT NULL AUTO_INCREMENT,
    customer_name VARCHAR(100) NOT NULL,
    customer_email VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- TABLE: order_items
-- Stores individual items in each order
-- ============================================
CREATE TABLE IF NOT EXISTS order_items (
    id INT(11) NOT NULL AUTO_INCREMENT,
    order_id INT(11) NOT NULL,
    product_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    PRIMARY KEY (id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================
-- SAMPLE DATA: Insert Categories
-- ============================================
INSERT INTO categories (name) VALUES
('Electronics'),
('Clothing'),
('Books'),
('Home & Garden'),
('Sports & Outdoors'),
('Toys & Games');

-- ============================================
-- SAMPLE DATA: Insert Products
-- Image files: product1.jpg, product2.jpg, etc.
-- ============================================
INSERT INTO products (category_id, name, price, description, image, stock, featured) VALUES
-- Electronics (Category 1)
(1, 'Wireless Bluetooth Headphones', 89.99, 'Premium wireless headphones with active noise cancellation, 30-hour battery life, and superior sound quality. Perfect for music lovers and professionals.', 'product1.jpg', 50, 1),
(1, 'Smart Watch Pro', 199.99, 'Advanced fitness tracking smart watch with heart rate monitor, GPS, sleep tracking, and smartphone notifications. Water-resistant up to 50 meters.', 'product2.jpg', 30, 1),
(1, 'Portable Bluetooth Speaker', 49.99, 'Compact waterproof Bluetooth speaker with 360-degree sound, 12-hour battery, and built-in microphone for hands-free calls.', 'product3.jpg', 75, 0),
(1, 'Wireless Gaming Mouse', 59.99, 'High-precision gaming mouse with customizable RGB lighting, 6 programmable buttons, and ergonomic design for extended gaming sessions.', 'product4.jpg', 45, 0),
(1, 'USB-C Fast Charger', 29.99, '65W USB-C fast charger compatible with laptops, tablets, and smartphones. Compact design perfect for travel.', 'product5.jpg', 100, 0),

-- Clothing (Category 2)
(2, 'Premium Cotton T-Shirt', 24.99, 'Ultra-soft 100% organic cotton t-shirt. Available in multiple colors. Perfect fit with reinforced stitching for durability.', 'product6.jpg', 120, 1),
(2, 'Classic Denim Jeans', 59.99, 'Timeless straight-fit denim jeans with stretch fabric for all-day comfort. Features classic 5-pocket design and durable construction.', 'product7.jpg', 80, 0),
(2, 'Cozy Fleece Hoodie', 44.99, 'Warm and comfortable fleece hoodie with kangaroo pocket and adjustable drawstring hood. Perfect for casual wear.', 'product8.jpg', 65, 1),
(2, 'Sport Running Shorts', 29.99, 'Lightweight athletic shorts with moisture-wicking fabric, zippered pocket, and built-in liner. Ideal for running and gym workouts.', 'product9.jpg', 90, 0),
(2, 'Winter Jacket', 89.99, 'Insulated winter jacket with waterproof outer shell, multiple pockets, and removable hood. Keeps you warm in cold weather.', 'product10.jpg', 40, 0),

-- Books (Category 3)
(3, 'The Complete Programming Guide', 45.00, 'Comprehensive 800-page guide covering modern programming languages, design patterns, algorithms, and best practices. Perfect for beginners and professionals.', 'product11.jpg', 35, 0),
(3, 'Mastering French Cuisine', 34.99, 'Learn authentic French cooking techniques from renowned chefs. Includes 200+ recipes with step-by-step instructions and beautiful photography.', 'product12.jpg', 50, 1),
(3, 'Mystery Thriller Collection', 39.99, 'Bestselling 3-book mystery collection that will keep you guessing until the last page. Perfect for mystery enthusiasts.', 'product13.jpg', 45, 0),
(3, 'Digital Photography Handbook', 29.99, 'Master digital photography with this comprehensive guide covering camera settings, composition, lighting, and post-processing techniques.', 'product14.jpg', 40, 0),

-- Home & Garden (Category 4)
(4, 'Modern LED Desk Lamp', 39.99, 'Sleek adjustable LED desk lamp with touch controls, 5 brightness levels, USB charging port, and eye-caring technology.', 'product15.jpg', 70, 0),
(4, 'Ceramic Plant Pot Set', 27.99, 'Beautiful set of 3 handcrafted ceramic plant pots with drainage holes and matching saucers. Perfect for indoor plants.', 'product16.jpg', 85, 0),
(4, 'Luxury Throw Blanket', 49.99, 'Ultra-soft microfiber throw blanket perfect for couch, bed, or travel. Machine washable and available in elegant colors.', 'product17.jpg', 60, 1),
(4, 'Stainless Steel Kitchen Knife Set', 79.99, 'Professional 8-piece knife set with ergonomic handles, razor-sharp blades, and wooden storage block.', 'product18.jpg', 35, 0),
(4, 'Aromatherapy Diffuser', 34.99, 'Ultrasonic essential oil diffuser with 7-color LED lights, 300ml capacity, and automatic shut-off. Creates a relaxing atmosphere.', 'product19.jpg', 95, 0),

-- Sports & Outdoors (Category 5)
(5, 'Premium Yoga Mat', 34.99, 'Extra-thick non-slip yoga mat with carrying strap. Eco-friendly TPE material provides excellent cushioning and grip.', 'product20.jpg', 75, 1),
(5, 'Insulated Water Bottle', 24.99, 'Double-wall vacuum insulated stainless steel bottle keeps drinks cold for 24 hours or hot for 12 hours. Leak-proof lid with carry handle.', 'product21.jpg', 150, 0),
(5, 'Professional Running Shoes', 89.99, 'Lightweight performance running shoes with responsive cushioning, breathable mesh upper, and durable rubber outsole.', 'product22.jpg', 55, 0),
(5, 'Camping Tent 4-Person', 149.99, 'Spacious waterproof camping tent with easy setup, ventilation windows, and storage pockets. Perfect for family camping trips.', 'product23.jpg', 25, 0),
(5, 'Adjustable Dumbbells Set', 119.99, 'Space-saving adjustable dumbbell set with weight range from 5 to 52.5 lbs per dumbbell. Perfect for home gym workouts.', 'product24.jpg', 30, 0),

-- Toys & Games (Category 6)
(6, 'Educational Building Blocks', 39.99, 'Creative 500-piece building block set that encourages imagination and develops fine motor skills. Suitable for ages 6+.', 'product25.jpg', 80, 0),
(6, 'Remote Control Race Car', 54.99, 'High-speed RC car with 2.4GHz remote control, rechargeable battery, and durable design. Reaches speeds up to 20 mph.', 'product26.jpg', 45, 1),
(6, 'Board Game Collection', 44.99, 'Family-friendly board game collection featuring 5 classic games. Perfect for game nights and gatherings.', 'product27.jpg', 60, 0),
(6, 'Art Supplies Set', 29.99, 'Complete 150-piece art set including colored pencils, markers, crayons, and sketch pad. Perfect for young artists.', 'product28.jpg', 70, 0),
(6, 'Interactive Learning Tablet', 79.99, 'Educational tablet for kids with preloaded learning apps, parental controls, and kid-proof case. Ages 3-9.', 'product29.jpg', 40, 0),
(6, 'Puzzle 1000 Pieces', 19.99, 'Challenging 1000-piece jigsaw puzzle featuring stunning landscape artwork. Great for relaxation and focus.', 'product30.jpg', 90, 0);

-- ============================================
-- VERIFICATION QUERIES
-- ============================================

-- Check all categories
SELECT * FROM categories;

-- Check count of products per category
SELECT c.name as category, COUNT(p.id) as product_count 
FROM categories c 
LEFT JOIN products p ON c.id = p.category_id 
GROUP BY c.id;

-- Check featured products (will show on homepage)
SELECT * FROM products WHERE featured = 1;

-- Check all products
SELECT p.id, p.name, p.price, c.name as category, p.image, p.stock 
FROM products p 
JOIN categories c ON p.category_id = c.id 
ORDER BY p.id;