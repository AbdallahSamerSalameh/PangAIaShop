-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 25, 2025 at 12:12 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pangaia_shop`
--

DELIMITER $$
--
-- Procedures
--
DROP PROCEDURE IF EXISTS `AddClothingVariants`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `AddClothingVariants` ()   BEGIN
  DECLARE i INT DEFAULT 1;
  DECLARE variant_id INT DEFAULT 1;
  DECLARE size_var VARCHAR(10);
  DECLARE color_var VARCHAR(20);
  
  -- For Men's and Women's Clothing (product_id 1-50)
  WHILE i <= 70 DO
    -- Skip non-clothing items
    IF NOT (i > 60 AND i < 71) THEN
      -- For each product, create size variants
      -- Small size
      INSERT INTO product_variants (id, product_id, sku, name, price_adjustment, attributes, image_url)
      VALUES (
        variant_id,
        i,
        CONCAT((SELECT sku FROM products WHERE id = i), '-S'),
        CONCAT((SELECT name FROM products WHERE id = i), ' - Small'),
        -5.00, -- Smaller sizes often cost less
        JSON_OBJECT('size', 'S', 'color', 'Default'),
        CONCAT('https://pangaiashop.com/images/products/', i, '/variant', variant_id, '.jpg')
      );
      SET variant_id = variant_id + 1;
      
      -- Medium size
      INSERT INTO product_variants (id, product_id, sku, name, price_adjustment, attributes, image_url)
      VALUES (
        variant_id,
        i,
        CONCAT((SELECT sku FROM products WHERE id = i), '-M'),
        CONCAT((SELECT name FROM products WHERE id = i), ' - Medium'),
        0.00, -- Standard price
        JSON_OBJECT('size', 'M', 'color', 'Default'),
        CONCAT('https://pangaiashop.com/images/products/', i, '/variant', variant_id, '.jpg')
      );
      SET variant_id = variant_id + 1;
      
      -- Large size
      INSERT INTO product_variants (id, product_id, sku, name, price_adjustment, attributes, image_url)
      VALUES (
        variant_id,
        i,
        CONCAT((SELECT sku FROM products WHERE id = i), '-L'),
        CONCAT((SELECT name FROM products WHERE id = i), ' - Large'),
        0.00, -- Standard price
        JSON_OBJECT('size', 'L', 'color', 'Default'),
        CONCAT('https://pangaiashop.com/images/products/', i, '/variant', variant_id, '.jpg')
      );
      SET variant_id = variant_id + 1;
      
      -- X-Large size
      INSERT INTO product_variants (id, product_id, sku, name, price_adjustment, attributes, image_url)
      VALUES (
        variant_id,
        i,
        CONCAT((SELECT sku FROM products WHERE id = i), '-XL'),
        CONCAT((SELECT name FROM products WHERE id = i), ' - X-Large'),
        5.00, -- Larger sizes often cost more
        JSON_OBJECT('size', 'XL', 'color', 'Default'),
        CONCAT('https://pangaiashop.com/images/products/', i, '/variant', variant_id, '.jpg')
      );
      SET variant_id = variant_id + 1;
    END IF;
    
    SET i = i + 1;
  END WHILE;
  
  -- Add color variants for selected products (every 5th product)
  SET i = 1;
  WHILE i <= 150 DO
    IF i % 5 = 0 THEN
      -- Add Black variant
      INSERT INTO product_variants (id, product_id, sku, name, price_adjustment, attributes, image_url)
      VALUES (
        variant_id,
        i,
        CONCAT((SELECT sku FROM products WHERE id = i), '-BLACK'),
        CONCAT((SELECT name FROM products WHERE id = i), ' - Black'),
        10.00, -- Premium color
        JSON_OBJECT('size', 'M', 'color', 'Black'),
        CONCAT('https://pangaiashop.com/images/products/', i, '/variant', variant_id, '.jpg')
      );
      SET variant_id = variant_id + 1;
      
      -- Add Blue variant
      INSERT INTO product_variants (id, product_id, sku, name, price_adjustment, attributes, image_url)
      VALUES (
        variant_id,
        i,
        CONCAT((SELECT sku FROM products WHERE id = i), '-BLUE'),
        CONCAT((SELECT name FROM products WHERE id = i), ' - Blue'),
        5.00, -- Colored variant
        JSON_OBJECT('size', 'M', 'color', 'Blue'),
        CONCAT('https://pangaiashop.com/images/products/', i, '/variant', variant_id, '.jpg')
      );
      SET variant_id = variant_id + 1;
    END IF;
    SET i = i + 1;
  END WHILE;
  
  -- Add inventory for variants
  INSERT INTO inventories (product_id, variant_id, quantity, reserved_quantity, location, last_restocked, low_stock_threshold, updated_by)
  SELECT 
    product_id,
    id as variant_id,
    FLOOR(20 + RAND() * 80) as quantity, -- Random quantity between 20 and 99
    FLOOR(RAND() * 10) as reserved_quantity, -- Random reserved between 0 and 9
    CASE 
      WHEN product_id % 3 = 0 THEN 'Warehouse A'
      WHEN product_id % 3 = 1 THEN 'Warehouse B'
      ELSE 'Warehouse C'
    END as location,
    DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 30) DAY) as last_restocked, -- Restocked within last 30 days
    5 as low_stock_threshold,
    1 as updated_by
  FROM product_variants;
END$$

DROP PROCEDURE IF EXISTS `CreateOrders`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `CreateOrders` ()   BEGIN
  DECLARE i INT DEFAULT 1;
  DECLARE j INT DEFAULT 0;
  DECLARE order_count INT;
  DECLARE user_count INT;
  DECLARE product_count INT;
  DECLARE max_order_count INT DEFAULT 25; -- Total number of orders to create
  DECLARE current_user_id INT;
  DECLARE current_order_id INT DEFAULT 1;
  DECLARE current_order_item_id INT DEFAULT 1;
  DECLARE current_payment_id INT DEFAULT 1;
  DECLARE current_shipment_id INT DEFAULT 1;
  DECLARE promo_id INT;
  DECLARE order_status VARCHAR(20);
  DECLARE payment_status VARCHAR(20);
  DECLARE order_date TIMESTAMP;
  DECLARE shipping_country CHAR(2);
  DECLARE subtotal DECIMAL(10,2);
  DECLARE discount DECIMAL(10,2);
  DECLARE shipping_cost DECIMAL(10,2);
  DECLARE total DECIMAL(10,2);
  DECLARE admin_id INT;
  DECLARE item_count INT;
  DECLARE current_product_id INT;
  DECLARE current_variant_id INT;
  DECLARE use_variant BOOLEAN;
  DECLARE item_price DECIMAL(10,2);
  DECLARE item_quantity INT;
  DECLARE price_adj DECIMAL(10,2);
  
  -- Get counts
  SELECT COUNT(*) INTO user_count FROM users;
  SELECT COUNT(*) INTO product_count FROM products;
  
  WHILE i <= max_order_count DO
    -- Select a random user for this order
    SET current_user_id = FLOOR(1 + RAND() * user_count);
    IF current_user_id < 1 THEN SET current_user_id = 1; END IF;
    IF current_user_id > user_count THEN SET current_user_id = user_count; END IF;
    
    -- Random order date within last 6 months
    SET order_date = DATE_SUB(NOW(), INTERVAL FLOOR(RAND() * 180) DAY);
    
    -- Random shipping country (mostly US)
    SET shipping_country = CASE 
      WHEN RAND() < 0.7 THEN 'US'
      WHEN RAND() < 0.8 THEN 'CA'
      WHEN RAND() < 0.9 THEN 'GB'
      ELSE 'DE'
    END;
    
    -- Random promo code (sometimes)
    SET promo_id = CASE WHEN RAND() < 0.3 THEN FLOOR(1 + RAND() * 5) ELSE NULL END;
    
    -- Calculate base subtotal
    SET subtotal = 100 + RAND() * 500; -- Random subtotal between $100 and $600
    
    -- Calculate discount if promo applied
    SET discount = CASE 
      WHEN promo_id IS NOT NULL THEN subtotal * (5 + RAND() * 20) / 100 -- 5-25% discount
      ELSE 0
    END;
    
    -- Calculate shipping
    SET shipping_cost = CASE 
      WHEN subtotal > 200 THEN 0 -- Free shipping over $200
      ELSE 10 + RAND() * 15 -- $10-$25 shipping
    END;
    
    -- Calculate total
    SET total = subtotal - discount + shipping_cost;
    
    -- Determine order status based on date
    SET order_status = CASE
      WHEN order_date > DATE_SUB(NOW(), INTERVAL 2 DAY) THEN 'pending'
      WHEN order_date > DATE_SUB(NOW(), INTERVAL 5 DAY) THEN 'processing'
      WHEN order_date > DATE_SUB(NOW(), INTERVAL 10 DAY) THEN 'shipped'
      WHEN RAND() < 0.1 THEN 'cancelled' -- 10% chance of cancelled
      WHEN RAND() < 0.05 THEN 'returned' -- 5% chance of returned
      ELSE 'delivered'
    END;
    
    -- Assign a random admin to handle order
    SET admin_id = FLOOR(1 + RAND() * 9); -- Admin 1-9 (not including super admin)
    
    -- Insert the order
    INSERT INTO orders (
      id, user_id, order_number, shipping_street, shipping_city, shipping_state, 
      shipping_postal_code, shipping_country, billing_street, billing_city, 
      billing_state, billing_postal_code, billing_country, total_amount,
      subtotal, shipping, order_date, status, discount, promo_code_id, 
      expected_delivery_date, admin_notes, handled_by
    )
    VALUES (
      current_order_id,
      current_user_id,
      CONCAT('ORD-', DATE_FORMAT(order_date, '%Y%m'), '-', LPAD(current_order_id, 5, '0')),
      CASE 
        WHEN shipping_country = 'US' THEN CONCAT(FLOOR(100 + RAND() * 9900), ' Main St')
        WHEN shipping_country = 'CA' THEN CONCAT(FLOOR(100 + RAND() * 9900), ' Maple Ave')
        WHEN shipping_country = 'GB' THEN CONCAT(FLOOR(1 + RAND() * 100), ' High Street')
        ELSE CONCAT(FLOOR(1 + RAND() * 100), ' Hauptstrasse')
      END,
      CASE 
        WHEN shipping_country = 'US' THEN ELT(FLOOR(1 + RAND() * 5), 'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix')
        WHEN shipping_country = 'CA' THEN ELT(FLOOR(1 + RAND() * 5), 'Toronto', 'Vancouver', 'Montreal', 'Calgary', 'Ottawa')
        WHEN shipping_country = 'GB' THEN ELT(FLOOR(1 + RAND() * 5), 'London', 'Manchester', 'Birmingham', 'Liverpool', 'Edinburgh')
        ELSE ELT(FLOOR(1 + RAND() * 5), 'Berlin', 'Munich', 'Hamburg', 'Frankfurt', 'Cologne')
      END,
      CASE 
        WHEN shipping_country = 'US' THEN ELT(FLOOR(1 + RAND() * 5), 'NY', 'CA', 'IL', 'TX', 'AZ')
        WHEN shipping_country = 'CA' THEN ELT(FLOOR(1 + RAND() * 5), 'ON', 'BC', 'QC', 'AB', 'ON')
        WHEN shipping_country = 'GB' THEN ELT(FLOOR(1 + RAND() * 5), 'London', 'Greater Manchester', 'West Midlands', 'Merseyside', 'Scotland')
        ELSE ELT(FLOOR(1 + RAND() * 5), 'Berlin', 'Bavaria', 'Hamburg', 'Hesse', 'North Rhine-Westphalia')
      END,
      CASE 
        WHEN shipping_country = 'US' THEN CONCAT(LPAD(FLOOR(10000 + RAND() * 90000), 5, '0'))
        WHEN shipping_country = 'CA' THEN CONCAT(CHAR(65 + FLOOR(RAND() * 26)), FLOOR(RAND() * 10), CHAR(65 + FLOOR(RAND() * 26)), ' ', FLOOR(RAND() * 10), CHAR(65 + FLOOR(RAND() * 26)), FLOOR(RAND() * 10))
        WHEN shipping_country = 'GB' THEN CONCAT(CHAR(65 + FLOOR(RAND() * 26)), CHAR(65 + FLOOR(RAND() * 26)), FLOOR(RAND() * 10), ' ', FLOOR(RAND() * 10), CHAR(65 + FLOOR(RAND() * 26)), CHAR(65 + FLOOR(RAND() * 26)))
        ELSE CONCAT(LPAD(FLOOR(10000 + RAND() * 90000), 5, '0'))
      END,
      shipping_country,
      -- Same billing info as shipping (for simplicity)
      CASE 
        WHEN shipping_country = 'US' THEN CONCAT(FLOOR(100 + RAND() * 9900), ' Main St')
        WHEN shipping_country = 'CA' THEN CONCAT(FLOOR(100 + RAND() * 9900), ' Maple Ave')
        WHEN shipping_country = 'GB' THEN CONCAT(FLOOR(1 + RAND() * 100), ' High Street')
        ELSE CONCAT(FLOOR(1 + RAND() * 100), ' Hauptstrasse')
      END,
      CASE 
        WHEN shipping_country = 'US' THEN ELT(FLOOR(1 + RAND() * 5), 'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix')
        WHEN shipping_country = 'CA' THEN ELT(FLOOR(1 + RAND() * 5), 'Toronto', 'Vancouver', 'Montreal', 'Calgary', 'Ottawa')
        WHEN shipping_country = 'GB' THEN ELT(FLOOR(1 + RAND() * 5), 'London', 'Manchester', 'Birmingham', 'Liverpool', 'Edinburgh')
        ELSE ELT(FLOOR(1 + RAND() * 5), 'Berlin', 'Munich', 'Hamburg', 'Frankfurt', 'Cologne')
      END,
      CASE 
        WHEN shipping_country = 'US' THEN ELT(FLOOR(1 + RAND() * 5), 'NY', 'CA', 'IL', 'TX', 'AZ')
        WHEN shipping_country = 'CA' THEN ELT(FLOOR(1 + RAND() * 5), 'ON', 'BC', 'QC', 'AB', 'ON')
        WHEN shipping_country = 'GB' THEN ELT(FLOOR(1 + RAND() * 5), 'London', 'Greater Manchester', 'West Midlands', 'Merseyside', 'Scotland')
        ELSE ELT(FLOOR(1 + RAND() * 5), 'Berlin', 'Bavaria', 'Hamburg', 'Hesse', 'North Rhine-Westphalia')
      END,
      CASE 
        WHEN shipping_country = 'US' THEN CONCAT(LPAD(FLOOR(10000 + RAND() * 90000), 5, '0'))
        WHEN shipping_country = 'CA' THEN CONCAT(CHAR(65 + FLOOR(RAND() * 26)), FLOOR(RAND() * 10), CHAR(65 + FLOOR(RAND() * 26)), ' ', FLOOR(RAND() * 10), CHAR(65 + FLOOR(RAND() * 26)), FLOOR(RAND() * 10))
        WHEN shipping_country = 'GB' THEN CONCAT(CHAR(65 + FLOOR(RAND() * 26)), CHAR(65 + FLOOR(RAND() * 26)), FLOOR(RAND() * 10), ' ', FLOOR(RAND() * 10), CHAR(65 + FLOOR(RAND() * 26)), CHAR(65 + FLOOR(RAND() * 26)))
        ELSE CONCAT(LPAD(FLOOR(10000 + RAND() * 90000), 5, '0'))
      END,
      shipping_country,
      total,
      subtotal,
      shipping_cost,
      order_date,
      order_status,
      discount,
      promo_id,
      CASE 
        WHEN order_status IN ('pending', 'processing') THEN DATE_ADD(order_date, INTERVAL FLOOR(3 + RAND() * 7) DAY)
        WHEN order_status = 'shipped' THEN DATE_ADD(order_date, INTERVAL FLOOR(1 + RAND() * 5) DAY)
        ELSE NULL
      END,
      CASE 
        WHEN order_status = 'cancelled' THEN 'Order cancelled by customer'
        WHEN order_status = 'returned' THEN 'Item returned due to customer preference'
        ELSE NULL
      END,
      CASE 
        WHEN order_status NOT IN ('pending') THEN admin_id
        ELSE NULL
      END
    );
    
    -- Add 1 to 5 items to the order
    SET j = 0;
    SET item_count = FLOOR(1 + RAND() * 5);
    
    WHILE j < item_count DO
      -- Select a random product
      SET current_product_id = FLOOR(1 + RAND() * product_count);
      IF current_product_id < 1 THEN SET current_product_id = 1; END IF;
      IF current_product_id > product_count THEN SET current_product_id = product_count; END IF;
      
      -- Decide whether to use a variant
      SET use_variant = RAND() < 0.3; -- 30% chance of using a variant
      SET current_variant_id = NULL;
      
      IF use_variant THEN
        -- Select a random variant for this product if available
        SELECT id INTO current_variant_id 
        FROM product_variants 
        WHERE product_id = current_product_id 
        ORDER BY RAND() 
        LIMIT 1;
      END IF;
      
      -- Get product price
      SELECT price INTO item_price FROM products WHERE id = current_product_id;
      
      -- Adjust price if variant
      IF current_variant_id IS NOT NULL THEN
        SELECT price_adjustment INTO price_adj FROM product_variants WHERE id = current_variant_id;
        SET item_price = item_price + price_adj;
      END IF;
      
      -- Random quantity 1-3
      SET item_quantity = FLOOR(1 + RAND() * 3);
      
      -- Insert order item
      INSERT INTO order_items (
        id, order_id, product_id, variant_id, quantity, price, 
        tax_rate, tax_amount, tax_name, tax_region, discount_amount
      )
      VALUES (
        current_order_item_id,
        current_order_id,
        current_product_id,
        current_variant_id,
        item_quantity,
        item_price,
        CASE 
          WHEN shipping_country = 'US' THEN 0.0825 -- 8.25% tax rate
          WHEN shipping_country = 'CA' THEN 0.1300 -- 13% tax rate
          WHEN shipping_country = 'GB' THEN 0.2000 -- 20% VAT
          ELSE 0.1900 -- 19% German VAT
        END,
        ROUND(item_price * item_quantity * CASE 
          WHEN shipping_country = 'US' THEN 0.0825
          WHEN shipping_country = 'CA' THEN 0.1300
          WHEN shipping_country = 'GB' THEN 0.2000
          ELSE 0.1900
        END, 2),
        CASE 
          WHEN shipping_country = 'US' THEN 'Sales Tax'
          WHEN shipping_country = 'CA' THEN 'GST/HST'
          WHEN shipping_country = 'GB' THEN 'VAT'
          ELSE 'VAT'
        END,
        CASE 
          WHEN shipping_country = 'US' THEN ELT(FLOOR(1 + RAND() * 5), 'NY', 'CA', 'IL', 'TX', 'AZ')
          WHEN shipping_country = 'CA' THEN ELT(FLOOR(1 + RAND() * 5), 'ON', 'BC', 'QC', 'AB', 'ON')
          WHEN shipping_country = 'GB' THEN 'UK'
          ELSE 'DE'
        END,
        CASE
          WHEN promo_id IS NOT NULL THEN ROUND(item_price * item_quantity * (5 + RAND() * 10) / 100, 2) -- 5-15% discount per item if promo
          ELSE 0
        END
      );
      
      SET current_order_item_id = current_order_item_id + 1;
      SET j = j + 1;
    END WHILE;
    
    -- Add payment for the order
    SET payment_status = CASE
      WHEN order_status = 'cancelled' THEN 'refunded'
      WHEN order_status = 'returned' THEN 'refunded'
      WHEN order_status IN ('pending') THEN 'pending'
      ELSE 'completed'
    END;
    
    INSERT INTO payments (
      id, order_id, amount, payment_method, payment_processor,
      transaction_id, status, created_at, updated_at, refund_id, refund_reason,
      processed_by
    )
    VALUES (
      current_payment_id,
      current_order_id,
      total,
      CASE FLOOR(RAND() * 4)
        WHEN 0 THEN 'credit_card'
        WHEN 1 THEN 'paypal'
        WHEN 2 THEN 'bank_transfer'
        ELSE 'crypto'
      END,
      CASE FLOOR(RAND() * 4)
        WHEN 0 THEN 'Stripe'
        WHEN 1 THEN 'PayPal'
        WHEN 2 THEN 'Authorize.net'
        ELSE 'Coinbase'
      END,
      CONCAT('TXN-', UPPER(SUBSTRING(MD5(RAND()), 1, 12))),
      payment_status,
      order_date,
      CASE 
        WHEN payment_status = 'refunded' THEN DATE_ADD(order_date, INTERVAL FLOOR(1 + RAND() * 10) DAY)
        ELSE order_date
      END,
      CASE 
        WHEN payment_status = 'refunded' THEN CONCAT('REF-', UPPER(SUBSTRING(MD5(RAND()), 1, 12)))
        ELSE NULL
      END,
      CASE 
        WHEN payment_status = 'refunded' AND order_status = 'cancelled' THEN 'Order cancelled by customer'
        WHEN payment_status = 'refunded' AND order_status = 'returned' THEN 'Item returned by customer'
        ELSE NULL
      END,
      CASE 
        WHEN payment_status != 'pending' THEN admin_id
        ELSE NULL
      END
    );
    
    -- Add shipment for shipped/delivered orders
    IF order_status IN ('shipped', 'delivered') THEN
      INSERT INTO shipments (
        id, order_id, tracking_number, origin_country, destination_country,
        destination_region, destination_zip, weight, shipping_zone, status,
        actual_cost, shipping_method, service_level, base_cost, per_item_cost,
        per_weight_unit_cost, delivery_time_days, shipped_at, delivered_at,
        created_by, updated_by
      )
      VALUES (
        current_shipment_id,
        current_order_id,
        CONCAT('TRACK-', UPPER(SUBSTRING(MD5(RAND()), 1, 10))),
        'US', -- Origin is always US in this example
        shipping_country,
        CASE 
          WHEN shipping_country = 'US' THEN ELT(FLOOR(1 + RAND() * 5), 'Northeast', 'West', 'Midwest', 'South', 'Southwest')
          WHEN shipping_country = 'CA' THEN ELT(FLOOR(1 + RAND() * 5), 'Ontario', 'British Columbia', 'Quebec', 'Alberta', 'Ontario')
          WHEN shipping_country = 'GB' THEN ELT(FLOOR(1 + RAND() * 5), 'London', 'Northern England', 'Midlands', 'Scotland', 'Wales')
          ELSE ELT(FLOOR(1 + RAND() * 5), 'Berlin Region', 'Bavaria', 'Hamburg Region', 'Hesse', 'North Rhine-Westphalia')
        END,
        CASE 
          WHEN shipping_country = 'US' THEN CONCAT(LPAD(FLOOR(10000 + RAND() * 90000), 5, '0'))
          WHEN shipping_country = 'CA' THEN CONCAT(CHAR(65 + FLOOR(RAND() * 26)), FLOOR(RAND() * 10), CHAR(65 + FLOOR(RAND() * 26)), ' ', FLOOR(RAND() * 10), CHAR(65 + FLOOR(RAND() * 26)), FLOOR(RAND() * 10))
          WHEN shipping_country = 'GB' THEN CONCAT(CHAR(65 + FLOOR(RAND() * 26)), CHAR(65 + FLOOR(RAND() * 26)), FLOOR(RAND() * 10), ' ', FLOOR(RAND() * 10), CHAR(65 + FLOOR(RAND() * 26)), CHAR(65 + FLOOR(RAND() * 26)))
          ELSE CONCAT(LPAD(FLOOR(10000 + RAND() * 90000), 5, '0'))
        END,
        FLOOR(1 + RAND() * 10), -- Weight between 1-10 kg
        CASE 
          WHEN shipping_country = 'US' THEN 'Domestic'
          WHEN shipping_country = 'CA' THEN 'North America'
          ELSE 'International'
        END,
        CASE 
          WHEN order_status = 'delivered' THEN 'delivered'
          ELSE 'shipped'
        END,
        shipping_cost, 
        CASE FLOOR(RAND() * 5)
          WHEN 0 THEN 'standard'
          WHEN 1 THEN 'express'
          WHEN 2 THEN 'priority'
          WHEN 3 THEN 'economy'
          ELSE 'overnight'
        END,
        CASE FLOOR(RAND() * 2)
          WHEN 0 THEN 'standard'
          ELSE 'express'
        END,
        CASE 
          WHEN shipping_country = 'US' THEN 5.00
          WHEN shipping_country = 'CA' THEN 8.00
          ELSE 15.00
        END,
        CASE 
          WHEN shipping_country = 'US' THEN 1.50
          WHEN shipping_country = 'CA' THEN 2.00
          ELSE 3.50
        END,
        CASE 
          WHEN shipping_country = 'US' THEN 0.50
          WHEN shipping_country = 'CA' THEN 0.75
          ELSE 1.50
        END,
        CASE 
          WHEN shipping_country = 'US' THEN FLOOR(1 + RAND() * 5)
          WHEN shipping_country = 'CA' THEN FLOOR(3 + RAND() * 7)
          ELSE FLOOR(7 + RAND() * 14)
        END,
        DATE_ADD(order_date, INTERVAL FLOOR(1 + RAND() * 3) DAY), -- Shipped 1-3 days after order
        CASE 
          WHEN order_status = 'delivered' THEN DATE_ADD(order_date, INTERVAL FLOOR(3 + RAND() * 10) DAY)
          ELSE NULL
        END,
        admin_id,
        admin_id
      );
      
      SET current_shipment_id = current_shipment_id + 1;
    END IF;
    
    SET current_order_id = current_order_id + 1;
    SET i = i + 1;
  END WHILE;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('Admin','Super Admin') COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `avatar_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_password_change` timestamp NULL DEFAULT NULL,
  `failed_login_count` tinyint NOT NULL DEFAULT '0',
  `last_login` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `two_factor_verified` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_method` enum('app','sms','email') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'app',
  `backup_codes` json DEFAULT NULL,
  `two_factor_enabled_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admins_email_unique` (`email`),
  KEY `admins_role_index` (`role`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password_hash`, `role`, `created_at`, `updated_at`, `avatar_url`, `phone_number`, `last_password_change`, `failed_login_count`, `last_login`, `is_active`, `two_factor_verified`, `two_factor_method`, `backup_codes`, `two_factor_enabled_at`, `deleted_at`) VALUES
(1, 'superadmin', 'superadmin@pangaiashop.com', '$2y$12$araZ6pUy88cSxtV5Vrk16O9eCHwJVy3/d9WeI8eZb/SPfJqqNJA2e', 'Super Admin', '2025-05-23 15:03:38', '2025-05-25 08:23:46', 'https://randomuser.me/api/portraits/men/1.jpg', '+1-555-123-4567', NULL, 0, '2025-05-25 08:23:46', 1, 0, 'app', NULL, NULL, NULL),
(2, 'janeadmin', 'jane.admin@pangaiashop.com', '$argon2id$v=19$m=65536,t=4,p=1$EamWqse1ZwNRZOxMOx6PQQ$Qn3b4FYtH9UtUXnA6QEkKMiOBTC6aIWk5dIbO87FXUs', 'Admin', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/women/2.jpg', '+1-555-234-5678', NULL, 0, '2025-05-22 11:30:15', 1, 0, 'app', NULL, NULL, NULL),
(3, 'michaelm', 'michael.manager@pangaiashop.com', '$argon2id$v=19$m=65536,t=4,p=1$aNbnUmAR+XZ24a91stDcQw$EgkOoGi3SFVQBL2ALkJxhUgep8CXO4Pgn7NTxSMO0JY', 'Admin', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/men/3.jpg', '+1-555-345-6789', NULL, 0, '2025-05-23 06:12:43', 1, 0, 'app', NULL, NULL, NULL),
(4, 'sophiat', 'sophia.tech@pangaiashop.com', '$argon2id$v=19$m=65536,t=4,p=1$VY5Mbw3nzoUc5I+XvZdDnA$alRjP0PWJ5U3PiGa38wFCv1tjJf0NOE9RmiYZ5qRDBk', 'Admin', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/women/4.jpg', '+1-555-456-7890', NULL, 0, '2025-05-21 14:45:30', 1, 0, 'app', NULL, NULL, NULL),
(5, 'danielp', 'daniel.products@pangaiashop.com', '$argon2id$v=19$m=65536,t=4,p=1$3y+fY4hVsKNfcAXdz6fPrQ$TBjo5ztbUVLP4hA9KwLHBmkOGMt2Pok15QM7NTmnVJI', 'Admin', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/men/5.jpg', '+1-555-567-8901', NULL, 0, '2025-05-22 12:22:10', 1, 0, 'app', NULL, NULL, NULL),
(6, 'olivias', 'olivia.sales@pangaiashop.com', '$argon2id$v=19$m=65536,t=4,p=1$O/aZ81sZmp6UF5pDbcOPgg$RWzXvzWGYm0U/c3QxZGLPslmaedPjRnr7kQHOt1HCQM', 'Admin', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/women/6.jpg', '+1-555-678-9012', NULL, 0, '2025-05-23 08:05:17', 1, 0, 'app', NULL, NULL, NULL),
(7, 'ryanc', 'ryan.customer@pangaiashop.com', '$argon2id$v=19$m=65536,t=4,p=1$ayaYVf4C+jZQX46HRxeGzg$VuQwKDh0pQGEJaBFYEQ0gbGNhutLg1Ohd0KI69XnJDA', 'Admin', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/men/7.jpg', '+1-555-789-0123', NULL, 0, '2025-05-20 13:38:25', 1, 0, 'app', NULL, NULL, NULL),
(8, 'emmam', 'emma.marketing@pangaiashop.com', '$argon2id$v=19$m=65536,t=4,p=1$RdDSHpG57nruMXJqVDFNlw$vwNtSLB1H9hYOvFMZpUVPDfLUWxDyJLMh84V8azkR4k', 'Admin', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/women/8.jpg', '+1-555-890-1234', NULL, 0, '2025-05-23 05:42:51', 1, 0, 'app', NULL, NULL, NULL),
(9, 'alexs', 'alex.shipping@pangaiashop.com', '$argon2id$v=19$m=65536,t=4,p=1$cPzkxq9BKEanBnpR7Timmg$UiO5Yu6KJlYiVC0vVCTi86c1613sHM6SHzW0MN86+eA', 'Admin', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/men/9.jpg', '+1-555-901-2345', NULL, 0, '2025-05-21 10:20:37', 1, 0, 'app', NULL, NULL, NULL),
(10, 'graces', 'grace.support@pangaiashop.com', '$argon2id$v=19$m=65536,t=4,p=1$tPDu+bqO23MOxfSApwTw7g$kRwKL0h/V9N3PAlIvR8NZsu38gEdVXBaLOkPOzt8Pxk', 'Admin', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/women/10.jpg', '+1-555-012-3456', NULL, 0, '2025-05-22 09:50:19', 1, 0, 'app', NULL, NULL, NULL);

--
-- Triggers `admins`
--
DROP TRIGGER IF EXISTS `validate_admin_2fa`;
DELIMITER $$
CREATE TRIGGER `validate_admin_2fa` BEFORE INSERT ON `admins` FOR EACH ROW BEGIN
                IF NEW.two_factor_method = "sms" AND NEW.phone_number IS NULL THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Phone number required for SMS 2FA";
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `admin_audit_logs`
--

DROP TABLE IF EXISTS `admin_audit_logs`;
CREATE TABLE IF NOT EXISTS `admin_audit_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `resource` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'system',
  `resource_id` int NOT NULL,
  `previous_data` json DEFAULT NULL,
  `new_data` json DEFAULT NULL,
  `ip_address` blob NOT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `admin_audit_logs_admin_id_index` (`admin_id`),
  KEY `admin_audit_logs_resource_index` (`resource`),
  KEY `admin_audit_logs_created_at_index` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin_audit_logs`
--

INSERT INTO `admin_audit_logs` (`id`, `admin_id`, `action`, `resource`, `resource_id`, `previous_data`, `new_data`, `ip_address`, `user_agent`, `created_at`, `deleted_at`) VALUES
(1, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 15:56:57', NULL),
(2, 1, 'test', 'system', 1, NULL, NULL, 0x7f000001, 'Test Script', '2025-05-24 13:15:56', '2025-05-24 13:15:56'),
(3, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 17:01:16', NULL),
(4, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 17:02:39', NULL),
(5, 1, 'logout', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 17:06:31', NULL),
(6, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 17:10:25', NULL),
(7, 1, 'logout', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 17:18:33', NULL),
(8, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 17:19:08', NULL),
(9, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 19:05:44', NULL),
(10, 1, 'failed_login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 19:06:15', NULL),
(11, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 19:06:22', NULL),
(12, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 19:22:24', NULL),
(13, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 19:29:03', NULL),
(14, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 19:30:04', NULL),
(15, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 19:47:34', NULL),
(16, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 19:50:00', NULL),
(17, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 19:51:20', NULL),
(18, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 20:17:17', NULL),
(19, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 20:20:37', NULL),
(20, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 20:33:43', NULL),
(21, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-24 21:33:34', NULL),
(22, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 08:40:49', NULL),
(23, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 08:50:47', NULL),
(24, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 08:51:24', NULL),
(25, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 08:58:25', NULL),
(26, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:01:01', NULL),
(27, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:09:04', NULL),
(28, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:11:00', NULL),
(29, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:13:35', NULL),
(30, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:21:58', NULL),
(31, 1, 'logout', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:24:09', NULL),
(32, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:24:15', NULL),
(33, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:30:29', NULL),
(34, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:31:18', NULL),
(35, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:39:40', NULL),
(36, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:50:38', NULL),
(37, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:52:07', NULL),
(38, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:53:43', NULL),
(39, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 09:58:34', NULL),
(40, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 10:00:08', NULL),
(41, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 10:06:01', NULL),
(42, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 10:07:37', NULL),
(43, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 10:25:33', NULL),
(44, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 10:28:12', NULL),
(45, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 10:28:53', NULL),
(46, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 10:53:59', NULL),
(47, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 10:56:09', NULL),
(48, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 10:56:44', NULL),
(49, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 10:57:23', NULL),
(50, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 10:57:32', NULL),
(51, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:13:28', NULL),
(52, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:19:32', NULL),
(53, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:19:43', NULL),
(54, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:20:01', NULL),
(55, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:20:18', NULL),
(56, 1, 'failed_login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:21:00', NULL),
(57, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:21:07', NULL),
(58, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:21:22', NULL),
(59, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:22:09', NULL),
(60, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:23:08', NULL),
(61, 1, 'failed_login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:23:41', NULL),
(62, 1, 'login', 'auth', 1, NULL, NULL, 0x3132372e302e302e31, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', '2025-05-25 11:23:46', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `admin_password_reset_tokens`
--

DROP TABLE IF EXISTS `admin_password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `admin_password_reset_tokens` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_general_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `carts`
--

DROP TABLE IF EXISTS `carts`;
CREATE TABLE IF NOT EXISTS `carts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `carts_expiry` timestamp NULL DEFAULT NULL,
  `status` enum('active','checkout','completed','abandoned') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `promo_code` varchar(191) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `promo_code_id` bigint UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `carts_user_id_index` (`user_id`),
  KEY `carts_product_id_index` (`product_id`),
  KEY `carts_variant_id_index` (`variant_id`),
  KEY `carts_promo_code_id_foreign` (`promo_code_id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `carts`
--

INSERT INTO `carts` (`id`, `user_id`, `product_id`, `variant_id`, `quantity`, `created_at`, `updated_at`, `carts_expiry`, `status`, `promo_code`, `discount`, `deleted_at`, `promo_code_id`) VALUES
(1, 10, NULL, NULL, 1, '2025-05-23 15:03:42', '2025-05-23 15:03:42', '2025-05-25 15:03:42', 'active', NULL, 0.00, NULL, NULL),
(2, 6, NULL, NULL, 1, '2025-05-23 15:03:42', '2025-05-23 15:03:42', '2025-05-25 15:03:42', 'active', NULL, 0.00, NULL, NULL),
(3, 2, NULL, NULL, 1, '2025-05-23 15:03:42', '2025-05-23 15:03:42', '2025-05-27 15:03:42', 'active', NULL, 0.00, NULL, NULL),
(4, 8, NULL, NULL, 1, '2025-05-23 15:03:42', '2025-05-23 15:03:42', '2025-05-30 15:03:42', 'active', NULL, 0.00, NULL, NULL),
(5, 4, NULL, NULL, 1, '2025-05-23 15:03:42', '2025-05-23 15:03:42', '2025-05-26 15:03:42', 'active', NULL, 0.00, NULL, NULL),
(8, 9, NULL, NULL, 1, '2025-05-23 15:03:42', '2025-05-23 15:03:42', '2025-04-23 15:03:42', 'abandoned', NULL, 0.00, NULL, NULL),
(9, 6, NULL, NULL, 1, '2025-05-23 15:03:42', '2025-05-23 15:03:42', '2025-04-28 15:03:42', 'abandoned', NULL, 0.00, NULL, NULL),
(10, 3, NULL, NULL, 1, '2025-05-23 15:03:42', '2025-05-23 15:03:42', '2025-05-21 15:03:42', 'abandoned', NULL, 0.00, NULL, NULL),
(11, 11, NULL, NULL, 1, '2025-05-23 13:48:33', '2025-05-25 08:27:06', '2025-06-01 08:26:50', 'completed', NULL, 0.00, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

DROP TABLE IF EXISTS `cart_items`;
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `cart_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_items_variant_id_foreign` (`variant_id`),
  KEY `cart_items_cart_id_index` (`cart_id`),
  KEY `cart_items_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=113 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart_items`
--

INSERT INTO `cart_items` (`id`, `cart_id`, `product_id`, `variant_id`, `quantity`, `unit_price`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 4, 36, NULL, 3, 315.75, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(2, 3, 119, NULL, 1, 951.41, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(3, 2, 46, NULL, 1, 564.56, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(4, 1, 36, NULL, 2, 175.54, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(5, 5, 88, 27, 2, 996.24, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(6, 4, 13, 31, 2, 970.32, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(7, 3, 132, NULL, 3, 89.10, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(8, 2, 35, NULL, 2, 143.64, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(9, 5, 95, NULL, 3, 553.89, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(10, 4, 89, 14, 2, 964.54, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(11, 3, 111, 4, 1, 577.07, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(12, 2, 49, NULL, 1, 975.90, '2025-05-23 15:03:42', '2025-05-23 15:03:42', NULL),
(16, 9, 80, NULL, 3, 872.80, '2025-05-04 15:03:42', '2025-05-09 15:03:42', NULL),
(17, 8, 63, 1, 3, 572.09, '2025-05-20 15:03:42', '2025-05-03 15:03:42', NULL),
(18, 8, 18, 38, 1, 184.03, '2025-05-04 15:03:42', '2025-05-04 15:03:42', NULL),
(19, 10, 144, NULL, 3, 669.23, '2025-05-18 15:03:42', '2025-04-29 15:03:42', NULL),
(20, 9, 117, NULL, 3, 747.96, '2025-05-05 15:03:42', '2025-05-02 15:03:42', NULL),
(21, 8, 85, NULL, 2, 216.82, '2025-04-28 15:03:42', '2025-05-11 15:03:42', NULL),
(23, 11, 1, NULL, 2, 24.99, '2025-05-23 13:48:33', '2025-05-23 14:31:14', '2025-05-23 14:31:14'),
(24, 11, 30, NULL, 1, 39.99, '2025-05-23 14:27:57', '2025-05-23 14:31:14', '2025-05-23 14:31:14'),
(25, 11, 73, NULL, 1, 34.99, '2025-05-23 14:28:03', '2025-05-23 14:31:14', '2025-05-23 14:31:14'),
(26, 11, 10, NULL, 1, 49.99, '2025-05-23 14:28:37', '2025-05-23 14:30:54', '2025-05-23 14:30:54'),
(27, 11, 3, NULL, 1, 59.99, '2025-05-23 14:35:51', '2025-05-23 14:36:10', '2025-05-23 14:36:10'),
(28, 11, 1, NULL, 1, 24.99, '2025-05-23 14:37:24', '2025-05-23 14:38:23', '2025-05-23 14:38:23'),
(29, 11, 2, NULL, 1, 89.99, '2025-05-23 14:37:27', '2025-05-23 14:38:23', '2025-05-23 14:38:23'),
(30, 11, 33, NULL, 1, 129.99, '2025-05-23 14:37:39', '2025-05-23 14:38:23', '2025-05-23 14:38:23'),
(31, 11, 35, NULL, 1, 69.99, '2025-05-23 14:37:43', '2025-05-23 14:38:23', '2025-05-23 14:38:23'),
(32, 11, 30, NULL, 1, 39.99, '2025-05-23 14:44:53', '2025-05-23 14:45:10', '2025-05-23 14:45:10'),
(33, 11, 1, NULL, 1, 24.99, '2025-05-23 14:47:17', '2025-05-23 14:47:34', '2025-05-23 14:47:34'),
(34, 11, 5, NULL, 1, 99.99, '2025-05-23 14:49:31', '2025-05-23 14:50:58', '2025-05-23 14:50:58'),
(35, 11, 2, NULL, 1, 89.99, '2025-05-23 14:54:10', '2025-05-23 14:54:52', '2025-05-23 14:54:52'),
(36, 11, 2, NULL, 1, 89.99, '2025-05-23 15:18:21', '2025-05-23 15:18:40', '2025-05-23 15:18:40'),
(37, 11, 2, NULL, 1, 89.99, '2025-05-23 15:52:12', '2025-05-23 16:03:57', '2025-05-23 16:03:57'),
(38, 11, 2, NULL, 2, 89.99, '2025-05-23 16:04:04', '2025-05-23 16:05:31', '2025-05-23 16:05:31'),
(39, 11, 2, NULL, 2, 89.99, '2025-05-23 16:06:02', '2025-05-23 17:57:49', '2025-05-23 17:57:49'),
(40, 11, 2, NULL, 1, 79.99, '2025-05-23 17:57:54', '2025-05-23 17:58:40', '2025-05-23 17:58:40'),
(41, 11, 2, NULL, 2, 79.99, '2025-05-23 17:58:56', '2025-05-23 18:12:06', '2025-05-23 18:12:06'),
(42, 11, 2, NULL, 8, 79.99, '2025-05-23 18:12:20', '2025-05-23 18:13:10', '2025-05-23 18:13:10'),
(43, 11, 2, NULL, 10, 79.99, '2025-05-23 18:13:20', '2025-05-23 18:14:05', '2025-05-23 18:14:05'),
(44, 11, 2, NULL, 18, 79.99, '2025-05-23 18:25:36', '2025-05-23 19:29:10', '2025-05-23 19:29:10'),
(45, 11, 4, NULL, 2, 59.99, '2025-05-23 18:25:41', '2025-05-23 19:29:13', '2025-05-23 19:29:13'),
(46, 11, 1, NULL, 10, 19.99, '2025-05-23 19:01:04', '2025-05-23 19:29:15', '2025-05-23 19:29:15'),
(47, 11, 3, NULL, 3, 49.99, '2025-05-23 19:01:11', '2025-05-23 19:29:19', '2025-05-23 19:29:19'),
(48, 11, 5, NULL, 2, 89.99, '2025-05-23 19:01:39', '2025-05-23 19:29:21', '2025-05-23 19:29:21'),
(49, 11, 8, NULL, 2, 49.99, '2025-05-23 19:01:42', '2025-05-23 19:29:24', '2025-05-23 19:29:24'),
(50, 11, 9, NULL, 1, 69.99, '2025-05-23 19:01:43', '2025-05-23 19:29:26', '2025-05-23 19:29:26'),
(51, 11, 7, NULL, 1, 34.99, '2025-05-23 19:01:44', '2025-05-23 19:29:28', '2025-05-23 19:29:28'),
(52, 11, 86, NULL, 1, 59.99, '2025-05-23 19:18:41', '2025-05-23 19:29:30', '2025-05-23 19:29:30'),
(53, 11, 2, NULL, 5, 79.99, '2025-05-23 19:29:42', '2025-05-24 07:59:33', '2025-05-24 07:59:33'),
(54, 11, 1, NULL, 4, 19.99, '2025-05-24 06:51:38', '2025-05-24 07:59:35', '2025-05-24 07:59:35'),
(55, 11, 3, NULL, 3, 49.99, '2025-05-24 06:51:40', '2025-05-24 07:59:38', '2025-05-24 07:59:38'),
(56, 11, 78, NULL, 3, 34.99, '2025-05-24 07:56:05', '2025-05-24 08:09:12', '2025-05-24 08:09:12'),
(57, 11, 113, NULL, 5, 949.99, '2025-05-24 07:56:07', '2025-05-24 08:09:15', '2025-05-24 08:09:15'),
(58, 11, 107, NULL, 2, 749.99, '2025-05-24 07:56:08', '2025-05-24 08:09:17', '2025-05-24 08:09:17'),
(59, 11, 133, NULL, 1, 29.99, '2025-05-24 07:56:11', '2025-05-24 08:09:20', '2025-05-24 08:09:20'),
(60, 11, 20, NULL, 9, 259.99, '2025-05-24 07:56:35', '2025-05-24 08:09:22', '2025-05-24 08:09:22'),
(61, 11, 105, NULL, 5, 849.99, '2025-05-24 07:56:43', '2025-05-24 08:09:25', '2025-05-24 08:09:25'),
(62, 11, 139, NULL, 2, 19.99, '2025-05-24 07:56:45', '2025-05-24 08:09:28', '2025-05-24 08:09:28'),
(63, 11, 61, NULL, 85, 29.99, '2025-05-24 07:57:04', '2025-05-24 08:09:31', '2025-05-24 08:09:31'),
(64, 11, 2, NULL, 3, 79.99, '2025-05-24 08:53:17', '2025-05-24 09:03:01', '2025-05-24 09:03:01'),
(65, 11, 1, NULL, 2, 19.99, '2025-05-24 08:53:18', '2025-05-24 09:03:01', '2025-05-24 09:03:01'),
(66, 11, 6, NULL, 1, 59.99, '2025-05-24 08:55:27', '2025-05-24 09:03:01', '2025-05-24 09:03:01'),
(67, 11, 4, NULL, 1, NULL, '2025-05-24 09:02:40', '2025-05-24 09:03:01', '2025-05-24 09:03:01'),
(68, 11, 10, NULL, 1, NULL, '2025-05-24 09:02:40', '2025-05-24 09:03:01', '2025-05-24 09:03:01'),
(69, 11, 11, NULL, 1, NULL, '2025-05-24 09:02:40', '2025-05-24 09:03:01', '2025-05-24 09:03:01'),
(70, 11, 12, NULL, 1, NULL, '2025-05-24 09:02:40', '2025-05-24 09:03:01', '2025-05-24 09:03:01'),
(71, 11, 30, NULL, 1, NULL, '2025-05-24 09:02:40', '2025-05-24 09:03:01', '2025-05-24 09:03:01'),
(72, 11, 48, NULL, 1, NULL, '2025-05-24 09:02:40', '2025-05-24 09:03:01', '2025-05-24 09:03:01'),
(73, 11, 1, NULL, 2, 19.99, '2025-05-25 06:08:32', '2025-05-25 06:08:53', '2025-05-25 06:08:53'),
(74, 11, 2, NULL, 3, 79.99, '2025-05-25 06:08:32', '2025-05-25 06:08:53', '2025-05-25 06:08:53'),
(75, 11, 3, NULL, 2, 49.99, '2025-05-25 06:08:33', '2025-05-25 06:08:53', '2025-05-25 06:08:53'),
(76, 11, 91, NULL, 2, 799.99, '2025-05-25 06:51:30', '2025-05-25 06:51:53', '2025-05-25 06:51:53'),
(77, 11, 92, NULL, 3, 1299.99, '2025-05-25 06:51:30', '2025-05-25 06:51:53', '2025-05-25 06:51:53'),
(78, 11, 93, NULL, 2, 649.99, '2025-05-25 06:51:31', '2025-05-25 06:51:53', '2025-05-25 06:51:53'),
(79, 11, 112, NULL, 1, 1499.99, '2025-05-25 06:53:11', '2025-05-25 06:53:32', '2025-05-25 06:53:32'),
(80, 11, 113, NULL, 1, 949.99, '2025-05-25 06:53:12', '2025-05-25 06:53:32', '2025-05-25 06:53:32'),
(81, 11, 132, NULL, 124, 19.99, '2025-05-25 06:57:43', '2025-05-25 06:58:21', '2025-05-25 06:58:21'),
(82, 11, 141, NULL, 1, 54.99, '2025-05-25 06:59:08', '2025-05-25 06:59:38', '2025-05-25 06:59:38'),
(83, 11, 142, NULL, 1, 44.99, '2025-05-25 06:59:09', '2025-05-25 06:59:38', '2025-05-25 06:59:38'),
(84, 11, 143, NULL, 1, 17.99, '2025-05-25 06:59:09', '2025-05-25 06:59:38', '2025-05-25 06:59:38'),
(85, 11, 71, NULL, 1, 34.99, '2025-05-25 07:06:33', '2025-05-25 07:07:12', '2025-05-25 07:07:12'),
(86, 11, 72, NULL, 1, 44.99, '2025-05-25 07:06:34', '2025-05-25 07:07:12', '2025-05-25 07:07:12'),
(87, 11, 73, NULL, 1, 29.99, '2025-05-25 07:06:34', '2025-05-25 07:07:12', '2025-05-25 07:07:12'),
(88, 11, 77, NULL, 1, 49.99, '2025-05-25 07:06:41', '2025-05-25 07:07:12', '2025-05-25 07:07:12'),
(89, 11, 78, NULL, 1, 34.99, '2025-05-25 07:06:42', '2025-05-25 07:07:12', '2025-05-25 07:07:12'),
(90, 11, 79, NULL, 1, 29.99, '2025-05-25 07:06:42', '2025-05-25 07:07:12', '2025-05-25 07:07:12'),
(91, 11, 2, NULL, 1, 79.99, '2025-05-25 08:09:49', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(92, 11, 25, NULL, 1, 99.99, '2025-05-25 08:09:52', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(93, 11, 24, NULL, 1, 79.99, '2025-05-25 08:09:53', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(94, 11, 41, NULL, 1, 259.99, '2025-05-25 08:10:00', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(95, 11, 40, NULL, 1, 69.99, '2025-05-25 08:10:00', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(96, 11, 42, NULL, 1, 69.99, '2025-05-25 08:10:01', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(97, 11, 101, NULL, 1, 949.99, '2025-05-25 08:10:06', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(98, 11, 102, NULL, 1, 449.99, '2025-05-25 08:10:07', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(99, 11, 103, NULL, 1, 229.99, '2025-05-25 08:10:07', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(100, 11, 105, NULL, 1, 849.99, '2025-05-25 08:10:09', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(101, 11, 104, NULL, 1, 1399.99, '2025-05-25 08:10:10', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(102, 11, 106, NULL, 1, 549.99, '2025-05-25 08:10:10', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(103, 11, 75, NULL, 1, 69.99, '2025-05-25 08:10:19', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(104, 11, 74, NULL, 1, 24.99, '2025-05-25 08:10:20', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(105, 11, 76, NULL, 1, 39.99, '2025-05-25 08:10:20', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(106, 11, 79, NULL, 1, 29.99, '2025-05-25 08:10:22', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(107, 11, 78, NULL, 1, 34.99, '2025-05-25 08:10:22', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(108, 11, 77, NULL, 1, 49.99, '2025-05-25 08:10:23', '2025-05-25 08:10:40', '2025-05-25 08:10:40'),
(109, 11, 1, NULL, 2, 19.99, '2025-05-25 08:24:44', '2025-05-25 08:27:06', '2025-05-25 08:27:06'),
(110, 11, 2, NULL, 3, 79.99, '2025-05-25 08:24:45', '2025-05-25 08:27:06', '2025-05-25 08:27:06'),
(111, 11, 3, NULL, 3, 49.99, '2025-05-25 08:24:45', '2025-05-25 08:27:06', '2025-05-25 08:27:06'),
(112, 11, 81, NULL, 164, 259.99, '2025-05-25 08:25:37', '2025-05-25 08:27:06', '2025-05-25 08:27:06');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `parent_category_id` bigint UNSIGNED DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `category_description` text COLLATE utf8mb4_general_ci,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `display_order` int NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_parent_category_id_index` (`parent_category_id`),
  KEY `categories_created_by_index` (`created_by`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `parent_category_id`, `image_url`, `category_description`, `created_by`, `created_at`, `updated_at`, `is_active`, `display_order`, `deleted_at`) VALUES
(1, 'Men\'s Clothes', NULL, 'https://images.unsplash.com/photo-1617137968427-85924c800a22?q=80&w=1287&auto=format&fit=crop', 'High-quality clothing for men including suits, shirts, pants and accessories', 1, '2025-05-23 15:03:38', '2025-05-23 15:29:14', 1, 10, NULL),
(2, 'Women\'s Clothes', NULL, 'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1287&auto=format&fit=crop', 'Stylish clothing for women including dresses, tops, bottoms and accessories', 1, '2025-05-23 15:03:38', '2025-05-23 15:29:14', 1, 20, NULL),
(3, 'Men\'s Suits', 1, 'https://images.unsplash.com/photo-1623880840102-7df0a9f3545b?q=80&w=1287&auto=format&fit=crop', 'Professional and elegant suits for men for all occasions', 1, '2025-05-23 15:03:38', '2025-05-23 15:55:06', 1, 11, NULL),
(4, 'Women\'s Dresses', 2, 'https://images.unsplash.com/photo-1595777457583-95e059d581b8?q=80&w=1483&auto=format&fit=crop', 'Beautiful dresses for women for casual and formal occasions', 1, '2025-05-23 15:03:38', '2025-05-23 15:29:14', 1, 21, NULL),
(5, 'Women\'s Accessories', 2, 'https://images.unsplash.com/photo-1629224316810-9d8805b95e76?q=80&w=1470&auto=format&fit=crop', 'Stylish accessories including makeup, hats, necklaces, rings and more', 1, '2025-05-23 15:03:38', '2025-05-23 15:29:14', 1, 22, NULL),
(6, 'Men\'s Accessories', 1, 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?q=80&w=1280', 'Elegant accessories including sunglasses, watches, hats and more', 1, '2025-05-23 15:03:38', '2025-05-23 16:09:34', 1, 12, NULL),
(7, 'Children\'s Clothes', NULL, 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?q=80&w=1287', 'Comfortable and durable clothing for children of all ages', 1, '2025-05-23 15:03:38', '2025-05-23 16:09:09', 1, 30, NULL),
(8, 'Toys', NULL, 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?q=80&w=1470', 'Fun and educational toys for children of all ages', 1, '2025-05-23 15:03:38', '2025-05-23 16:08:29', 1, 40, NULL),
(9, 'Home Accessories', NULL, 'https://images.unsplash.com/photo-1616486338812-3dadae4b4ace?q=80&w=1332&auto=format&fit=crop', 'Beautiful and functional accessories to enhance your home', 1, '2025-05-23 15:03:38', '2025-05-23 15:29:14', 1, 50, NULL),
(10, 'Electronics', NULL, 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?q=80&w=1301&auto=format&fit=crop', 'High-quality electronics for your home and personal use', 1, '2025-05-23 15:03:38', '2025-05-23 15:29:14', 1, 60, NULL),
(11, 'Phones', 10, 'https://images.unsplash.com/photo-1585060544812-6b45742d762f?q=80&w=1381&auto=format&fit=crop', 'Latest smartphones with advanced features and technology', 1, '2025-05-23 15:03:38', '2025-05-23 15:29:14', 1, 61, NULL),
(12, 'Laptops', 10, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?q=80&w=1471&auto=format&fit=crop', 'High-performance laptops for work and entertainment', 1, '2025-05-23 15:03:38', '2025-05-23 15:29:14', 1, 62, NULL),
(13, 'Shoes', NULL, 'https://images.unsplash.com/photo-1595950653106-6c9ebd614d3a?q=80&w=1287&auto=format&fit=crop', 'Stylish and comfortable footwear for all occasions', 1, '2025-05-23 15:03:38', '2025-05-23 15:29:14', 1, 70, NULL),
(14, 'Books', NULL, 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=1290&auto=format&fit=crop', 'A wide selection of books across various genres and topics', 1, '2025-05-23 15:03:38', '2025-05-23 15:29:14', 1, 80, NULL),
(15, 'Pet Needs', NULL, 'https://images.unsplash.com/photo-1548767797-d8c844163c4c?q=80&w=1171&auto=format&fit=crop', 'Everything you need to keep your pets healthy and happy', 1, '2025-05-23 15:03:38', '2025-05-23 15:29:14', 1, 90, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `connection` text COLLATE utf8mb4_general_ci NOT NULL,
  `queue` text COLLATE utf8mb4_general_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventories`
--

DROP TABLE IF EXISTS `inventories`;
CREATE TABLE IF NOT EXISTS `inventories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL,
  `reserved_quantity` int NOT NULL DEFAULT '0',
  `location` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `last_restocked` timestamp NULL DEFAULT NULL,
  `low_stock_threshold` int NOT NULL DEFAULT '10',
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `inventories_product_id_index` (`product_id`),
  KEY `inventories_variant_id_index` (`variant_id`),
  KEY `inventories_updated_by_index` (`updated_by`),
  KEY `inventories_product_id_quantity_reserved_quantity_index` (`product_id`,`quantity`,`reserved_quantity`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventories`
--

INSERT INTO `inventories` (`id`, `product_id`, `variant_id`, `quantity`, `reserved_quantity`, `location`, `last_restocked`, `low_stock_threshold`, `updated_by`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 162, 15, 'Warehouse B', '2025-05-14 15:03:39', 10, 1, '2025-05-25 11:27:06', NULL),
(2, 2, NULL, 66, 15, 'Warehouse C', '2025-04-26 15:03:39', 10, 1, '2025-05-25 11:27:06', NULL),
(3, 3, NULL, 83, 10, 'Warehouse A', '2025-04-27 15:03:39', 10, 1, '2025-05-25 11:27:06', NULL),
(4, 4, NULL, 164, 4, 'Warehouse B', '2025-04-30 15:03:39', 10, 1, '2025-05-24 12:03:01', NULL),
(5, 5, NULL, 80, 14, 'Warehouse C', '2025-05-21 15:03:39', 10, 1, '2025-05-23 17:50:58', NULL),
(6, 6, NULL, 75, 12, 'Warehouse A', '2025-05-03 15:03:39', 10, 1, '2025-05-24 12:03:01', NULL),
(7, 7, NULL, 123, 8, 'Warehouse B', '2025-05-07 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(8, 8, NULL, 135, 3, 'Warehouse C', '2025-05-20 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(9, 9, NULL, 66, 3, 'Warehouse A', '2025-05-05 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(10, 10, NULL, 130, 16, 'Warehouse B', '2025-05-06 15:03:39', 10, 1, '2025-05-24 12:03:01', NULL),
(11, 11, NULL, 106, 3, 'Warehouse C', '2025-05-03 15:03:39', 10, 1, '2025-05-24 12:03:01', NULL),
(12, 12, NULL, 194, 15, 'Warehouse A', '2025-04-28 15:03:39', 10, 1, '2025-05-24 12:03:01', NULL),
(13, 13, NULL, 59, 14, 'Warehouse B', '2025-05-10 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(14, 14, NULL, 67, 3, 'Warehouse C', '2025-05-05 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(15, 15, NULL, 126, 13, 'Warehouse A', '2025-04-26 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(16, 16, NULL, 125, 15, 'Warehouse B', '2025-05-11 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(17, 17, NULL, 167, 12, 'Warehouse C', '2025-05-02 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(18, 18, NULL, 165, 13, 'Warehouse A', '2025-05-18 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(19, 19, NULL, 166, 7, 'Warehouse B', '2025-05-10 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(20, 20, NULL, 73, 8, 'Warehouse C', '2025-05-01 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(21, 23, NULL, 108, 14, 'Warehouse C', '2025-05-09 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(22, 24, NULL, 80, 12, 'Warehouse A', '2025-05-11 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(23, 25, NULL, 81, 17, 'Warehouse B', '2025-05-03 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(24, 26, NULL, 170, 19, 'Warehouse C', '2025-05-08 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(25, 27, NULL, 139, 8, 'Warehouse A', '2025-05-10 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(26, 28, NULL, 186, 3, 'Warehouse B', '2025-05-17 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(27, 29, NULL, 118, 13, 'Warehouse C', '2025-04-24 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(28, 30, NULL, 181, 11, 'Warehouse A', '2025-05-21 15:03:39', 10, 1, '2025-05-24 12:03:01', NULL),
(29, 31, NULL, 155, 5, 'Warehouse B', '2025-05-13 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(30, 32, NULL, 185, 9, 'Warehouse C', '2025-05-06 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(31, 33, NULL, 132, 0, 'Warehouse A', '2025-05-13 15:03:39', 10, 1, '2025-05-23 17:38:23', NULL),
(32, 34, NULL, 169, 17, 'Warehouse B', '2025-05-21 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(33, 35, NULL, 158, 8, 'Warehouse C', '2025-04-29 15:03:39', 10, 1, '2025-05-23 17:38:23', NULL),
(34, 36, NULL, 179, 17, 'Warehouse A', '2025-05-01 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(35, 37, NULL, 70, 8, 'Warehouse B', '2025-04-30 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(36, 38, NULL, 133, 9, 'Warehouse C', '2025-05-04 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(37, 39, NULL, 170, 1, 'Warehouse A', '2025-05-21 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(38, 40, NULL, 67, 7, 'Warehouse B', '2025-05-08 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(39, 41, NULL, 106, 8, 'Warehouse C', '2025-04-27 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(40, 42, NULL, 72, 3, 'Warehouse A', '2025-05-12 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(41, 43, NULL, 118, 1, 'Warehouse B', '2025-05-21 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(42, 44, NULL, 77, 12, 'Warehouse C', '2025-05-04 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(43, 45, NULL, 105, 17, 'Warehouse A', '2025-05-15 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(44, 46, NULL, 157, 15, 'Warehouse B', '2025-04-30 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(45, 47, NULL, 128, 5, 'Warehouse C', '2025-04-29 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(46, 48, NULL, 89, 17, 'Warehouse A', '2025-05-05 15:03:39', 10, 1, '2025-05-24 12:03:01', NULL),
(47, 49, NULL, 114, 5, 'Warehouse B', '2025-05-18 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(48, 50, NULL, 61, 16, 'Warehouse C', '2025-04-28 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(49, 51, NULL, 164, 5, 'Warehouse A', '2025-05-18 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(50, 52, NULL, 63, 17, 'Warehouse B', '2025-05-23 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(51, 53, NULL, 132, 13, 'Warehouse C', '2025-05-01 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(52, 54, NULL, 151, 3, 'Warehouse A', '2025-04-30 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(53, 55, NULL, 112, 14, 'Warehouse B', '2025-05-10 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(54, 56, NULL, 55, 16, 'Warehouse C', '2025-05-21 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(55, 57, NULL, 181, 3, 'Warehouse A', '2025-05-19 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(56, 58, NULL, 96, 1, 'Warehouse B', '2025-05-12 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(57, 59, NULL, 169, 15, 'Warehouse C', '2025-05-08 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(58, 60, NULL, 87, 13, 'Warehouse A', '2025-05-01 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(59, 121, NULL, 137, 13, 'Warehouse B', '2025-05-01 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(60, 122, NULL, 150, 1, 'Warehouse C', '2025-05-10 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(61, 123, NULL, 189, 6, 'Warehouse A', '2025-04-28 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(62, 124, NULL, 89, 15, 'Warehouse B', '2025-05-20 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(63, 125, NULL, 82, 15, 'Warehouse C', '2025-05-19 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(64, 126, NULL, 109, 11, 'Warehouse A', '2025-05-03 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(65, 128, NULL, 144, 3, 'Warehouse C', '2025-04-28 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(66, 129, NULL, 175, 12, 'Warehouse A', '2025-05-07 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(67, 130, NULL, 196, 3, 'Warehouse B', '2025-05-23 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(68, 91, NULL, 131, 14, 'Warehouse B', '2025-04-28 15:03:39', 10, 1, '2025-05-25 09:51:53', NULL),
(69, 92, NULL, 77, 8, 'Warehouse C', '2025-05-10 15:03:39', 10, 1, '2025-05-25 09:51:53', NULL),
(70, 93, NULL, 50, 14, 'Warehouse A', '2025-05-04 15:03:39', 10, 1, '2025-05-25 09:51:53', NULL),
(71, 94, NULL, 52, 2, 'Warehouse B', '2025-05-04 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(72, 95, NULL, 176, 5, 'Warehouse C', '2025-05-01 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(73, 96, NULL, 198, 13, 'Warehouse A', '2025-05-09 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(74, 97, NULL, 100, 4, 'Warehouse B', '2025-05-18 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(75, 98, NULL, 73, 5, 'Warehouse C', '2025-04-27 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(76, 99, NULL, 145, 10, 'Warehouse A', '2025-05-04 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(77, 100, NULL, 153, 10, 'Warehouse B', '2025-05-09 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(78, 101, NULL, 176, 16, 'Warehouse C', '2025-05-06 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(79, 102, NULL, 106, 3, 'Warehouse A', '2025-04-28 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(80, 103, NULL, 147, 14, 'Warehouse B', '2025-05-04 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(81, 104, NULL, 71, 14, 'Warehouse C', '2025-05-16 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(82, 105, NULL, 54, 8, 'Warehouse A', '2025-05-21 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(83, 106, NULL, 71, 9, 'Warehouse B', '2025-04-26 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(84, 107, NULL, 63, 15, 'Warehouse C', '2025-05-09 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(85, 108, NULL, 77, 8, 'Warehouse A', '2025-05-03 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(86, 109, NULL, 62, 7, 'Warehouse B', '2025-05-07 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(87, 110, NULL, 151, 14, 'Warehouse C', '2025-05-06 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(88, 111, NULL, 161, 19, 'Warehouse A', '2025-05-03 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(89, 112, NULL, 116, 4, 'Warehouse B', '2025-05-03 15:03:39', 10, 1, '2025-05-25 09:53:32', NULL),
(90, 113, NULL, 162, 15, 'Warehouse C', '2025-05-06 15:03:39', 10, 1, '2025-05-25 09:53:32', NULL),
(91, 114, NULL, 138, 4, 'Warehouse A', '2025-05-14 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(92, 115, NULL, 181, 9, 'Warehouse B', '2025-05-01 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(93, 116, NULL, 95, 5, 'Warehouse C', '2025-05-08 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(94, 117, NULL, 151, 17, 'Warehouse A', '2025-05-14 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(95, 118, NULL, 185, 12, 'Warehouse B', '2025-05-11 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(96, 119, NULL, 85, 18, 'Warehouse C', '2025-04-29 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(97, 120, NULL, 106, 8, 'Warehouse A', '2025-04-24 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(98, 81, NULL, -21, 3, 'Warehouse A', '2025-05-21 15:03:39', 10, 1, '2025-05-25 11:27:06', NULL),
(99, 82, NULL, 175, 18, 'Warehouse B', '2025-05-20 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(100, 83, NULL, 180, 19, 'Warehouse C', '2025-05-17 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(101, 84, NULL, 88, 11, 'Warehouse A', '2025-05-20 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(102, 85, NULL, 177, 17, 'Warehouse B', '2025-04-25 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(103, 86, NULL, 50, 3, 'Warehouse C', '2025-04-25 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(104, 87, NULL, 83, 4, 'Warehouse A', '2025-05-08 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(105, 88, NULL, 177, 14, 'Warehouse B', '2025-05-21 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(106, 89, NULL, 78, 14, 'Warehouse C', '2025-05-22 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(107, 90, NULL, 56, 1, 'Warehouse A', '2025-05-14 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(108, 61, NULL, 100, 14, 'Warehouse B', '2025-05-06 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(109, 62, NULL, 167, 2, 'Warehouse C', '2025-05-13 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(110, 63, NULL, 97, 10, 'Warehouse A', '2025-05-01 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(111, 64, NULL, 69, 7, 'Warehouse B', '2025-05-06 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(112, 65, NULL, 152, 14, 'Warehouse C', '2025-05-08 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(113, 66, NULL, 108, 8, 'Warehouse A', '2025-04-24 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(114, 67, NULL, 135, 18, 'Warehouse B', '2025-04-24 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(115, 68, NULL, 64, 10, 'Warehouse C', '2025-05-11 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(116, 69, NULL, 113, 18, 'Warehouse A', '2025-05-17 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(117, 70, NULL, 116, 10, 'Warehouse B', '2025-05-14 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(118, 71, NULL, 195, 18, 'Warehouse C', '2025-04-29 15:03:39', 10, 1, '2025-05-25 10:07:11', NULL),
(119, 72, NULL, 76, 9, 'Warehouse A', '2025-04-27 15:03:39', 10, 1, '2025-05-25 10:07:11', NULL),
(120, 73, NULL, 53, 9, 'Warehouse B', '2025-05-16 15:03:39', 10, 1, '2025-05-25 10:07:11', NULL),
(121, 74, NULL, 175, 9, 'Warehouse C', '2025-05-01 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(122, 75, NULL, 98, 8, 'Warehouse A', '2025-05-18 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(123, 76, NULL, 135, 7, 'Warehouse B', '2025-05-20 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(124, 77, NULL, 111, 15, 'Warehouse C', '2025-05-03 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(125, 78, NULL, 52, 2, 'Warehouse A', '2025-05-10 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(126, 79, NULL, 195, 10, 'Warehouse B', '2025-05-04 15:03:39', 10, 1, '2025-05-25 11:10:40', NULL),
(127, 80, NULL, 146, 6, 'Warehouse C', '2025-05-06 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(128, 127, NULL, 59, 10, 'Warehouse B', '2025-05-09 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(129, 141, NULL, 180, 17, 'Warehouse A', '2025-04-28 15:03:39', 10, 1, '2025-05-25 09:59:38', NULL),
(130, 142, NULL, 133, 5, 'Warehouse B', '2025-05-05 15:03:39', 10, 1, '2025-05-25 09:59:38', NULL),
(131, 143, NULL, 90, 11, 'Warehouse C', '2025-04-26 15:03:39', 10, 1, '2025-05-25 09:59:38', NULL),
(132, 144, NULL, 197, 2, 'Warehouse A', '2025-05-02 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(133, 145, NULL, 68, 10, 'Warehouse B', '2025-05-19 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(134, 146, NULL, 78, 10, 'Warehouse C', '2025-05-19 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(135, 147, NULL, 76, 7, 'Warehouse A', '2025-05-13 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(136, 148, NULL, 148, 4, 'Warehouse B', '2025-05-21 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(137, 149, NULL, 163, 11, 'Warehouse C', '2025-05-08 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(138, 150, NULL, 185, 19, 'Warehouse A', '2025-05-20 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(139, 21, NULL, 153, 2, 'Warehouse A', '2025-05-10 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(140, 22, NULL, 187, 4, 'Warehouse B', '2025-05-09 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(141, 131, NULL, 159, 2, 'Warehouse C', '2025-05-07 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(142, 132, NULL, -17, 4, 'Warehouse A', '2025-04-25 15:03:39', 10, 1, '2025-05-25 09:58:21', NULL),
(143, 133, NULL, 72, 17, 'Warehouse B', '2025-04-28 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(144, 134, NULL, 160, 1, 'Warehouse C', '2025-05-16 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(145, 135, NULL, 199, 4, 'Warehouse A', '2025-05-20 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(146, 136, NULL, 179, 19, 'Warehouse B', '2025-05-12 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(147, 137, NULL, 190, 10, 'Warehouse C', '2025-04-28 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(148, 138, NULL, 141, 10, 'Warehouse A', '2025-04-27 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(149, 139, NULL, 164, 3, 'Warehouse B', '2025-05-06 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL),
(150, 140, NULL, 114, 7, 'Warehouse C', '2025-05-05 15:03:39', 10, 1, '2025-05-23 15:03:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `attempts` tinyint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_general_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_00_create_users_table', 1),
(2, '0001_01_01_01_create_cache_table', 1),
(3, '0001_01_01_02_create_jobs_table', 1),
(4, '2025_04_21_03_create_admins_table', 1),
(5, '2025_04_21_04_create_vendors_table', 1),
(6, '2025_04_21_05_create_products_table', 1),
(7, '2025_04_21_06_create_product_variants_table', 1),
(8, '2025_04_21_07_create_carts_table', 1),
(9, '2025_04_21_08_create_admin_audit_logs_table', 1),
(10, '2025_04_21_09_create_user_preferences_table', 1),
(11, '2025_04_21_10_create_password_reset_tokens_table', 1),
(12, '2025_04_21_11_create_product_images_table', 1),
(13, '2025_04_21_12_create_categories_table', 1),
(14, '2025_04_21_13_create_product_categories_table', 1),
(15, '2025_04_21_14_create_inventories_table', 1),
(16, '2025_04_21_15_create_price_histories_table', 1),
(17, '2025_04_21_16_create_promo_codes_table', 1),
(18, '2025_04_21_17_create_orders_table', 1),
(19, '2025_04_21_18_create_order_items_table', 1),
(20, '2025_04_21_19_create_payments_table', 1),
(21, '2025_04_21_20_create_shipments_table', 1),
(22, '2025_04_21_21_create_wishlists_table', 1),
(23, '2025_04_21_22_create_wishlist_items_table', 1),
(24, '2025_04_21_23_create_reviews_table', 1),
(25, '2025_04_21_24_create_support_tickets_table', 1),
(26, '2025_04_25_25_add_soft_deletes_to_all_tables', 1),
(27, '2025_04_25_26_create_personal_access_tokens_table', 1),
(28, '2025_04_28_27_normalize_returned_order_status', 1),
(29, '2025_05_18_142643_add_sale_price_to_products_table', 1),
(30, '2025_05_20_092041_add_view_count_to_products_table', 1),
(31, '2025_05_22_000000_create_cart_items_table', 1),
(32, '2025_05_22_000001_fix_zero_quantity_cart_items', 1),
(33, '2025_05_22_000002_modify_carts_table_nullable_product_id', 1),
(34, '2025_05_22_000003_alter_carts_product_id_nullable', 1),
(35, '2025_05_22_000004_add_status_to_carts_table', 1),
(36, '2025_05_23_add_missing_columns_to_orders_table', 1),
(37, '2025_05_24_000000_fix_order_table_structure', 1),
(38, '2025_05_24_000001_fix_order_trigger', 1),
(39, '2025_05_23_180511_add_cancellation_columns_to_orders_table', 2),
(40, '2025_05_23_185825_add_promo_code_id_to_carts_table', 3),
(41, '2025_05_23_190000_create_promo_code_usages_table', 4),
(42, '2025_05_23_191749_create_promo_code_usages_table', 5),
(43, '2025_05_24_081950_create_admin_password_reset_tokens_table', 6),
(44, '2025_05_24_083436_create_settings_table', 7),
(45, '2025_05_24_105813_add_default_value_to_resource_in_admin_audit_logs', 8),
(46, '2025_05_24_111758_add_in_stock_to_products_table', 9);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `order_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `shipping_street` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `shipping_city` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `shipping_state` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `shipping_postal_code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `shipping_country` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `billing_street` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `billing_city` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `billing_state` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `billing_postal_code` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `billing_country` char(2) COLLATE utf8mb4_general_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shipping` decimal(10,2) NOT NULL DEFAULT '0.00',
  `order_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','processing','shipped','delivered','cancelled','returned') COLLATE utf8mb4_general_ci NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `promo_code_id` bigint UNSIGNED DEFAULT NULL,
  `expected_delivery_date` timestamp NULL DEFAULT NULL,
  `admin_notes` text COLLATE utf8mb4_general_ci,
  `handled_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `notes` varchar(500) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancellation_reason` text COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`),
  KEY `orders_user_id_index` (`user_id`),
  KEY `orders_user_id_order_date_index` (`user_id`,`order_date`),
  KEY `orders_status_index` (`status`),
  KEY `orders_user_id_status_order_date_index` (`user_id`,`status`,`order_date`),
  KEY `orders_promo_code_id_index` (`promo_code_id`),
  KEY `orders_handled_by_index` (`handled_by`),
  KEY `orders_order_date_status_index` (`order_date`,`status`),
  KEY `orders_user_id_order_date_status_total_amount_index` (`user_id`,`order_date`,`status`,`total_amount`),
  KEY `orders_order_date_index` (`order_date`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_number`, `shipping_street`, `shipping_city`, `shipping_state`, `shipping_postal_code`, `shipping_country`, `billing_street`, `billing_city`, `billing_state`, `billing_postal_code`, `billing_country`, `total_amount`, `subtotal`, `shipping`, `order_date`, `status`, `discount`, `promo_code_id`, `expected_delivery_date`, `admin_notes`, `handled_by`, `deleted_at`, `notes`, `cancelled_at`, `cancellation_reason`) VALUES
(1, 11, 'ORD-QAROAZ8L0Y', 'Taburbor', 'Amman', 'Amman', '09876', 'AL', 'Taburbor', 'Amman', 'Amman', '09876', 'AL', 129.96, 124.96, 5.00, '2025-05-23 14:31:13', 'cancelled', 0.00, NULL, '2025-05-30 14:31:13', NULL, NULL, NULL, NULL, '2025-05-23 15:17:58', NULL),
(2, 11, 'ORD-HMED26TAPV', 'Taburbor', 'Amman', 'Amman', '09876', 'AF', 'Taburbor', 'Amman', 'Amman', '09876', 'AF', 64.99, 59.99, 5.00, '2025-05-23 14:36:10', 'cancelled', 0.00, NULL, '2025-05-30 14:36:10', NULL, NULL, NULL, NULL, '2025-05-23 15:09:12', NULL),
(3, 11, 'ORD-8XR3B10LNV', 'Taburbor', 'Amman', 'Amman', '09876', 'AF', 'Taburbor', 'Amman', 'Amman', '09876', 'AF', 319.96, 314.96, 5.00, '2025-05-23 14:38:23', 'cancelled', 0.00, NULL, '2025-05-30 14:38:23', NULL, NULL, NULL, NULL, '2025-05-23 15:07:47', NULL),
(4, 11, 'ORD-VWDTZPHZ6B', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 44.99, 39.99, 5.00, '2025-05-23 14:45:10', 'cancelled', 0.00, NULL, '2025-05-30 14:45:10', NULL, NULL, NULL, NULL, '2025-05-23 15:08:00', NULL),
(5, 11, 'ORD-TU9PRPHHBQ', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 29.99, 24.99, 5.00, '2025-05-23 14:47:34', 'cancelled', 0.00, NULL, '2025-05-30 14:47:34', NULL, NULL, NULL, NULL, '2025-05-23 15:17:50', NULL),
(6, 11, 'ORD-X5CCKPYJZ6', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 104.99, 99.99, 5.00, '2025-05-23 14:50:58', 'cancelled', 0.00, NULL, '2025-05-30 14:50:58', NULL, NULL, NULL, NULL, '2025-05-23 15:11:42', NULL),
(7, 11, 'ORD-MLNRVTYY1I', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 94.99, 89.99, 5.00, '2025-05-23 14:54:51', 'cancelled', 0.00, NULL, '2025-05-30 14:54:51', NULL, NULL, NULL, NULL, '2025-05-23 15:09:41', NULL),
(8, 11, 'ORD-N5BLSOYZ9X', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AL', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AL', 94.99, 89.99, 5.00, '2025-05-23 15:18:39', 'processing', 0.00, NULL, '2025-05-30 15:18:39', NULL, NULL, NULL, NULL, NULL, NULL),
(9, 11, 'ORD-FF0ATDISRN', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 139.98, 179.98, 5.00, '2025-05-23 16:05:31', 'processing', 45.00, NULL, '2025-05-30 16:05:31', NULL, NULL, NULL, NULL, NULL, NULL),
(10, 11, 'ORD-HU1K2SV5LB', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AL', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AL', 64.99, 79.99, 5.00, '2025-05-23 17:58:40', 'processing', 20.00, NULL, '2025-05-30 17:58:40', NULL, NULL, NULL, NULL, NULL, NULL),
(11, 11, 'ORD-DO9SNIUY4V', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 124.99, 159.98, 5.00, '2025-05-23 18:12:06', 'processing', 40.00, NULL, '2025-05-30 18:12:06', NULL, NULL, NULL, NULL, NULL, NULL),
(12, 11, 'ORD-INRWWVLO9E', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 544.92, 639.92, 5.00, '2025-05-23 18:13:10', 'processing', 100.00, NULL, '2025-05-30 18:13:10', NULL, NULL, NULL, NULL, NULL, NULL),
(13, 11, 'ORD-SDHQSMVN8Z', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 804.90, 799.90, 5.00, '2025-05-23 18:14:05', 'processing', 0.00, NULL, '2025-05-30 18:14:05', NULL, NULL, NULL, NULL, NULL, NULL),
(14, 11, 'ORD-A9QOQYE4JM', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 1064.88, 1059.88, 5.00, '2025-05-24 09:03:01', 'cancelled', 0.00, NULL, '2025-05-31 09:03:01', NULL, NULL, NULL, NULL, '2025-05-24 09:03:20', NULL),
(15, 11, 'ORD-L0431YCU1Z', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 384.93, 379.93, 5.00, '2025-05-25 06:08:53', 'processing', 0.00, NULL, '2025-06-01 06:08:53', NULL, NULL, NULL, NULL, NULL, NULL),
(16, 11, 'ORD-DYH5DSYHSJ', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 6804.93, 6799.93, 5.00, '2025-05-25 06:51:53', 'processing', 0.00, NULL, '2025-06-01 06:51:53', NULL, NULL, NULL, NULL, NULL, NULL),
(17, 11, 'ORD-KMPT1NTMHM', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 2454.98, 2449.98, 5.00, '2025-05-25 06:53:32', 'cancelled', 0.00, NULL, '2025-06-01 06:53:32', NULL, NULL, NULL, NULL, NULL, NULL),
(18, 11, 'ORD-UOWEGJMMSZ', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 2483.76, 2478.76, 5.00, '2025-05-25 06:58:21', 'processing', 0.00, NULL, '2025-06-01 06:58:21', NULL, NULL, NULL, NULL, NULL, NULL),
(19, 11, 'ORD-ID5Q2MCG1L', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AL', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AL', 122.97, 117.97, 5.00, '2025-05-25 06:59:38', 'delivered', 0.00, NULL, '2025-06-01 06:59:38', NULL, NULL, NULL, NULL, NULL, NULL),
(20, 11, 'ORD-LRWWXEDBSR', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 229.94, 224.94, 5.00, '2025-05-25 07:07:11', 'shipped', 0.00, NULL, '2025-06-01 07:07:11', NULL, NULL, NULL, NULL, NULL, NULL),
(21, 11, 'ORD-JGOPXAFD6E', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 5344.82, 5339.82, 5.00, '2025-05-25 08:10:40', 'shipped', 0.00, NULL, '2025-06-01 08:10:40', NULL, NULL, NULL, NULL, NULL, NULL),
(22, 11, 'ORD-ACOURYDK1N', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'AF', 43073.28, 43068.28, 5.00, '2025-04-25 08:27:06', 'delivered', 0.00, NULL, '2025-06-01 08:27:06', NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Triggers `orders`
--
DROP TRIGGER IF EXISTS `before_order_amount_check`;
DELIMITER $$
CREATE TRIGGER `before_order_amount_check` BEFORE INSERT ON `orders` FOR EACH ROW BEGIN
                IF NEW.total_amount < 0 OR NEW.discount < 0 THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Order amounts cannot be negative";
                END IF;
            END
$$
DELIMITER ;
DROP TRIGGER IF EXISTS `before_order_insert_promo`;
DELIMITER $$
CREATE TRIGGER `before_order_insert_promo` BEFORE INSERT ON `orders` FOR EACH ROW BEGIN
                IF NEW.promo_code_id IS NOT NULL THEN
                    IF (SELECT valid_until FROM promo_codes 
                        WHERE id = NEW.promo_code_id) < NOW() THEN
                        SIGNAL SQLSTATE "45000"
                        SET MESSAGE_TEXT = "Promo code has expired";
                    END IF;
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `tax_rate` decimal(5,4) NOT NULL DEFAULT '0.0000',
  `tax_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `tax_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `tax_region` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_items_order_id_index` (`order_id`),
  KEY `order_items_product_id_index` (`product_id`),
  KEY `order_items_variant_id_index` (`variant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `variant_id`, `quantity`, `price`, `tax_rate`, `tax_amount`, `tax_name`, `tax_region`, `discount_amount`, `deleted_at`) VALUES
(1, 1, 1, NULL, 2, 24.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(2, 1, 30, NULL, 1, 39.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(3, 1, 73, NULL, 1, 34.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(4, 2, 3, NULL, 1, 59.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(5, 3, 1, NULL, 1, 24.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(6, 3, 2, NULL, 1, 89.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(7, 3, 33, NULL, 1, 129.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(8, 3, 35, NULL, 1, 69.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(9, 4, 30, NULL, 1, 39.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(10, 5, 1, NULL, 1, 24.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(11, 6, 5, NULL, 1, 99.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(12, 7, 2, NULL, 1, 89.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(13, 8, 2, NULL, 1, 89.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(14, 9, 2, NULL, 2, 89.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(15, 10, 2, NULL, 1, 79.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(16, 11, 2, NULL, 2, 79.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(17, 12, 2, NULL, 8, 79.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(18, 13, 2, NULL, 10, 79.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(19, 14, 2, NULL, 3, 79.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(20, 14, 1, NULL, 2, 19.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(21, 14, 6, NULL, 1, 59.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(22, 14, 4, NULL, 1, 69.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(23, 14, 10, NULL, 1, 49.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(24, 14, 11, NULL, 1, 299.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(25, 14, 12, NULL, 1, 249.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(26, 14, 30, NULL, 1, 39.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(27, 14, 48, NULL, 1, 89.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(28, 15, 1, NULL, 2, 19.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(29, 15, 2, NULL, 3, 79.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(30, 15, 3, NULL, 2, 49.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(31, 16, 91, NULL, 2, 799.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(32, 16, 92, NULL, 3, 1299.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(33, 16, 93, NULL, 2, 649.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(34, 17, 112, NULL, 1, 1499.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(35, 17, 113, NULL, 1, 949.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(36, 18, 132, NULL, 124, 19.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(37, 19, 141, NULL, 1, 54.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(38, 19, 142, NULL, 1, 44.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(39, 19, 143, NULL, 1, 17.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(40, 20, 71, NULL, 1, 34.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(41, 20, 72, NULL, 1, 44.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(42, 20, 73, NULL, 1, 29.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(43, 20, 77, NULL, 1, 49.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(44, 20, 78, NULL, 1, 34.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(45, 20, 79, NULL, 1, 29.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(46, 21, 2, NULL, 1, 79.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(47, 21, 25, NULL, 1, 99.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(48, 21, 24, NULL, 1, 79.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(49, 21, 41, NULL, 1, 259.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(50, 21, 40, NULL, 1, 69.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(51, 21, 42, NULL, 1, 69.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(52, 21, 101, NULL, 1, 949.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(53, 21, 102, NULL, 1, 449.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(54, 21, 103, NULL, 1, 229.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(55, 21, 105, NULL, 1, 849.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(56, 21, 104, NULL, 1, 1399.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(57, 21, 106, NULL, 1, 549.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(58, 21, 75, NULL, 1, 69.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(59, 21, 74, NULL, 1, 24.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(60, 21, 76, NULL, 1, 39.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(61, 21, 79, NULL, 1, 29.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(62, 21, 78, NULL, 1, 34.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(63, 21, 77, NULL, 1, 49.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(64, 22, 1, NULL, 2, 19.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(65, 22, 2, NULL, 3, 79.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(66, 22, 3, NULL, 3, 49.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL),
(67, 22, 81, NULL, 164, 259.99, 0.0000, 0.00, NULL, NULL, 0.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `admin_id` bigint UNSIGNED DEFAULT NULL,
  `token_hash` varchar(97) COLLATE utf8mb4_general_ci NOT NULL,
  `request_ip` varchar(45) COLLATE utf8mb4_general_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `is_used` tinyint(1) NOT NULL DEFAULT '0',
  `used_at` timestamp NULL DEFAULT NULL,
  `reset_type` enum('user','admin') COLLATE utf8mb4_general_ci NOT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `password_reset_tokens_user_id_index` (`user_id`),
  KEY `password_reset_tokens_admin_id_index` (`admin_id`),
  KEY `password_reset_tokens_token_hash_index` (`token_hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('credit_card','paypal','bank_transfer','crypto') COLLATE utf8mb4_general_ci NOT NULL,
  `payment_processor` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','completed','failed','refunded') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `refund_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `refund_reason` text COLLATE utf8mb4_general_ci,
  `processed_by` bigint UNSIGNED DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_order_id_index` (`order_id`),
  KEY `payments_status_index` (`status`),
  KEY `payments_transaction_id_index` (`transaction_id`),
  KEY `payments_processed_by_index` (`processed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `amount`, `payment_method`, `payment_processor`, `transaction_id`, `status`, `created_at`, `updated_at`, `refund_id`, `refund_reason`, `processed_by`, `deleted_at`) VALUES
(1, 1, 129.96, 'bank_transfer', 'bank', 'sim_QdoXW5lD8TRFeJHHC3KwjxCI', 'completed', '2025-05-23 14:31:13', '2025-05-23 14:31:13', NULL, NULL, NULL, NULL),
(2, 2, 64.99, 'paypal', 'paypal', 'sim_jnbB5BABLnANCGV7rnWG88Oq', 'completed', '2025-05-23 14:36:10', '2025-05-23 14:36:10', NULL, NULL, NULL, NULL),
(3, 3, 319.96, 'bank_transfer', 'bank', 'sim_lPfKlSdWfrcYRwb7LHnslLsw', 'completed', '2025-05-23 14:38:23', '2025-05-23 14:38:23', NULL, NULL, NULL, NULL),
(4, 4, 44.99, 'bank_transfer', 'bank', 'sim_sDBWvFph3Lokp6GBNzammvkO', 'completed', '2025-05-23 14:45:10', '2025-05-23 14:45:10', NULL, NULL, NULL, NULL),
(5, 5, 29.99, 'bank_transfer', 'bank', 'sim_R7ZA1Knx2PRpg7ZcvChGDlUT', 'completed', '2025-05-23 14:47:34', '2025-05-23 14:47:34', NULL, NULL, NULL, NULL),
(6, 6, 104.99, 'bank_transfer', 'bank', 'sim_cZacBchk55V4lUtP3M2mCQdt', 'completed', '2025-05-23 14:50:58', '2025-05-23 14:50:58', NULL, NULL, NULL, NULL),
(7, 7, 94.99, 'bank_transfer', 'bank', 'sim_8BLYRbZMiQvqJ5E3eOCMmKGC', 'completed', '2025-05-23 14:54:51', '2025-05-23 14:54:51', NULL, NULL, NULL, NULL),
(8, 8, 94.99, 'bank_transfer', 'bank', 'sim_Ysm9YsdCiHRfDn4AWI9gZRQH', 'completed', '2025-05-23 15:18:40', '2025-05-23 15:18:40', NULL, NULL, NULL, NULL),
(9, 9, 139.98, 'bank_transfer', 'bank', 'sim_MzIwk6fXlTbOopBRZfYC2nMk', 'completed', '2025-05-23 16:05:31', '2025-05-23 16:05:31', NULL, NULL, NULL, NULL),
(10, 10, 64.99, 'bank_transfer', 'bank', 'sim_mI1AHeGwPY3rf54rhjLlZkO0', 'completed', '2025-05-23 17:58:40', '2025-05-23 17:58:40', NULL, NULL, NULL, NULL),
(11, 11, 124.99, 'bank_transfer', 'bank', 'sim_GB9D33iqzARtDxn0rD7t2EfB', 'completed', '2025-05-23 18:12:06', '2025-05-23 18:12:06', NULL, NULL, NULL, NULL),
(12, 12, 544.92, 'bank_transfer', 'bank', 'sim_DMcuFKROqksvgrw68FuJzJP1', 'completed', '2025-05-23 18:13:10', '2025-05-23 18:13:10', NULL, NULL, NULL, NULL),
(13, 13, 804.90, 'paypal', 'paypal', 'sim_IGzIPuQtcXHrcIZ7c9w1a542', 'completed', '2025-05-23 18:14:05', '2025-05-23 18:14:05', NULL, NULL, NULL, NULL),
(14, 14, 1064.88, 'bank_transfer', 'bank', 'sim_ZO6sjGBHsOZ357y3fjvofwjn', 'completed', '2025-05-24 09:03:01', '2025-05-24 09:03:01', NULL, NULL, NULL, NULL),
(15, 15, 384.93, 'bank_transfer', 'bank', 'sim_bOLB3yhi1x7j4Uj2jQ8C0LKy', 'completed', '2025-05-25 06:08:53', '2025-05-25 06:08:53', NULL, NULL, NULL, NULL),
(16, 16, 6804.93, 'bank_transfer', 'bank', 'sim_ocXxXnxHnRG5CzpdCteoXbGG', 'completed', '2025-05-25 06:51:53', '2025-05-25 06:51:53', NULL, NULL, NULL, NULL),
(17, 17, 2454.98, 'bank_transfer', 'bank', 'sim_quURwozvcLoiOQnhHBAM7FF4', 'completed', '2025-05-25 06:53:32', '2025-05-25 06:53:32', NULL, NULL, NULL, NULL),
(18, 18, 2483.76, 'bank_transfer', 'bank', 'sim_Rh3KkfVRCKIiiGPKyt653xda', 'completed', '2025-05-25 06:58:21', '2025-05-25 06:58:21', NULL, NULL, NULL, NULL),
(19, 19, 122.97, 'bank_transfer', 'bank', 'sim_Xig5w6ScRqv2q7eY4Uu5QTyF', 'completed', '2025-05-25 06:59:38', '2025-05-25 06:59:38', NULL, NULL, NULL, NULL),
(20, 20, 229.94, 'paypal', 'paypal', 'sim_CnCTqaWMYiyMJn2BwLJWU0IB', 'completed', '2025-05-25 07:07:12', '2025-05-25 07:07:12', NULL, NULL, NULL, NULL),
(21, 21, 5344.82, 'bank_transfer', 'bank', 'sim_wmcAhbg0sFnCYa3QqnSQJEye', 'completed', '2025-05-25 08:10:40', '2025-05-25 08:10:40', NULL, NULL, NULL, NULL),
(22, 22, 43073.28, 'bank_transfer', 'bank', 'sim_HniwAMYP1ycVKLL0mitmjxzX', 'completed', '2025-05-25 08:27:06', '2025-05-25 08:27:06', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_general_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_general_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `price_histories`
--

DROP TABLE IF EXISTS `price_histories`;
CREATE TABLE IF NOT EXISTS `price_histories` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `previous_price` decimal(10,2) NOT NULL,
  `new_price` decimal(10,2) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `changed_by` bigint UNSIGNED NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `price_histories_product_id_index` (`product_id`),
  KEY `price_histories_updated_at_index` (`updated_at`),
  KEY `price_histories_variant_id_index` (`variant_id`),
  KEY `price_histories_changed_by_index` (`changed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `description` text COLLATE utf8mb4_general_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL,
  `sku` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `vendor_id` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `status` enum('active','draft','discontinued') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `in_stock` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Indicates whether the product is in stock',
  `weight` decimal(8,2) DEFAULT NULL,
  `dimensions` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `warranty_info` text COLLATE utf8mb4_general_ci,
  `return_policy` text COLLATE utf8mb4_general_ci,
  `view_count` int UNSIGNED NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `products_sku_unique` (`sku`),
  KEY `products_vendor_id_index` (`vendor_id`),
  KEY `products_created_by_index` (`created_by`),
  KEY `products_updated_by_index` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `sale_price`, `sku`, `vendor_id`, `created_at`, `updated_at`, `created_by`, `updated_by`, `status`, `in_stock`, `weight`, `dimensions`, `warranty_info`, `return_policy`, `view_count`, `deleted_at`) VALUES
(1, 'Classic Cotton T-Shirt', 'A comfortable, breathable cotton t-shirt perfect for everyday wear. Features a regular fit and crew neckline.', 24.99, 19.99, 'MC-TSHIRT-001', 1, '2025-05-23 15:03:38', '2025-05-25 08:27:06', 1, NULL, 'active', 1, 0.20, '30x20x2 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1064, NULL),
(2, 'Premium Denim Jeans', 'High-quality denim jeans with a modern slim fit. Features five pockets and a comfortable stretch fabric.', 89.99, 79.99, 'MC-JEANS-001', 1, '2025-05-23 15:03:38', '2025-05-25 08:27:06', 1, NULL, 'active', 1, 0.60, '40x30x5 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 823, NULL),
(3, 'Button-Down Oxford Shirt', 'A classic Oxford button-down shirt made from premium cotton. Perfect for both casual and semi-formal occasions.', 59.99, 49.99, 'MC-OXFORD-001', 1, '2025-05-23 15:03:38', '2025-05-25 08:27:06', 1, NULL, 'active', 1, 0.30, '35x25x3 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 837, NULL),
(4, 'Merino Wool Sweater', 'Luxurious Merino wool sweater featuring a crew neck design. Keeps you warm while remaining breathable.', 69.99, 59.99, 'MC-SWEATER-001', 1, '2025-05-23 15:03:38', '2025-05-24 09:03:01', 1, NULL, 'active', 1, 0.40, '35x25x4 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1695, NULL),
(5, 'Lightweight Bomber Jacket', 'Stylish bomber jacket with ribbed collar, cuffs, and hem. Features a front zip closure and two side pockets.', 99.99, 89.99, 'MC-JACKET-001', 1, '2025-05-23 15:03:38', '2025-05-23 12:37:57', 1, NULL, 'active', 0, 0.80, '45x35x5 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1917, NULL),
(6, 'Chino Trousers', 'Classic chino trousers made from premium cotton twill. Features a regular fit and flat front design.', 69.99, 59.99, 'MC-CHINO-001', 1, '2025-05-23 15:03:38', '2025-05-24 09:03:01', 1, NULL, 'active', 1, 0.50, '40x30x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 435, NULL),
(7, 'Athletic Performance Polo', 'Moisture-wicking polo shirt designed for both sport and casual wear. Features a three-button placket and ribbed collar.', 39.99, 34.99, 'MC-POLO-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.30, '35x25x3 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 380, NULL),
(8, 'Casual Linen Shirt', 'Breathable linen shirt perfect for warm weather. Features a relaxed fit and button-down collar.', 59.99, 49.99, 'MC-LINEN-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.30, '35x25x3 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 3157, NULL),
(9, 'Cargo Pants', 'Durable cargo pants with multiple pockets. Made from hard-wearing cotton canvas with a regular fit.', 79.99, 69.99, 'MC-CARGO-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.70, '40x30x5 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1547, NULL),
(10, 'Hooded Sweatshirt', 'Cozy hooded sweatshirt made from soft cotton blend. Features a kangaroo pocket and adjustable drawstring hood.', 49.99, 44.99, 'MC-HOODIE-001', 1, '2025-05-23 15:03:38', '2025-05-24 09:03:01', 1, NULL, 'active', 1, 0.50, '40x30x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 2043, NULL),
(11, 'Classic Three-Piece Suit', 'Elegant three-piece suit including jacket, vest, and trousers. Made from premium wool blend with a modern fit.', 299.99, 269.99, 'MS-3PIECE-001', 1, '2025-05-23 15:03:38', '2025-05-24 16:21:06', 1, NULL, 'active', 1, 2.00, '50x40x10 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1524, NULL),
(12, 'Business Slim Fit Suit', 'Modern slim-fit suit perfect for business meetings and formal occasions. Made from high-quality wool with subtle texture.', 249.99, 229.99, 'MS-BUSINESS-001', 1, '2025-05-23 15:03:38', '2025-05-24 09:03:01', 1, NULL, 'active', 1, 1.80, '50x40x10 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1431, NULL),
(13, 'Casual Linen Blazer', 'Lightweight linen blazer perfect for summer events. Features a half lining and relaxed fit for comfort.', 159.99, 139.99, 'MS-BLAZER-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.90, '50x40x5 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 535, NULL),
(14, 'Formal Tuxedo', 'Elegant black tuxedo with satin lapels. Perfect for black tie events and formal occasions.', 349.99, 299.99, 'MS-TUXEDO-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 2.20, '50x40x10 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 338, NULL),
(15, 'Double-Breasted Suit', 'Sophisticated double-breasted suit with peak lapels. Made from premium wool for a luxurious feel and look.', 279.99, 249.99, 'MS-DBREASTED-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 2.10, '50x40x10 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 2036, NULL),
(16, 'Pinstripe Business Suit', 'Classic pinstripe suit for a distinguished professional look. Features a modern fit and high-quality wool blend.', 269.99, 239.99, 'MS-PINSTRIPE-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 2.00, '50x40x10 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1115, NULL),
(17, 'Summer Weight Suit', 'Lightweight suit perfect for warm weather. Made from breathable wool blend with a half lining for comfort.', 229.99, 199.99, 'MS-SUMMER-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 1.70, '50x40x10 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1417, NULL),
(18, 'Navy Blue Wool Suit', 'Versatile navy blue suit made from fine wool. Features a classic fit suitable for many occasions.', 259.99, 229.99, 'MS-NAVY-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 1.90, '50x40x10 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1693, NULL),
(19, 'Charcoal Gray Suit', 'Professional charcoal gray suit with a modern cut. Made from durable wool blend perfect for everyday business wear.', 249.99, 219.99, 'MS-CHARCOAL-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 1.90, '50x40x10 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 162, NULL),
(20, 'Wedding Suit', 'Special occasion suit in light beige. Perfect for summer weddings and formal daytime events.', 289.99, 259.99, 'MS-WEDDING-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 2.00, '50x40x10 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1684, NULL),
(21, 'Polarized Aviator Sunglasses', 'Classic aviator sunglasses with polarized lenses for UV protection. Features a metal frame and comfortable nose pads.', 129.99, 99.99, 'MA-SUNGLASS-001', 6, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.10, '15x5x5 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 3526, NULL),
(22, 'Automatic Chronograph Watch', 'Sophisticated automatic watch with chronograph function. Features a stainless steel case and leather strap.', 299.99, 259.99, 'MA-WATCH-001', 6, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.20, '10x10x5 cm', '2-year international warranty', 'Returnable within 30 days if unused and in original packaging', 322, NULL),
(23, 'Wool Fedora Hat', 'Classic wool fedora hat with a grosgrain ribbon band. Perfect for adding style to any outfit.', 79.99, 69.99, 'MA-HAT-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.30, '25x25x15 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1908, NULL),
(24, 'Italian Leather Belt', 'Premium Italian leather belt with a classic buckle. Available in black and brown.', 89.99, 79.99, 'MA-BELT-001', 1, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 1, NULL, 'active', 1, 0.20, '120x3x0.5 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 522, NULL),
(25, 'Silk Necktie Collection', 'Set of three silk neckties in various patterns. Perfect for professional and formal occasions.', 119.99, 99.99, 'MA-TIE-001', 1, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 1, NULL, 'active', 1, 0.30, '30x8x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 838, NULL),
(26, 'Leather Bifold Wallet', 'Slim leather bifold wallet with multiple card slots and a bill compartment. Features RFID blocking technology.', 59.99, 49.99, 'MA-WALLET-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.10, '10x12x1 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 567, NULL),
(27, 'Cashmere Scarf', 'Luxurious cashmere scarf in a variety of colors. Provides warmth and style during colder months.', 79.99, 69.99, 'MA-SCARF-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.20, '180x25x1 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 276, NULL),
(28, 'Cufflink and Tie Bar Set', 'Elegant set including stainless steel cufflinks and a matching tie bar. Perfect for formal occasions.', 69.99, 59.99, 'MA-CUFFLINK-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.10, '10x5x3 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1631, NULL),
(29, 'Leather Gloves', 'Premium leather gloves with cashmere lining for warmth and comfort during winter months.', 89.99, 79.99, 'MA-GLOVES-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.20, '25x10x3 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1279, NULL),
(30, 'Business Card Holder', 'Sleek metal and leather business card holder. Perfect for networking and maintaining a professional appearance.', 39.99, 34.99, 'MA-CARDHOLDER-001', 1, '2025-05-23 15:03:38', '2025-05-24 09:03:01', 1, NULL, 'active', 1, 0.10, '10x7x1 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1450, NULL),
(31, 'Cotton Blouse', 'Elegant cotton blouse with button-front closure. Perfect for professional settings or casual outings.', 49.99, 39.99, 'WC-BLOUSE-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.20, '30x25x2 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1363, NULL),
(32, 'Slim Fit Denim Jeans', 'Modern slim fit jeans with slight stretch for comfort. Features a mid-rise waist and five-pocket styling.', 79.99, 69.99, 'WC-JEANS-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.50, '35x25x5 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 415, NULL),
(33, 'Cashmere Cardigan', 'Soft cashmere cardigan with front button closure. Perfect layering piece for all seasons.', 129.99, 109.99, 'WC-CARDIGAN-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.40, '35x25x3 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1936, NULL),
(34, 'Tailored Blazer', 'Professional blazer with structured silhouette. Features notched lapels and front button closure.', 149.99, 129.99, 'WC-BLAZER-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.70, '40x30x5 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 388, NULL),
(35, 'Pleated Midi Skirt', 'Elegant pleated midi skirt with elastic waistband. Perfect for both casual and semi-formal occasions.', 69.99, 59.99, 'WC-SKIRT-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.40, '35x25x3 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 83, NULL),
(36, 'Silk Camisole', 'Luxurious silk camisole with adjustable straps. Perfect as a base layer or standalone piece.', 59.99, 49.99, 'WC-CAMISOLE-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.10, '25x20x1 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1199, NULL),
(37, 'Wool Blend Trousers', 'Classic wool blend trousers with straight leg design. Features side pockets and flattering fit.', 89.99, 79.99, 'WC-TROUSERS-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.50, '40x30x5 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1697, NULL),
(38, 'Cotton T-shirt', 'Soft cotton t-shirt with crew neckline. Essential basic piece for any wardrobe.', 24.99, 19.99, 'WC-TSHIRT-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.20, '30x25x2 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 838, NULL),
(39, 'Cropped Pants', 'Modern cropped pants with side pockets. Perfect for spring and summer seasons.', 69.99, 59.99, 'WC-CROPPED-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.40, '35x25x4 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1049, NULL),
(40, 'Knitwear Sweater', 'Cozy knitwear sweater with ribbed cuffs and hem. Perfect for keeping warm during colder months.', 79.99, 69.99, 'WC-SWEATER-001', 1, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 2, NULL, 'active', 1, 0.50, '35x25x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 683, NULL),
(41, 'Evening Gown', 'Elegant floor-length evening gown with sequin embellishments. Perfect for formal events and black-tie occasions.', 299.99, 259.99, 'WD-EVENING-001', 1, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 2, NULL, 'active', 1, 1.20, '50x35x10 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 217, NULL),
(42, 'Casual Maxi Dress', 'Comfortable maxi dress with floral print. Features adjustable straps and side pockets.', 79.99, 69.99, 'WD-MAXI-001', 1, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 2, NULL, 'active', 1, 0.50, '40x30x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 4672, NULL),
(43, 'Cocktail Dress', 'Stylish knee-length cocktail dress with ruched detailing. Perfect for parties and semi-formal events.', 149.99, 129.99, 'WD-COCKTAIL-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.60, '40x30x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 235, NULL),
(44, 'Wrap Dress', 'Flattering wrap dress in solid color. Features V-neckline and tie closure at waist.', 89.99, 79.99, 'WD-WRAP-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.50, '40x30x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 5397, NULL),
(45, 'Shift Dress', 'Classic shift dress with clean lines and minimal detailing. Perfect for office or daytime events.', 99.99, 89.99, 'WD-SHIFT-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.50, '40x30x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 68, NULL),
(46, 'Summer Sundress', 'Lightweight sundress with adjustable straps and flowy skirt. Ideal for warm weather occasions.', 69.99, 59.99, 'WD-SUNDRESS-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.40, '40x30x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1799, NULL),
(47, 'Office Sheath Dress', 'Professional sheath dress with modest neckline and knee-length hem. Perfect for business settings.', 129.99, 109.99, 'WD-SHEATH-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.60, '40x30x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 5505, NULL),
(48, 'Bohemian Midi Dress', 'Free-spirited midi dress with embroidered details and tiered skirt. Perfect for casual outings.', 89.99, 79.99, 'WD-BOHO-001', 1, '2025-05-23 15:03:38', '2025-05-24 09:03:01', 2, NULL, 'active', 1, 0.50, '40x30x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 4849, NULL),
(49, 'Sweater Dress', 'Cozy sweater dress with ribbed cuffs and hem. Perfect for fall and winter seasons.', 99.99, 89.99, 'WD-SWEATER-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.70, '40x30x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 982, NULL),
(50, 'A-Line Dress', 'Classic A-line dress with fitted bodice and flared skirt. Versatile piece for many occasions.', 109.99, 99.99, 'WD-ALINE-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.60, '40x30x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 110, NULL),
(51, 'Premium Makeup Set', 'Complete makeup kit including foundation, eyeshadow palette, mascara, and lipstick. Perfect for everyday use.', 129.99, 109.99, 'WA-MAKEUP-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.50, '20x15x10 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unopened and in original packaging', 1555, NULL),
(52, 'Wide Brim Sun Hat', 'Stylish wide-brim sun hat with decorative band. Perfect for sun protection during summer months.', 59.99, 49.99, 'WA-HAT-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.30, '40x40x20 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1394, NULL),
(53, 'Pearl Necklace', 'Elegant freshwater pearl necklace with sterling silver clasp. Perfect for special occasions.', 149.99, 129.99, 'WA-NECKLACE-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.10, '5x5x3 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 256, NULL),
(54, 'Diamond Stud Earrings', 'Classic diamond stud earrings set in 14k white gold. Perfect for everyday wear or special occasions.', 299.99, 279.99, 'WA-EARRINGS-001', 1, '2025-05-23 15:03:38', '2025-05-24 08:55:53', 2, NULL, 'active', 0, 0.10, '5x5x3 cm', '2-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1051, NULL),
(55, 'Designer Handbag', 'Luxury leather handbag with multiple compartments and adjustable strap. Features gold-tone hardware.', 249.99, 229.99, 'WA-HANDBAG-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.80, '35x25x15 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 431, NULL),
(56, 'Silk Scarf', 'Luxurious silk scarf with artistic print. Perfect for adding style to any outfit.', 79.99, 69.99, 'WA-SCARF-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.10, '90x90x0.5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 955, NULL),
(57, 'Statement Bracelet', 'Bold statement bracelet featuring semi-precious stones. Perfect for adding interest to simple outfits.', 89.99, 79.99, 'WA-BRACELET-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.10, '7x7x3 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1435, NULL),
(58, 'Designer Sunglasses', 'Oversized designer sunglasses with UV protection. Features acetate frame and polarized lenses.', 159.99, 139.99, 'WA-SUNGLASSES-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.10, '15x5x5 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 258, NULL),
(59, 'Evening Clutch', 'Elegant evening clutch with rhinestone embellishments. Includes detachable chain strap.', 99.99, 89.99, 'WA-CLUTCH-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.30, '25x15x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 936, NULL),
(60, 'Leather Gloves', 'Soft leather gloves with cashmere lining. Perfect for cold weather while maintaining style.', 69.99, 59.99, 'WA-GLOVES-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 2, NULL, 'active', 0, 0.20, '25x10x3 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1855, NULL),
(61, 'Kids T-Shirt Set', 'Set of three colorful cotton t-shirts for children. Comfortable for everyday wear with fun prints.', 34.99, 29.99, 'KC-TSHIRT-001', 4, '2025-05-23 15:03:38', '2025-05-24 08:01:06', 1, NULL, 'active', 0, 0.30, '25x20x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 429, NULL),
(62, 'Children\'s Denim Jeans', 'Durable denim jeans with adjustable waistband. Features reinforced knees for active kids.', 39.99, 34.99, 'KC-JEANS-001', 4, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.40, '30x20x5 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 485, NULL),
(63, 'Kids Winter Jacket', 'Warm winter jacket with water-resistant exterior and fleece lining. Features reflective details for safety.', 79.99, 69.99, 'KC-JACKET-001', 4, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.60, '35x30x10 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 2022, NULL),
(64, 'Girls Party Dress', 'Beautiful party dress with tulle skirt and satin bow. Perfect for special occasions.', 49.99, 44.99, 'KC-DRESS-001', 4, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.40, '30x25x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 68, NULL),
(65, 'Boys Formal Suit', 'Three-piece formal suit for boys including jacket, vest, and pants. Perfect for special events.', 69.99, 59.99, 'KC-SUIT-001', 4, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.70, '35x30x10 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 946, NULL),
(66, 'Children\'s Pajama Set', 'Soft cotton pajama set featuring fun prints. Comfortable for sleeping and lounging.', 29.99, 24.99, 'KC-PAJAMA-001', 4, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.30, '30x25x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 479, NULL),
(67, 'Kids Raincoat', 'Waterproof raincoat with hood and reflective details. Features fun printed design.', 39.99, 34.99, 'KC-RAINCOAT-001', 4, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.40, '35x30x5 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1505, NULL),
(68, 'Children\'s Sweater', 'Warm knit sweater with fun animal design. Perfect for staying cozy during colder months.', 34.99, 29.99, 'KC-SWEATER-001', 4, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.30, '30x25x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 2039, NULL),
(69, 'Kids Athletic Set', 'Two-piece athletic set including shorts and t-shirt. Made from moisture-wicking material for active play.', 29.99, 24.99, 'KC-ATHLETIC-001', 4, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 1, NULL, 'active', 0, 0.30, '30x25x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 1629, NULL),
(70, 'Children\'s Swimwear', 'UV-protective swimwear for children. Quick-drying material with fun prints.', 24.99, 19.99, 'KC-SWIM-001', 4, '2025-05-23 15:03:38', '2025-05-23 12:59:37', 1, NULL, 'active', 0, 0.20, '25x20x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unworn and with tags attached', 5845, NULL),
(71, 'Educational Building Blocks', 'Set of 100 colorful building blocks for creative play. Helps develop fine motor skills and spatial awareness.', 39.99, 34.99, 'TY-BLOCKS-001', 4, '2025-05-23 15:03:38', '2025-05-25 07:07:11', 3, NULL, 'active', 1, 1.20, '30x25x15 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unopened and in original packaging', 972, NULL),
(72, 'Remote Control Car', 'High-speed remote control car with rechargeable battery. Features realistic sound effects and LED lights.', 49.99, 44.99, 'TY-RCCAR-001', 4, '2025-05-23 15:03:38', '2025-05-25 07:07:11', 3, NULL, 'active', 1, 0.80, '30x20x15 cm', '6-month warranty against manufacturing defects', 'Returnable within 30 days if unopened and in original packaging', 863, NULL),
(73, 'Stuffed Animal Collection', 'Set of five soft plush animals. Perfect for cuddling and imaginative play for all ages.', 34.99, 29.99, 'TY-PLUSH-001', 4, '2025-05-23 15:03:38', '2025-05-25 07:07:11', 3, NULL, 'active', 1, 0.50, '35x30x20 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unopened and in original packaging', 1349, NULL),
(74, 'Art and Craft Set', 'Complete art set including paints, markers, colored pencils, and more. Perfect for creative development.', 29.99, 24.99, 'TY-ART-001', 4, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 3, NULL, 'active', 1, 1.00, '35x25x10 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unopened and in original packaging', 2853, NULL),
(75, 'Interactive Learning Tablet', 'Child-friendly tablet with educational games and activities. Helps develop language, math, and logic skills.', 79.99, 69.99, 'TY-TABLET-001', 4, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 3, NULL, 'active', 1, 0.50, '25x20x5 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unopened and in original packaging', 450, NULL),
(76, 'Board Game Set', 'Collection of five classic board games for family fun. Includes chess, checkers, backgammon, and more.', 44.99, 39.99, 'TY-BOARD-001', 4, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 3, NULL, 'active', 1, 1.50, '40x30x10 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unopened and in original packaging', 1874, NULL),
(77, 'Wooden Train Set', 'Expandable wooden train set with tracks, trains, and accessories. Perfect for developing creativity.', 59.99, 49.99, 'TY-TRAIN-001', 4, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 3, NULL, 'active', 1, 2.00, '45x35x15 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unopened and in original packaging', 1967, NULL),
(78, 'Science Experiment Kit', 'Educational science kit with 50+ experiments. Great for learning basic scientific principles through play.', 39.99, 34.99, 'TY-SCIENCE-001', 4, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 3, NULL, 'active', 1, 1.20, '35x30x10 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unopened and in original packaging', 165, NULL),
(79, 'Musical Instrument Set', 'Collection of child-friendly musical instruments. Great for developing rhythm and musical interest.', 34.99, 29.99, 'TY-MUSIC-001', 4, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 3, NULL, 'active', 1, 1.00, '40x30x15 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unopened and in original packaging', 873, NULL),
(80, 'Outdoor Play Equipment', 'Set including jump rope, frisbee, ball, and more. Perfect for encouraging outdoor activity.', 29.99, 24.99, 'TY-OUTDOOR-001', 4, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 3, NULL, 'active', 0, 1.50, '45x35x20 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unopened and in original packaging', 1820, NULL),
(81, 'Luxury Area Rug', 'Hand-knotted wool area rug with intricate pattern. Adds warmth and style to any room.', 299.99, 259.99, 'HA-RUG-001', 3, '2025-05-23 15:03:38', '2025-05-25 08:27:06', 4, NULL, 'active', 0, 8.00, '200x150x2 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 444, NULL),
(82, 'Decorative Throw Pillows', 'Set of four coordinating throw pillows with removable covers. Perfect for adding style to sofas and beds.', 79.99, 69.99, 'HA-PILLOW-001', 3, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 4, NULL, 'active', 0, 2.00, '45x45x20 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 653, NULL),
(83, 'Blackout Curtains', 'Energy-efficient blackout curtains in various colors. Features grommets for easy installation.', 89.99, 79.99, 'HA-CURTAIN-001', 3, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 4, NULL, 'active', 0, 2.50, '240x140x5 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1667, NULL),
(84, 'Decorative Wall Art', 'Set of three coordinating canvas prints. Perfect for adding visual interest to any room.', 129.99, 109.99, 'HA-ART-001', 3, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 4, NULL, 'active', 0, 3.00, '40x50x5 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1578, NULL),
(85, 'Crystal Table Lamp', 'Elegant table lamp with crystal base and fabric shade. Adds sophisticated lighting to any space.', 149.99, 129.99, 'HA-LAMP-001', 3, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 4, NULL, 'active', 0, 2.50, '35x35x60 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 89, NULL),
(86, 'Ceramic Vase Set', 'Set of three coordinating ceramic vases in various sizes. Perfect for floral arrangements or as standalone decor.', 69.99, 59.99, 'HA-VASE-001', 3, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 4, NULL, 'active', 0, 2.00, '30x30x40 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1660, NULL),
(87, 'Luxury Bedding Set', 'Complete bedding set including duvet cover, sheets, and pillowcases. Made from premium cotton with high thread count.', 199.99, 179.99, 'HA-BEDDING-001', 3, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 4, NULL, 'active', 0, 3.50, '50x40x20 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1982, NULL),
(88, 'Decorative Wall Mirror', 'Elegant wall mirror with ornate frame. Perfect for adding depth and light to any room.', 159.99, 139.99, 'HA-MIRROR-001', 3, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 4, NULL, 'active', 0, 5.00, '90x60x5 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 884, NULL),
(89, 'Indoor Plant Collection', 'Set of three low-maintenance indoor plants in decorative pots. Perfect for adding natural elements to your decor.', 89.99, 79.99, 'HA-PLANT-001', 3, '2025-05-23 15:03:38', '2025-05-23 14:29:47', 4, NULL, 'active', 0, 4.00, '40x40x50 cm', '14-day warranty for plant health', 'Returnable within 14 days if plants are unhealthy', 424, NULL),
(90, 'Scented Candle Set', 'Set of four luxury scented candles in decorative glass containers. Features various fragrances for different moods.', 69.99, 59.99, 'HA-CANDLE-001', 3, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 4, NULL, 'active', 0, 1.50, '30x30x15 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1412, NULL),
(91, 'Smart 4K Television', '55-inch Smart 4K Ultra HD TV with HDR and built-in streaming apps. Features excellent picture quality and sound.', 899.99, 799.99, 'EL-TV-001', 2, '2025-05-23 15:03:38', '2025-05-25 06:51:53', 5, NULL, 'active', 1, 15.00, '123x71x6 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1743, NULL),
(92, 'Side-by-Side Refrigerator', 'Energy-efficient side-by-side refrigerator with water and ice dispenser. Features adjustable shelves and smart connectivity.', 1499.99, 1299.99, 'EL-FRIDGE-001', 2, '2025-05-23 15:03:38', '2025-05-25 06:51:53', 5, NULL, 'active', 1, 130.00, '90x180x70 cm', '5-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 431, NULL),
(93, 'Smart Oven', 'Advanced smart oven with multiple cooking modes. Features touch screen controls and app connectivity.', 699.99, 649.99, 'EL-OVEN-001', 2, '2025-05-23 15:03:38', '2025-05-25 06:51:53', 5, NULL, 'active', 1, 35.00, '60x60x85 cm', '3-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 872, NULL),
(94, 'Front-Load Washing Machine', 'High-efficiency front-load washer with multiple wash cycles. Features quiet operation and steam cleaning technology.', 799.99, 749.99, 'EL-WASHER-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 80.00, '85x60x60 cm', '3-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1015, NULL),
(95, 'Countertop Microwave', 'Stainless steel countertop microwave with multiple power levels and preset cooking options. Features digital display.', 149.99, 129.99, 'EL-MICRO-001', 2, '2025-05-23 15:03:38', '2025-05-23 14:29:33', 5, NULL, 'active', 0, 12.00, '45x35x25 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 413, NULL),
(96, 'Robot Vacuum Cleaner', 'Smart robot vacuum with mapping technology and app control. Features multiple cleaning modes and automatic recharging.', 299.99, 279.99, 'EL-VACUUM-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 3.50, '35x35x10 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 964, NULL),
(97, 'Air Purifier', 'HEPA air purifier for large rooms. Features quiet operation and real-time air quality monitoring.', 199.99, 179.99, 'EL-PURIFIER-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 6.00, '40x25x60 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1536, NULL),
(98, 'Coffee Maker', 'Programmable coffee maker with thermal carafe. Features multiple brew strengths and timer function.', 129.99, 109.99, 'EL-COFFEE-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 4.00, '30x25x40 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 739, NULL),
(99, 'Stand Mixer', 'Professional-grade stand mixer with multiple attachments. Features 10 speed settings and large capacity bowl.', 349.99, 299.99, 'EL-MIXER-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 9.00, '40x30x35 cm', '3-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1035, NULL),
(100, 'Smart Sound System', '5.1 channel home theater system with wireless speakers. Features Bluetooth connectivity and voice control.', 499.99, 449.99, 'EL-SOUND-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 15.00, '100x40x20 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 911, NULL),
(101, 'Premium Smartphone', 'Flagship smartphone with 6.7-inch OLED display, 256GB storage, and advanced camera system. Features 5G connectivity.', 999.99, 949.99, 'PH-SMART-001', 2, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 5, NULL, 'active', 1, 0.20, '16x7.5x0.8 cm', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1399, NULL),
(102, 'Mid-range Smartphone', 'Feature-rich smartphone with 6.4-inch LCD display, 128GB storage, and quad-camera system. Great value for price.', 499.99, 449.99, 'PH-SMART-002', 2, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 5, NULL, 'active', 1, 0.19, '15.5x7.2x0.9 cm', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 211, NULL),
(103, 'Budget Smartphone', 'Affordable smartphone with 6.1-inch display, 64GB storage, and dual camera. Perfect for basic needs.', 249.99, 229.99, 'PH-SMART-003', 2, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 5, NULL, 'active', 1, 0.18, '15x7x0.9 cm', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 811, NULL),
(104, 'Foldable Smartphone', 'Innovative foldable smartphone with dual screens. Features 512GB storage and professional-grade camera system.', 1499.99, 1399.99, 'PH-FOLD-001', 2, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 5, NULL, 'active', 1, 0.25, '16x7x1.5 cm (folded)', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1373, NULL),
(105, 'Large-Screen Smartphone', 'Extra-large 6.9-inch smartphone with stylus support. Perfect for productivity and entertainment.', 899.99, 849.99, 'PH-NOTE-001', 2, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 5, NULL, 'active', 1, 0.22, '17x8x0.9 cm', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 381, NULL),
(106, 'Rugged Smartphone', 'Water and shock-resistant smartphone designed for outdoor use. Features extra battery life and enhanced durability.', 599.99, 549.99, 'PH-RUGGED-001', 2, '2025-05-23 15:03:38', '2025-05-25 08:10:40', 5, NULL, 'active', 1, 0.25, '16x8x1.2 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1736, NULL),
(107, 'Gaming Smartphone', 'High-performance smartphone optimized for gaming. Features cooling system and dedicated gaming controls.', 799.99, 749.99, 'PH-GAMING-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 0.23, '17x8x1 cm', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 5408, NULL),
(108, 'Senior-Friendly Smartphone', 'Easy-to-use smartphone with simplified interface. Features large buttons and enhanced audio for seniors.', 299.99, 279.99, 'PH-SENIOR-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 0.20, '15.5x7.5x1 cm', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 182, NULL),
(109, 'Dual SIM Smartphone', 'Versatile smartphone supporting two SIM cards. Perfect for separating work and personal use.', 449.99, 399.99, 'PH-DUALSIM-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 0.19, '15.8x7.3x0.9 cm', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 395, NULL),
(110, 'Camera-focused Smartphone', 'Premium smartphone with professional-grade camera system. Perfect for photography enthusiasts.', 899.99, 849.99, 'PH-CAMERA-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 0.21, '16.2x7.6x0.9 cm', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1383, NULL),
(111, 'Premium Ultrabook', 'Ultra-thin and lightweight laptop with 14-inch 4K display and 512GB SSD. Perfect for professionals on the go.', 1299.99, 1199.99, 'LT-ULTRA-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 1.20, '32x22x1.5 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1678, NULL),
(112, 'Gaming Laptop', 'High-performance gaming laptop with dedicated graphics card and 16GB RAM. Features RGB keyboard and cooling system.', 1599.99, 1499.99, 'LT-GAMING-001', 2, '2025-05-23 15:03:38', '2025-05-25 06:53:32', 5, NULL, 'active', 1, 2.50, '36x26x2.5 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 191, NULL),
(113, 'Business Laptop', 'Professional laptop with enhanced security features and long battery life. Perfect for business users.', 999.99, 949.99, 'LT-BIZ-001', 2, '2025-05-23 15:03:38', '2025-05-25 06:53:32', 5, NULL, 'active', 1, 1.80, '34x24x2 cm', '3-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1871, NULL),
(114, 'Budget Laptop', 'Affordable laptop with 15.6-inch display and 256GB storage. Perfect for everyday computing needs.', 499.99, 449.99, 'LT-BUDGET-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 2.00, '36x25x2.2 cm', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 733, NULL),
(115, 'Student Laptop', 'Durable laptop designed for educational use. Features long battery life and productivity software.', 599.99, 549.99, 'LT-STUDENT-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 1.70, '33x23x2 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 2001, NULL),
(116, '2-in-1 Convertible Laptop', 'Versatile laptop with touchscreen that converts to tablet mode. Features stylus support for creative work.', 899.99, 849.99, 'LT-CONVERT-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 1.50, '33x22x1.8 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1758, NULL),
(117, 'Workstation Laptop', 'Professional-grade laptop for graphic design and video editing. Features high-performance processor and color-accurate display.', 1899.99, 1799.99, 'LT-WORK-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 2.80, '38x26x2.5 cm', '3-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 738, NULL),
(118, 'Chromebook', 'Lightweight laptop running Chrome OS. Perfect for web-based tasks and long battery life.', 349.99, 329.99, 'LT-CHROME-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 1.30, '30x20x1.8 cm', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 365, NULL),
(119, 'Mini Laptop', 'Ultra-portable 11-inch laptop weighing under 1kg. Perfect for travel and light computing needs.', 499.99, 479.99, 'LT-MINI-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 0.90, '28x19x1.5 cm', '1-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 1562, NULL),
(120, 'Large-Screen Laptop', '17-inch laptop with desktop-replacement capabilities. Features expansive display and full-size keyboard.', 1099.99, 999.99, 'LT-LARGE-001', 2, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 5, NULL, 'active', 0, 3.00, '40x28x2.5 cm', '2-year manufacturer warranty', 'Returnable within 30 days if unused and in original packaging', 665, NULL),
(121, 'Men\'s Running Shoes', 'Lightweight running shoes with responsive cushioning and breathable mesh upper. Perfect for long-distance running.', 129.99, 109.99, 'SH-MRUN-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 6, NULL, 'active', 0, 0.80, '30x20x15 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 590, NULL),
(122, 'Women\'s Running Shoes', 'Performance running shoes designed specifically for women. Features enhanced arch support and cushioning.', 129.99, 109.99, 'SH-WRUN-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 6, NULL, 'active', 0, 0.60, '28x18x15 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 906, NULL),
(123, 'Men\'s Dress Shoes', 'Classic leather dress shoes with comfortable insole. Perfect for formal occasions and professional settings.', 149.99, 129.99, 'SH-MDRESS-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 6, NULL, 'active', 0, 1.00, '32x20x15 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 711, NULL),
(124, 'Women\'s Heels', 'Elegant medium-height heels with cushioned insole. Perfect for professional settings and special occasions.', 99.99, 89.99, 'SH-WHEEL-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 6, NULL, 'active', 0, 0.70, '28x18x12 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 2497, NULL),
(125, 'Men\'s Casual Sneakers', 'Versatile casual sneakers with classic design. Perfect for everyday wear and casual outings.', 79.99, 69.99, 'SH-MCASUAL-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 6, NULL, 'active', 0, 0.90, '32x20x15 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1748, NULL),
(126, 'Women\'s Casual Sneakers', 'Stylish and comfortable casual sneakers for women. Features lightweight design and cushioned insole.', 79.99, 69.99, 'SH-WCASUAL-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 6, NULL, 'active', 0, 0.70, '28x18x15 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 335, NULL),
(127, 'Children\'s Sport Shoes', 'Durable sport shoes for kids with adjustable straps. Features non-marking soles and proper arch support.', 59.99, 49.99, 'SH-KSPORT-001', 4, '2025-05-23 15:03:38', '2025-05-23 12:59:34', 6, NULL, 'active', 0, 0.50, '24x15x12 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 380, NULL),
(128, 'Men\'s Hiking Boots', 'Waterproof hiking boots with ankle support and rugged traction. Perfect for outdoor adventures.', 159.99, 139.99, 'SH-MHIKE-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 6, NULL, 'active', 0, 1.20, '33x22x15 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 839, NULL),
(129, 'Women\'s Hiking Boots', 'Lightweight waterproof hiking boots designed for women. Features excellent grip and comfort for long hikes.', 159.99, 139.99, 'SH-WHIKE-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 6, NULL, 'active', 0, 1.00, '30x20x15 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1010, NULL),
(130, 'Unisex Sandals', 'Comfortable sandals with adjustable straps. Perfect for casual wear and beach outings.', 49.99, 39.99, 'SH-SANDAL-001', 1, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 6, NULL, 'active', 0, 0.60, '30x18x10 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 483, NULL),
(131, 'Business Strategy Guide', 'Comprehensive guide to modern business strategies. Perfect for entrepreneurs and business professionals.', 39.99, 34.99, 'BK-BIZ-001', 6, '2025-05-23 15:03:38', '2025-05-23 13:16:02', 7, NULL, 'active', 0, 0.70, '23x15x3 cm', NULL, 'Returnable within 30 days if in original condition', 1341, NULL),
(132, 'Contemporary Fiction Novel', 'Award-winning novel exploring themes of identity and belonging in modern society. Engaging storytelling with memorable characters.', 24.99, 19.99, 'BK-FICTION-001', 6, '2025-05-23 15:03:38', '2025-05-25 06:58:21', 7, NULL, 'active', 0, 0.50, '21x14x3 cm', NULL, 'Returnable within 30 days if in original condition', 1192, NULL),
(133, 'Cookbook Collection', 'Collection of 100 recipes from around the world. Features beautiful photography and step-by-step instructions.', 34.99, 29.99, 'BK-COOK-001', 6, '2025-05-23 15:03:38', '2025-05-23 13:27:40', 7, NULL, 'active', 0, 1.20, '28x22x3 cm', NULL, 'Returnable within 30 days if in original condition', 4183, NULL);
INSERT INTO `products` (`id`, `name`, `description`, `price`, `sale_price`, `sku`, `vendor_id`, `created_at`, `updated_at`, `created_by`, `updated_by`, `status`, `in_stock`, `weight`, `dimensions`, `warranty_info`, `return_policy`, `view_count`, `deleted_at`) VALUES
(134, 'Self-Help Bestseller', 'Popular self-help book focused on personal growth and mindfulness. Includes practical exercises and techniques.', 19.99, 17.99, 'BK-SELF-001', 6, '2025-05-23 15:03:38', '2025-05-23 13:12:25', 7, NULL, 'active', 0, 0.40, '21x14x2 cm', NULL, 'Returnable within 30 days if in original condition', 1746, NULL),
(135, 'Historical Biography', 'In-depth biography of a significant historical figure. Well-researched with new insights and historical context.', 29.99, 26.99, 'BK-BIO-001', 6, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 7, NULL, 'active', 0, 0.80, '24x16x4 cm', NULL, 'Returnable within 30 days if in original condition', 5202, NULL),
(136, 'Children\'s Picture Book', 'Beautifully illustrated picture book for young children. Features engaging story with educational elements.', 14.99, 12.99, 'BK-KIDS-001', 6, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 7, NULL, 'active', 0, 0.30, '25x25x1 cm', NULL, 'Returnable within 30 days if in original condition', 5411, NULL),
(137, 'Science & Technology Reference', 'Comprehensive reference guide to modern science and technology concepts. Perfect for students and professionals.', 49.99, 44.99, 'BK-SCI-001', 6, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 7, NULL, 'active', 0, 1.50, '28x22x4 cm', NULL, 'Returnable within 30 days if in original condition', 742, NULL),
(138, 'Travel Guide', 'Detailed travel guide featuring maps, recommendations, and cultural insights. Perfect for planning your next adventure.', 24.99, 21.99, 'BK-TRAVEL-001', 6, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 7, NULL, 'active', 0, 0.60, '22x15x2 cm', NULL, 'Returnable within 30 days if in original condition', 1688, NULL),
(139, 'Mystery Thriller', 'Page-turning thriller with unexpected twists and turns. From an award-winning mystery author.', 22.99, 19.99, 'BK-MYSTERY-001', 6, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 7, NULL, 'active', 0, 0.50, '21x14x3 cm', NULL, 'Returnable within 30 days if in original condition', 4771, NULL),
(140, 'Art & Photography Collection', 'Coffee table book featuring stunning photography and artistic works. Includes historical context and artist profiles.', 59.99, 54.99, 'BK-ART-001', 6, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 7, NULL, 'active', 0, 2.00, '30x25x3 cm', NULL, 'Returnable within 30 days if in original condition', 1712, NULL),
(141, 'Premium Dog Food', 'High-quality dog food with balanced nutrition. Features real meat as the first ingredient and no artificial preservatives.', 59.99, 54.99, 'PT-DOGFOOD-001', 5, '2025-05-23 15:03:38', '2025-05-25 06:59:38', 8, NULL, 'active', 1, 10.00, '40x30x15 cm', NULL, 'Returnable within 30 days if unopened and in original packaging', 2014, NULL),
(142, 'Premium Cat Food', 'Nutritionally complete cat food formulated for all life stages. Features real fish or poultry and essential vitamins.', 49.99, 44.99, 'PT-CATFOOD-001', 5, '2025-05-23 15:03:38', '2025-05-25 06:59:38', 8, NULL, 'active', 1, 5.00, '35x25x15 cm', NULL, 'Returnable within 30 days if unopened and in original packaging', 3869, NULL),
(143, 'Interactive Dog Toy', 'Durable interactive toy designed to keep dogs mentally stimulated and physically active.', 19.99, 17.99, 'PT-DOGTOY-001', 5, '2025-05-23 15:03:38', '2025-05-25 06:59:38', 8, NULL, 'active', 1, 0.30, '20x15x15 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 329, NULL),
(144, 'Cat Climbing Tree', 'Multi-level cat tree with scratching posts, perches, and hiding spots. Provides exercise and relaxation for cats.', 99.99, 89.99, 'PT-CATTREE-001', 5, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 8, NULL, 'active', 0, 15.00, '60x60x120 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 3952, NULL),
(145, 'Pet Bed', 'Comfortable pet bed with washable cover. Available in various sizes for different pets.', 39.99, 34.99, 'PT-BED-001', 5, '2025-05-23 15:03:38', '2025-05-23 14:29:56', 8, NULL, 'active', 0, 2.00, '70x50x15 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1691, NULL),
(146, 'Adjustable Pet Collar', 'Durable adjustable collar with quick-release buckle. Available in various colors and sizes.', 14.99, 12.99, 'PT-COLLAR-001', 5, '2025-05-23 15:03:38', '2025-05-23 14:30:00', 8, NULL, 'active', 0, 0.10, '15x10x3 cm', '30-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1567, NULL),
(147, 'Retractable Dog Leash', 'Durable retractable leash with comfortable grip and one-button brake system. Perfect for daily walks.', 24.99, 21.99, 'PT-LEASH-001', 5, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 8, NULL, 'active', 0, 0.40, '20x15x5 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 709, NULL),
(148, 'Automatic Pet Feeder', 'Programmable pet feeder with portion control. Features timer and multiple feeding schedules.', 79.99, 69.99, 'PT-FEEDER-001', 5, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 8, NULL, 'active', 0, 2.50, '30x20x40 cm', '1-year warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 4206, NULL),
(149, 'Pet Grooming Kit', 'Complete grooming kit including brushes, clippers, and nail trimmers. Perfect for at-home pet care.', 49.99, 44.99, 'PT-GROOM-001', 5, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 8, NULL, 'active', 0, 0.80, '25x20x10 cm', '60-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 1809, NULL),
(150, 'Fish Tank Starter Kit', 'Complete 20-gallon aquarium starter kit including tank, filter, lights, and basic accessories.', 129.99, 119.99, 'PT-FISH-001', 5, '2025-05-23 15:03:38', '2025-05-23 15:03:42', 8, NULL, 'active', 0, 8.00, '60x30x40 cm', '90-day warranty against manufacturing defects', 'Returnable within 30 days if unused and in original packaging', 5855, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

DROP TABLE IF EXISTS `product_categories`;
CREATE TABLE IF NOT EXISTS `product_categories` (
  `product_id` bigint UNSIGNED NOT NULL,
  `category_id` bigint UNSIGNED NOT NULL,
  `is_primary_category` tinyint(1) NOT NULL DEFAULT '0',
  `added_by` bigint UNSIGNED DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`product_id`,`category_id`),
  KEY `product_categories_category_id_index` (`category_id`),
  KEY `product_categories_added_by_index` (`added_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`product_id`, `category_id`, `is_primary_category`, `added_by`, `added_at`, `deleted_at`) VALUES
(1, 1, 1, 1, '2025-05-23 15:03:38', NULL),
(2, 1, 1, 1, '2025-05-23 15:03:38', NULL),
(3, 1, 1, 1, '2025-05-23 15:03:38', NULL),
(4, 1, 1, 1, '2025-05-23 15:03:38', NULL),
(5, 1, 1, 1, '2025-05-23 15:03:38', NULL),
(6, 1, 1, 1, '2025-05-23 15:03:38', NULL),
(7, 1, 1, 1, '2025-05-23 15:03:38', NULL),
(8, 1, 1, 1, '2025-05-23 15:03:38', NULL),
(9, 1, 1, 1, '2025-05-23 15:03:38', NULL),
(10, 1, 1, 1, '2025-05-23 15:03:38', NULL),
(11, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(11, 3, 1, 1, '2025-05-23 15:03:38', NULL),
(12, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(12, 3, 1, 1, '2025-05-23 15:03:38', NULL),
(13, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(13, 3, 1, 1, '2025-05-23 15:03:38', NULL),
(14, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(14, 3, 1, 1, '2025-05-23 15:03:38', NULL),
(15, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(15, 3, 1, 1, '2025-05-23 15:03:38', NULL),
(16, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(16, 3, 1, 1, '2025-05-23 15:03:38', NULL),
(17, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(17, 3, 1, 1, '2025-05-23 15:03:38', NULL),
(18, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(18, 3, 1, 1, '2025-05-23 15:03:38', NULL),
(19, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(19, 3, 1, 1, '2025-05-23 15:03:38', NULL),
(20, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(20, 3, 1, 1, '2025-05-23 15:03:38', NULL),
(21, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(21, 6, 1, 1, '2025-05-23 15:03:38', NULL),
(22, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(22, 6, 1, 1, '2025-05-23 15:03:38', NULL),
(23, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(23, 6, 1, 1, '2025-05-23 15:03:38', NULL),
(24, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(24, 6, 1, 1, '2025-05-23 15:03:38', NULL),
(25, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(25, 6, 1, 1, '2025-05-23 15:03:38', NULL),
(26, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(26, 6, 1, 1, '2025-05-23 15:03:38', NULL),
(27, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(27, 6, 1, 1, '2025-05-23 15:03:38', NULL),
(28, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(28, 6, 1, 1, '2025-05-23 15:03:38', NULL),
(29, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(29, 6, 1, 1, '2025-05-23 15:03:38', NULL),
(30, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(30, 6, 1, 1, '2025-05-23 15:03:38', NULL),
(31, 2, 1, 1, '2025-05-23 15:03:38', NULL),
(32, 2, 1, 1, '2025-05-23 15:03:38', NULL),
(33, 2, 1, 1, '2025-05-23 15:03:38', NULL),
(34, 2, 1, 1, '2025-05-23 15:03:38', NULL),
(35, 2, 1, 1, '2025-05-23 15:03:38', NULL),
(36, 2, 1, 1, '2025-05-23 15:03:38', NULL),
(37, 2, 1, 1, '2025-05-23 15:03:38', NULL),
(38, 2, 1, 1, '2025-05-23 15:03:38', NULL),
(39, 2, 1, 1, '2025-05-23 15:03:38', NULL),
(40, 2, 1, 1, '2025-05-23 15:03:38', NULL),
(41, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(41, 4, 1, 1, '2025-05-23 15:03:38', NULL),
(42, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(42, 4, 1, 1, '2025-05-23 15:03:38', NULL),
(43, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(43, 4, 1, 1, '2025-05-23 15:03:38', NULL),
(44, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(44, 4, 1, 1, '2025-05-23 15:03:38', NULL),
(45, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(45, 4, 1, 1, '2025-05-23 15:03:38', NULL),
(46, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(46, 4, 1, 1, '2025-05-23 15:03:38', NULL),
(47, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(47, 4, 1, 1, '2025-05-23 15:03:38', NULL),
(48, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(48, 4, 1, 1, '2025-05-23 15:03:38', NULL),
(49, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(49, 4, 1, 1, '2025-05-23 15:03:38', NULL),
(50, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(50, 4, 1, 1, '2025-05-23 15:03:38', NULL),
(51, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(51, 5, 1, 1, '2025-05-23 15:03:38', NULL),
(52, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(52, 5, 1, 1, '2025-05-23 15:03:38', NULL),
(53, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(53, 5, 1, 1, '2025-05-23 15:03:38', NULL),
(54, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(54, 5, 1, 1, '2025-05-23 15:03:38', NULL),
(55, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(55, 5, 1, 1, '2025-05-23 15:03:38', NULL),
(56, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(56, 5, 1, 1, '2025-05-23 15:03:38', NULL),
(57, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(57, 5, 1, 1, '2025-05-23 15:03:38', NULL),
(58, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(58, 5, 1, 1, '2025-05-23 15:03:38', NULL),
(59, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(59, 5, 1, 1, '2025-05-23 15:03:38', NULL),
(60, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(60, 5, 1, 1, '2025-05-23 15:03:38', NULL),
(61, 7, 1, 1, '2025-05-23 15:03:38', NULL),
(62, 7, 1, 1, '2025-05-23 15:03:38', NULL),
(63, 7, 1, 1, '2025-05-23 15:03:38', NULL),
(64, 7, 1, 1, '2025-05-23 15:03:38', NULL),
(65, 7, 1, 1, '2025-05-23 15:03:38', NULL),
(66, 7, 1, 1, '2025-05-23 15:03:38', NULL),
(67, 7, 1, 1, '2025-05-23 15:03:38', NULL),
(68, 7, 1, 1, '2025-05-23 15:03:38', NULL),
(69, 7, 1, 1, '2025-05-23 15:03:38', NULL),
(70, 7, 1, 1, '2025-05-23 15:03:38', NULL),
(71, 8, 1, 1, '2025-05-23 15:03:38', NULL),
(72, 8, 1, 1, '2025-05-23 15:03:38', NULL),
(73, 8, 1, 1, '2025-05-23 15:03:38', NULL),
(74, 8, 1, 1, '2025-05-23 15:03:38', NULL),
(75, 8, 1, 1, '2025-05-23 15:03:38', NULL),
(76, 8, 1, 1, '2025-05-23 15:03:38', NULL),
(77, 8, 1, 1, '2025-05-23 15:03:38', NULL),
(78, 8, 1, 1, '2025-05-23 15:03:38', NULL),
(79, 8, 1, 1, '2025-05-23 15:03:38', NULL),
(80, 8, 1, 1, '2025-05-23 15:03:38', NULL),
(81, 9, 1, 1, '2025-05-23 15:03:38', NULL),
(82, 9, 1, 1, '2025-05-23 15:03:38', NULL),
(83, 9, 1, 1, '2025-05-23 15:03:38', NULL),
(84, 9, 1, 1, '2025-05-23 15:03:38', NULL),
(85, 9, 1, 1, '2025-05-23 15:03:38', NULL),
(86, 9, 1, 1, '2025-05-23 15:03:38', NULL),
(87, 9, 1, 1, '2025-05-23 15:03:38', NULL),
(88, 9, 1, 1, '2025-05-23 15:03:38', NULL),
(89, 9, 1, 1, '2025-05-23 15:03:38', NULL),
(90, 9, 1, 1, '2025-05-23 15:03:38', NULL),
(91, 10, 1, 1, '2025-05-23 15:03:38', NULL),
(92, 10, 1, 1, '2025-05-23 15:03:38', NULL),
(93, 10, 1, 1, '2025-05-23 15:03:38', NULL),
(94, 10, 1, 1, '2025-05-23 15:03:38', NULL),
(95, 10, 1, 1, '2025-05-23 15:03:38', NULL),
(96, 10, 1, 1, '2025-05-23 15:03:38', NULL),
(97, 10, 1, 1, '2025-05-23 15:03:38', NULL),
(98, 10, 1, 1, '2025-05-23 15:03:38', NULL),
(99, 10, 1, 1, '2025-05-23 15:03:38', NULL),
(100, 10, 1, 1, '2025-05-23 15:03:38', NULL),
(101, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(101, 11, 1, 1, '2025-05-23 15:03:38', NULL),
(102, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(102, 11, 1, 1, '2025-05-23 15:03:38', NULL),
(103, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(103, 11, 1, 1, '2025-05-23 15:03:38', NULL),
(104, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(104, 11, 1, 1, '2025-05-23 15:03:38', NULL),
(105, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(105, 11, 1, 1, '2025-05-23 15:03:38', NULL),
(106, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(106, 11, 1, 1, '2025-05-23 15:03:38', NULL),
(107, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(107, 11, 1, 1, '2025-05-23 15:03:38', NULL),
(108, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(108, 11, 1, 1, '2025-05-23 15:03:38', NULL),
(109, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(109, 11, 1, 1, '2025-05-23 15:03:38', NULL),
(110, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(110, 11, 1, 1, '2025-05-23 15:03:38', NULL),
(111, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(111, 12, 1, 1, '2025-05-23 15:03:38', NULL),
(112, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(112, 12, 1, 1, '2025-05-23 15:03:38', NULL),
(113, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(113, 12, 1, 1, '2025-05-23 15:03:38', NULL),
(114, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(114, 12, 1, 1, '2025-05-23 15:03:38', NULL),
(115, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(115, 12, 1, 1, '2025-05-23 15:03:38', NULL),
(116, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(116, 12, 1, 1, '2025-05-23 15:03:38', NULL),
(117, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(117, 12, 1, 1, '2025-05-23 15:03:38', NULL),
(118, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(118, 12, 1, 1, '2025-05-23 15:03:38', NULL),
(119, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(119, 12, 1, 1, '2025-05-23 15:03:38', NULL),
(120, 10, 0, 1, '2025-05-23 15:03:38', NULL),
(120, 12, 1, 1, '2025-05-23 15:03:38', NULL),
(121, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(121, 13, 1, 1, '2025-05-23 15:03:38', NULL),
(122, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(122, 13, 1, 1, '2025-05-23 15:03:38', NULL),
(123, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(123, 13, 1, 1, '2025-05-23 15:03:38', NULL),
(124, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(124, 13, 1, 1, '2025-05-23 15:03:38', NULL),
(125, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(125, 13, 1, 1, '2025-05-23 15:03:38', NULL),
(126, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(126, 13, 1, 1, '2025-05-23 15:03:38', NULL),
(127, 7, 0, 1, '2025-05-23 15:03:38', NULL),
(127, 13, 1, 1, '2025-05-23 15:03:38', NULL),
(128, 1, 0, 1, '2025-05-23 15:03:38', NULL),
(128, 13, 1, 1, '2025-05-23 15:03:38', NULL),
(129, 2, 0, 1, '2025-05-23 15:03:38', NULL),
(129, 13, 1, 1, '2025-05-23 15:03:38', NULL),
(130, 13, 1, 1, '2025-05-23 15:03:38', NULL),
(131, 14, 1, 1, '2025-05-23 15:03:38', NULL),
(132, 14, 1, 1, '2025-05-23 15:03:38', NULL),
(133, 14, 1, 1, '2025-05-23 15:03:38', NULL),
(134, 14, 1, 1, '2025-05-23 15:03:38', NULL),
(135, 14, 1, 1, '2025-05-23 15:03:38', NULL),
(136, 14, 1, 1, '2025-05-23 15:03:38', NULL),
(137, 14, 1, 1, '2025-05-23 15:03:38', NULL),
(138, 14, 1, 1, '2025-05-23 15:03:38', NULL),
(139, 14, 1, 1, '2025-05-23 15:03:38', NULL),
(140, 14, 1, 1, '2025-05-23 15:03:38', NULL),
(141, 15, 1, 1, '2025-05-23 15:03:38', NULL),
(142, 15, 1, 1, '2025-05-23 15:03:38', NULL),
(143, 15, 1, 1, '2025-05-23 15:03:38', NULL),
(144, 15, 1, 1, '2025-05-23 15:03:38', NULL),
(145, 15, 1, 1, '2025-05-23 15:03:38', NULL),
(146, 15, 1, 1, '2025-05-23 15:03:38', NULL),
(147, 15, 1, 1, '2025-05-23 15:03:38', NULL),
(148, 15, 1, 1, '2025-05-23 15:03:38', NULL),
(149, 15, 1, 1, '2025-05-23 15:03:38', NULL),
(150, 15, 1, 1, '2025-05-23 15:03:38', NULL);

--
-- Triggers `product_categories`
--
DROP TRIGGER IF EXISTS `before_product_category_insert`;
DELIMITER $$
CREATE TRIGGER `before_product_category_insert` BEFORE INSERT ON `product_categories` FOR EACH ROW BEGIN
    IF NEW.is_primary_category = 1 THEN
        UPDATE product_categories 
        SET is_primary_category = 0 
        WHERE product_id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

DROP TABLE IF EXISTS `product_images`;
CREATE TABLE IF NOT EXISTS `product_images` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `alt_text` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `image_type` enum('thumbnail','gallery','360-view') COLLATE utf8mb4_general_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `uploaded_by` bigint UNSIGNED DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_images_product_id_index` (`product_id`),
  KEY `product_images_uploaded_by_index` (`uploaded_by`)
) ENGINE=InnoDB AUTO_INCREMENT=366 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `alt_text`, `image_type`, `is_primary`, `uploaded_by`, `uploaded_at`, `deleted_at`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1578932750294-f5075e85f44a?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #1', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(2, 1, 'https://images.unsplash.com/photo-1557760257-b02421ae77fe?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #1', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(3, 2, 'https://images.unsplash.com/photo-1601067055250-584e365eab29?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #2', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(4, 2, 'https://images.unsplash.com/photo-1509103125097-696e3881f56e?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #2', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(5, 3, 'https://images.unsplash.com/photo-1525127752301-99b0b6379811?q=80&w=1350&auto=format&fit=crop', 'Primary image of product #3', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(6, 3, 'https://images.unsplash.com/photo-1490114538077-0a7f8cb49891?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #3', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(7, 3, 'https://images.unsplash.com/photo-1490114538077-0a7f8cb49891?q=80&w=1470&auto=format&fit=crop', 'Additional view of product #3', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(8, 4, 'https://images.unsplash.com/photo-1490114538077-0a7f8cb49891?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #4', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(9, 4, 'https://images.unsplash.com/photo-1474594605419-611f51bc7e5a?q=80&w=1300&auto=format&fit=crop', 'Secondary image of product #4', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(10, 5, 'https://images.unsplash.com/photo-1593030103066-0093718efeb9?q=80&w=1480&auto=format&fit=crop', 'Primary image of product #5', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(11, 5, 'https://images.unsplash.com/photo-1495121605193-b116b5b9c5fe?q=80&w=1476&auto=format&fit=crop', 'Secondary image of product #5', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(12, 6, 'https://images.unsplash.com/photo-1552831388-6a0b3575b32a?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #6', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(13, 6, 'https://images.unsplash.com/photo-1571908598047-2b9c19c3b75b?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #6', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(14, 6, 'https://images.unsplash.com/photo-1571908598047-2b9c19c3b75b?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #6', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(15, 7, 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #7', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(16, 7, 'https://images.unsplash.com/photo-1576566588028-4147f3842f27?q=80&w=1364&auto=format&fit=crop', 'Secondary image of product #7', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(17, 8, 'https://images.unsplash.com/photo-1475180098004-ca77a66827be?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #8', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(18, 8, 'https://images.unsplash.com/photo-1552831388-6a0b3575b32a?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #8', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(19, 9, 'https://images.unsplash.com/photo-1505632958218-4f23394784a6?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #9', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(20, 9, 'https://images.unsplash.com/photo-1603252109303-2751441dd157?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #9', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(21, 9, 'https://images.unsplash.com/photo-1603252109303-2751441dd157?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #9', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(22, 10, 'https://images.unsplash.com/photo-1602810316693-3667c854239a?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #10', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(23, 10, 'https://images.unsplash.com/photo-1565084888279-aca607ee8ca8?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #10', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(24, 10, 'https://images.unsplash.com/photo-1565084888279-aca607ee8ca8?q=80&w=1470&auto=format&fit=crop', '360-degree view of product #10', '360-view', 0, 1, '2025-05-23 15:03:38', NULL),
(25, 11, 'https://images.unsplash.com/photo-1593032534936-3607d5f04c9c?q=80&w=1480&auto=format&fit=crop', 'Primary image of product #11', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(26, 11, 'https://images.unsplash.com/photo-1605518216938-7c31b7b14ad0?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #11', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(27, 12, 'https://images.unsplash.com/photo-1598808503429-896017973877?q=80&w=1412&auto=format&fit=crop', 'Primary image of product #12', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(28, 12, 'https://images.unsplash.com/photo-1598808503475-655171cf6196?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #12', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(29, 12, 'https://images.unsplash.com/photo-1598808503475-655171cf6196?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #12', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(30, 13, 'https://images.unsplash.com/photo-1617127365659-c47fa864d8bc?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #13', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(31, 13, 'https://images.unsplash.com/photo-1543132220-4bf3de6e10ae?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #13', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(32, 14, 'https://images.unsplash.com/photo-1578932750294-f5075e85f44a?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #14', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(33, 14, 'https://images.unsplash.com/photo-1594938298603-c8148c4dae35?q=80&w=1480&auto=format&fit=crop', 'Secondary image of product #14', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(34, 15, 'https://images.unsplash.com/photo-1623880840102-7df0a9f3545b?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #15', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(35, 15, 'https://images.unsplash.com/photo-1548883354-94bcfe321cbb?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #15', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(36, 15, 'https://images.unsplash.com/photo-1548883354-94bcfe321cbb?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #15', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(37, 16, 'https://images.unsplash.com/photo-1593032465175-481ac7f401f0?q=80&w=1480&auto=format&fit=crop', 'Primary image of product #16', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(38, 16, 'https://images.unsplash.com/photo-1589363460779-68653ebfa2b8?q=80&w=1374&auto=format&fit=crop', 'Secondary image of product #16', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(39, 17, 'https://images.unsplash.com/photo-1507679799987-c73779587ccf?q=80&w=1471&auto=format&fit=crop', 'Primary image of product #17', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(40, 17, 'https://images.unsplash.com/photo-1586183189334-33ca6ea6c271?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #17', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(41, 18, 'https://images.unsplash.com/photo-1593030761757-71fae45fa0e7?q=80&w=1480&auto=format&fit=crop', 'Primary image of product #18', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(42, 18, 'https://images.unsplash.com/photo-1553588087-3af5987de5b5?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #18', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(43, 18, 'https://images.unsplash.com/photo-1553588087-3af5987de5b5?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #18', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(44, 19, 'https://images.unsplash.com/photo-1593032534936-3607d5f04c9c?q=80&w=1480&auto=format&fit=crop', 'Primary image of product #19', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(45, 19, 'https://images.unsplash.com/photo-1605518216938-7c31b7b14ad0?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #19', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(46, 20, 'https://images.unsplash.com/photo-1598808503429-896017973877?q=80&w=1412&auto=format&fit=crop', 'Primary image of product #20', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(47, 20, 'https://images.unsplash.com/photo-1598808503475-655171cf6196?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #20', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(48, 20, 'https://images.unsplash.com/photo-1598808503475-655171cf6196?q=80&w=1287&auto=format&fit=crop', '360-degree view of product #20', '360-view', 0, 1, '2025-05-23 15:03:38', NULL),
(49, 21, 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?q=80&w=1280&auto=format&fit=crop', 'Primary image of product #21', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(50, 21, 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?q=80&w=1480&auto=format&fit=crop', 'Secondary image of product #21', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(51, 21, 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?q=80&w=1480&auto=format&fit=crop', 'Additional view of product #21', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(52, 22, 'https://images.unsplash.com/photo-1622434641406-a158123450f9?q=80&w=1404&auto=format&fit=crop', 'Primary image of product #22', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(53, 22, 'https://images.unsplash.com/photo-1514327605112-b887c0e61c0a?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #22', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(54, 23, 'https://images.unsplash.com/photo-1596455607563-ad6511806173?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #23', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(55, 23, 'https://images.unsplash.com/photo-1604024466177-38c4121ae81c?q=80&w=1335&auto=format&fit=crop', 'Secondary image of product #23', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(56, 24, 'https://images.unsplash.com/photo-1526766720902-eff37a47ffd4?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #24', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(57, 24, 'https://images.unsplash.com/photo-1589782182703-2aaa69037b5b?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #24', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(58, 24, 'https://images.unsplash.com/photo-1589782182703-2aaa69037b5b?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #24', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(59, 25, 'https://images.unsplash.com/photo-1549971352-c7fa7d829fcd?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #25', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(60, 25, 'https://images.unsplash.com/photo-1577803645773-f96470509666?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #25', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(61, 26, 'https://images.unsplash.com/photo-1511499767150-a48a237f0083?q=80&w=1280&auto=format&fit=crop', 'Primary image of product #26', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(62, 26, 'https://images.unsplash.com/photo-1523170335258-f5ed11844a49?q=80&w=1480&auto=format&fit=crop', 'Secondary image of product #26', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(63, 27, 'https://images.unsplash.com/photo-1622434641406-a158123450f9?q=80&w=1404&auto=format&fit=crop', 'Primary image of product #27', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(64, 27, 'https://images.unsplash.com/photo-1514327605112-b887c0e61c0a?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #27', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(65, 27, 'https://images.unsplash.com/photo-1514327605112-b887c0e61c0a?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #27', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(66, 28, 'https://images.unsplash.com/photo-1596455607563-ad6511806173?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #28', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(67, 28, 'https://images.unsplash.com/photo-1604024466177-38c4121ae81c?q=80&w=1335&auto=format&fit=crop', 'Secondary image of product #28', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(68, 29, 'https://images.unsplash.com/photo-1526766720902-eff37a47ffd4?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #29', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(69, 29, 'https://images.unsplash.com/photo-1589782182703-2aaa69037b5b?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #29', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(70, 30, 'https://images.unsplash.com/photo-1549971352-c7fa7d829fcd?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #30', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(71, 30, 'https://images.unsplash.com/photo-1577803645773-f96470509666?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #30', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(72, 30, 'https://images.unsplash.com/photo-1577803645773-f96470509666?q=80&w=1470&auto=format&fit=crop', 'Additional view of product #30', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(73, 30, 'https://images.unsplash.com/photo-1577803645773-f96470509666?q=80&w=1470&auto=format&fit=crop', '360-degree view of product #30', '360-view', 0, 1, '2025-05-23 15:03:38', NULL),
(74, 31, 'https://images.unsplash.com/photo-1591369822096-ffd140ec948f?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #31', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(75, 31, 'https://images.unsplash.com/photo-1623626747969-6c03a22e5a95?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #31', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(76, 32, 'https://images.unsplash.com/photo-1434389677669-e08b4cac3105?q=80&w=1349&auto=format&fit=crop', 'Primary image of product #32', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(77, 32, 'https://images.unsplash.com/photo-1584370848010-d7fe6bc767ec?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #32', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(78, 33, 'https://images.unsplash.com/photo-1483985988355-763728e1935b?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #33', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(79, 33, 'https://images.unsplash.com/photo-1562157873-818bc0726f68?q=80&w=1527&auto=format&fit=crop', 'Secondary image of product #33', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(80, 33, 'https://images.unsplash.com/photo-1562157873-818bc0726f68?q=80&w=1527&auto=format&fit=crop', 'Additional view of product #33', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(81, 34, 'https://images.unsplash.com/photo-1551799517-eb8f03cb5e6a?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #34', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(82, 34, 'https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #34', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(83, 35, 'https://images.unsplash.com/photo-1552718752-f95a33ab1a3d?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #35', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(84, 35, 'https://images.unsplash.com/photo-1552718752-f95a33ab1a3d?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #35', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(85, 36, 'https://images.unsplash.com/photo-1564257631407-4deb1f99d992?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #36', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(86, 36, 'https://images.unsplash.com/photo-1564257631407-4deb1f99d992?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #36', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(87, 36, 'https://images.unsplash.com/photo-1564257631407-4deb1f99d992?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #36', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(88, 37, 'https://images.unsplash.com/photo-1616400619175-5beda3a17896?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #37', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(89, 37, 'https://images.unsplash.com/photo-1616400619175-5beda3a17896?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #37', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(90, 38, 'https://images.unsplash.com/photo-1632149877166-f75d49000351?q=80&w=1364&auto=format&fit=crop', 'Primary image of product #38', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(91, 38, 'https://images.unsplash.com/photo-1632149877166-f75d49000351?q=80&w=1364&auto=format&fit=crop', 'Secondary image of product #38', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(92, 39, 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?q=80&w=1364&auto=format&fit=crop', 'Primary image of product #39', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(93, 39, 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?q=80&w=1364&auto=format&fit=crop', 'Secondary image of product #39', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(94, 39, 'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?q=80&w=1364&auto=format&fit=crop', 'Additional view of product #39', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(95, 40, 'https://images.unsplash.com/photo-1489987707025-afc232f7ea0f?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #40', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(96, 40, 'https://images.unsplash.com/photo-1623626766581-9d8d63b5e161?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #40', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(97, 40, 'https://images.unsplash.com/photo-1623626766581-9d8d63b5e161?q=80&w=1287&auto=format&fit=crop', '360-degree view of product #40', '360-view', 0, 1, '2025-05-23 15:03:38', NULL),
(98, 41, 'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?q=80&w=1338&auto=format&fit=crop', 'Primary image of product #41', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(99, 41, 'https://images.unsplash.com/photo-1623656607289-2bc76aa5d51b?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #41', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(100, 42, 'https://images.unsplash.com/photo-1612336307429-8a898d10e223?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #42', 'thumbnail', 1, 1, '2025-05-23 15:03:38', NULL),
(101, 42, 'https://images.unsplash.com/photo-1622122201714-77da0ca8e5d2?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #42', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(102, 42, 'https://images.unsplash.com/photo-1622122201714-77da0ca8e5d2?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #42', 'gallery', 0, 1, '2025-05-23 15:03:38', NULL),
(103, 43, 'https://images.unsplash.com/photo-1502716119720-b23a93e5fe1b?q=80&w=1374&auto=format&fit=crop', 'Primary image of product #43', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(104, 43, 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?q=80&w=1330&auto=format&fit=crop', 'Secondary image of product #43', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(105, 44, 'https://images.unsplash.com/photo-1539008835657-9e8e9680c956?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #44', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(106, 44, 'https://images.unsplash.com/photo-1613539246099-8e75c25d0d1e?q=80&w=1288&auto=format&fit=crop', 'Secondary image of product #44', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(107, 45, 'https://images.unsplash.com/photo-1618721405269-e8cc2c6f66e5?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #45', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(108, 45, 'https://images.unsplash.com/photo-1509087859087-a384654eca4d?q=80&w=1288&auto=format&fit=crop', 'Secondary image of product #45', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(109, 45, 'https://images.unsplash.com/photo-1509087859087-a384654eca4d?q=80&w=1288&auto=format&fit=crop', 'Additional view of product #45', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(110, 46, 'https://images.unsplash.com/photo-1614786269829-d24616faf56d?q=80&w=1335&auto=format&fit=crop', 'Primary image of product #46', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(111, 46, 'https://images.unsplash.com/photo-1492707892479-7bc8d5a4ee93?q=80&w=1530&auto=format&fit=crop', 'Secondary image of product #46', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(112, 47, 'https://images.unsplash.com/photo-1621184455862-c163dfb30e0f?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #47', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(113, 47, 'https://images.unsplash.com/photo-1612722432474-b971cdcea546?q=80&w=1627&auto=format&fit=crop', 'Secondary image of product #47', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(114, 48, 'https://images.unsplash.com/photo-1631234764568-996fab378d01?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #48', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(115, 48, 'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?q=80&w=1335&auto=format&fit=crop', 'Secondary image of product #48', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(116, 48, 'https://images.unsplash.com/photo-1525507119028-ed4c629a60a3?q=80&w=1335&auto=format&fit=crop', 'Additional view of product #48', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(117, 49, 'https://images.unsplash.com/photo-1566174053879-31528523f8ae?q=80&w=1330&auto=format&fit=crop', 'Primary image of product #49', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(118, 49, 'https://images.unsplash.com/photo-1622470953794-aa9c70b0fb9d?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #49', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(119, 50, 'https://images.unsplash.com/photo-1496747611176-843222e1e57c?q=80&w=1373&auto=format&fit=crop', 'Primary image of product #50', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(120, 50, 'https://images.unsplash.com/photo-1564257631407-4deb1f99d992?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #50', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(121, 50, 'https://images.unsplash.com/photo-1564257631407-4deb1f99d992?q=80&w=1287&auto=format&fit=crop', '360-degree view of product #50', '360-view', 0, 1, '2025-05-23 15:03:39', NULL),
(122, 51, 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?q=80&w=1480&auto=format&fit=crop', 'Primary image of product #51', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(123, 51, 'https://images.unsplash.com/photo-1596690097552-77e91f47d4c9?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #51', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(124, 51, 'https://images.unsplash.com/photo-1596690097552-77e91f47d4c9?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #51', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(125, 52, 'https://images.unsplash.com/photo-1534215754734-18e55d13e346?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #52', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(126, 52, 'https://images.unsplash.com/photo-1587467512961-120760940315?q=80&w=1335&auto=format&fit=crop', 'Secondary image of product #52', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(127, 53, 'https://images.unsplash.com/photo-1599643477877-530eb83abc8e?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #53', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(128, 53, 'https://images.unsplash.com/photo-1598560917807-1bae44bd2be8?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #53', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(129, 54, 'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #54', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(130, 54, 'https://images.unsplash.com/photo-1565857936036-28f3160f8649?q=80&w=1364&auto=format&fit=crop', 'Secondary image of product #54', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(131, 54, 'https://images.unsplash.com/photo-1565857936036-28f3160f8649?q=80&w=1364&auto=format&fit=crop', 'Additional view of product #54', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(132, 55, 'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?q=80&w=1471&auto=format&fit=crop', 'Primary image of product #55', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(133, 55, 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #55', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(134, 56, 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?q=80&w=1480&auto=format&fit=crop', 'Primary image of product #56', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(135, 56, 'https://images.unsplash.com/photo-1596690097552-77e91f47d4c9?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #56', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(136, 57, 'https://images.unsplash.com/photo-1534215754734-18e55d13e346?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #57', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(137, 57, 'https://images.unsplash.com/photo-1587467512961-120760940315?q=80&w=1335&auto=format&fit=crop', 'Secondary image of product #57', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(138, 57, 'https://images.unsplash.com/photo-1587467512961-120760940315?q=80&w=1335&auto=format&fit=crop', 'Additional view of product #57', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(139, 58, 'https://images.unsplash.com/photo-1599643477877-530eb83abc8e?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #58', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(140, 58, 'https://images.unsplash.com/photo-1598560917807-1bae44bd2be8?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #58', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(141, 59, 'https://images.unsplash.com/photo-1617038260897-41a1f14a8ca0?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #59', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(142, 59, 'https://images.unsplash.com/photo-1565857936036-28f3160f8649?q=80&w=1364&auto=format&fit=crop', 'Secondary image of product #59', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(143, 60, 'https://images.unsplash.com/photo-1566150905458-1bf1fc113f0d?q=80&w=1471&auto=format&fit=crop', 'Primary image of product #60', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(144, 60, 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #60', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(145, 60, 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #60', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(146, 60, 'https://images.unsplash.com/photo-1584917865442-de89df76afd3?q=80&w=1287&auto=format&fit=crop', '360-degree view of product #60', '360-view', 0, 1, '2025-05-23 15:03:39', NULL),
(147, 61, 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #61', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(148, 61, 'https://images.unsplash.com/photo-1519278409-1f56fdda7fe5?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #61', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(149, 62, 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?q=80&w=1372&auto=format&fit=crop', 'Primary image of product #62', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(150, 62, 'https://images.unsplash.com/photo-1632753045505-831a81d3f2a8?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #62', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(151, 63, 'https://images.unsplash.com/photo-1545048702-79362596cdc9?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #63', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(152, 63, 'https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #63', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(153, 63, 'https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #63', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(154, 64, 'https://images.unsplash.com/photo-1476234251651-f353703a034d?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #64', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(155, 64, 'https://images.unsplash.com/photo-1622290319146-7b63df48a635?q=80&w=1372&auto=format&fit=crop', 'Secondary image of product #64', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(156, 65, 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #65', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(157, 65, 'https://images.unsplash.com/photo-1518831959646-28f4145e1d44?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #65', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(158, 66, 'https://images.unsplash.com/photo-1512436991641-6745cdb1723f?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #66', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(159, 66, 'https://images.unsplash.com/photo-1519278409-1f56fdda7fe5?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #66', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(160, 66, 'https://images.unsplash.com/photo-1519278409-1f56fdda7fe5?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #66', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(161, 67, 'https://images.unsplash.com/photo-1622290291468-a28f7a7dc6a8?q=80&w=1372&auto=format&fit=crop', 'Primary image of product #67', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(162, 67, 'https://images.unsplash.com/photo-1632753045505-831a81d3f2a8?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #67', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(163, 68, 'https://images.unsplash.com/photo-1545048702-79362596cdc9?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #68', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(164, 68, 'https://images.unsplash.com/photo-1471286174890-9c112ffca5b4?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #68', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(165, 69, 'https://images.unsplash.com/photo-1476234251651-f353703a034d?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #69', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(166, 69, 'https://images.unsplash.com/photo-1622290319146-7b63df48a635?q=80&w=1372&auto=format&fit=crop', 'Secondary image of product #69', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(167, 69, 'https://images.unsplash.com/photo-1622290319146-7b63df48a635?q=80&w=1372&auto=format&fit=crop', 'Additional view of product #69', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(168, 70, 'https://images.unsplash.com/photo-1519238263530-99bdd11df2ea?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #70', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(169, 70, 'https://images.unsplash.com/photo-1518831959646-28f4145e1d44?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #70', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(170, 70, 'https://images.unsplash.com/photo-1518831959646-28f4145e1d44?q=80&w=1287&auto=format&fit=crop', '360-degree view of product #70', '360-view', 0, 1, '2025-05-23 15:03:39', NULL),
(171, 71, 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #71', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(172, 71, 'https://images.unsplash.com/photo-1595594000631-11c553d2e2cf?q=80&w=1350&auto=format&fit=crop', 'Secondary image of product #71', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(173, 72, 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?q=80&w=1348&auto=format&fit=crop', 'Primary image of product #72', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(174, 72, 'https://images.unsplash.com/photo-1611874408634-9650257f3ef0?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #72', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(175, 72, 'https://images.unsplash.com/photo-1611874408634-9650257f3ef0?q=80&w=1470&auto=format&fit=crop', 'Additional view of product #72', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(176, 73, 'https://images.unsplash.com/photo-1602734846297-9299fc2d4703?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #73', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(177, 73, 'https://images.unsplash.com/photo-1599623560574-39d485900c95?q=80&w=1374&auto=format&fit=crop', 'Secondary image of product #73', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(178, 74, 'https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?q=80&w=1374&auto=format&fit=crop', 'Primary image of product #74', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(179, 74, 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?q=80&w=1450&auto=format&fit=crop', 'Secondary image of product #74', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(180, 75, 'https://images.unsplash.com/photo-1570039489884-3ffa5a5156ab?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #75', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(181, 75, 'https://images.unsplash.com/photo-1558060370-d6a73b7d6f32?q=80&w=1364&auto=format&fit=crop', 'Secondary image of product #75', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(182, 75, 'https://images.unsplash.com/photo-1558060370-d6a73b7d6f32?q=80&w=1364&auto=format&fit=crop', 'Additional view of product #75', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(183, 76, 'https://images.unsplash.com/photo-1587654780291-39c9404d746b?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #76', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(184, 76, 'https://images.unsplash.com/photo-1595594000631-11c553d2e2cf?q=80&w=1350&auto=format&fit=crop', 'Secondary image of product #76', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(185, 77, 'https://images.unsplash.com/photo-1594736797933-d0501ba2fe65?q=80&w=1348&auto=format&fit=crop', 'Primary image of product #77', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(186, 77, 'https://images.unsplash.com/photo-1611874408634-9650257f3ef0?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #77', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(187, 78, 'https://images.unsplash.com/photo-1602734846297-9299fc2d4703?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #78', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(188, 78, 'https://images.unsplash.com/photo-1599623560574-39d485900c95?q=80&w=1374&auto=format&fit=crop', 'Secondary image of product #78', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(189, 78, 'https://images.unsplash.com/photo-1599623560574-39d485900c95?q=80&w=1374&auto=format&fit=crop', 'Additional view of product #78', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(190, 79, 'https://images.unsplash.com/photo-1513542789411-b6a5d4f31634?q=80&w=1374&auto=format&fit=crop', 'Primary image of product #79', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(191, 79, 'https://images.unsplash.com/photo-1544197150-b99a580bb7a8?q=80&w=1450&auto=format&fit=crop', 'Secondary image of product #79', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(192, 80, 'https://images.unsplash.com/photo-1570039489884-3ffa5a5156ab?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #80', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(193, 80, 'https://images.unsplash.com/photo-1558060370-d6a73b7d6f32?q=80&w=1364&auto=format&fit=crop', 'Secondary image of product #80', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(194, 80, 'https://images.unsplash.com/photo-1558060370-d6a73b7d6f32?q=80&w=1364&auto=format&fit=crop', '360-degree view of product #80', '360-view', 0, 1, '2025-05-23 15:03:39', NULL),
(195, 81, 'https://images.unsplash.com/photo-1616046229478-9901c5536a45?q=80&w=1480&auto=format&fit=crop', 'Primary image of product #81', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(196, 81, 'https://images.unsplash.com/photo-1594026112284-02bb6f3352fe?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #81', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(197, 81, 'https://images.unsplash.com/photo-1594026112284-02bb6f3352fe?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #81', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(198, 82, 'https://images.unsplash.com/photo-1575414003591-ece8d0416c7a?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #82', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(199, 82, 'https://images.unsplash.com/photo-1600210492493-0946911123ea?q=80&w=1374&auto=format&fit=crop', 'Secondary image of product #82', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(200, 83, 'https://images.unsplash.com/photo-1540574163026-643ea20ade25?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #83', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(201, 83, 'https://images.unsplash.com/photo-1601045569976-173aa7db26e3?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #83', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(202, 84, 'https://images.unsplash.com/photo-1551907234-fb773fb01db9?q=80&w=1288&auto=format&fit=crop', 'Primary image of product #84', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(203, 84, 'https://images.unsplash.com/photo-1587459028907-ccd6ff453a0f?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #84', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(204, 84, 'https://images.unsplash.com/photo-1587459028907-ccd6ff453a0f?q=80&w=1470&auto=format&fit=crop', 'Additional view of product #84', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(205, 85, 'https://images.unsplash.com/photo-1585412727339-54a5d9e91c2c?q=80&w=1480&auto=format&fit=crop', 'Primary image of product #85', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(206, 85, 'https://images.unsplash.com/photo-1629140727571-9b5c6f6267b4?q=80&w=1527&auto=format&fit=crop', 'Secondary image of product #85', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(207, 86, 'https://images.unsplash.com/photo-1616046229478-9901c5536a45?q=80&w=1480&auto=format&fit=crop', 'Primary image of product #86', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(208, 86, 'https://images.unsplash.com/photo-1594026112284-02bb6f3352fe?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #86', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(209, 87, 'https://images.unsplash.com/photo-1575414003591-ece8d0416c7a?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #87', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(210, 87, 'https://images.unsplash.com/photo-1600210492493-0946911123ea?q=80&w=1374&auto=format&fit=crop', 'Secondary image of product #87', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(211, 87, 'https://images.unsplash.com/photo-1600210492493-0946911123ea?q=80&w=1374&auto=format&fit=crop', 'Additional view of product #87', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(212, 88, 'https://images.unsplash.com/photo-1540574163026-643ea20ade25?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #88', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(213, 88, 'https://images.unsplash.com/photo-1601045569976-173aa7db26e3?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #88', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(214, 89, 'https://images.unsplash.com/photo-1551907234-fb773fb01db9?q=80&w=1288&auto=format&fit=crop', 'Primary image of product #89', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(215, 89, 'https://images.unsplash.com/photo-1587459028907-ccd6ff453a0f?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #89', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(216, 90, 'https://images.unsplash.com/photo-1585412727339-54a5d9e91c2c?q=80&w=1480&auto=format&fit=crop', 'Primary image of product #90', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(217, 90, 'https://images.unsplash.com/photo-1629140727571-9b5c6f6267b4?q=80&w=1527&auto=format&fit=crop', 'Secondary image of product #90', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(218, 90, 'https://images.unsplash.com/photo-1629140727571-9b5c6f6267b4?q=80&w=1527&auto=format&fit=crop', 'Additional view of product #90', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(219, 90, 'https://images.unsplash.com/photo-1629140727571-9b5c6f6267b4?q=80&w=1527&auto=format&fit=crop', '360-degree view of product #90', '360-view', 0, 1, '2025-05-23 15:03:39', NULL),
(220, 91, 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #91', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(221, 91, 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #91', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(222, 92, 'https://images.unsplash.com/photo-1593305841991-05c297ba4575?q=80&w=1474&auto=format&fit=crop', 'Primary image of product #92', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(223, 92, 'https://images.unsplash.com/photo-1577979749830-f1d742b96791?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #92', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(224, 93, 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #93', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(225, 93, 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?q=80&w=1335&auto=format&fit=crop', 'Secondary image of product #93', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(226, 93, 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?q=80&w=1335&auto=format&fit=crop', 'Additional view of product #93', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(227, 94, 'https://images.unsplash.com/photo-1585351923007-bf6a311e1d5a?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #94', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(228, 94, 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=1336&auto=format&fit=crop', 'Secondary image of product #94', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(229, 95, 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?q=80&w=1301&auto=format&fit=crop', 'Primary image of product #95', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(230, 95, 'https://images.unsplash.com/photo-1606904825846-f7356321a929?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #95', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(231, 96, 'https://images.unsplash.com/photo-1519389950473-47ba0277781c?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #96', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(232, 96, 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #96', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(233, 96, 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?q=80&w=1470&auto=format&fit=crop', 'Additional view of product #96', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(234, 97, 'https://images.unsplash.com/photo-1593305841991-05c297ba4575?q=80&w=1474&auto=format&fit=crop', 'Primary image of product #97', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(235, 97, 'https://images.unsplash.com/photo-1577979749830-f1d742b96791?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #97', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(236, 98, 'https://images.unsplash.com/photo-1571175443880-49e1d25b2bc5?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #98', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(237, 98, 'https://images.unsplash.com/photo-1584568694244-14fbdf83bd30?q=80&w=1335&auto=format&fit=crop', 'Secondary image of product #98', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(238, 99, 'https://images.unsplash.com/photo-1585351923007-bf6a311e1d5a?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #99', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(239, 99, 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=1336&auto=format&fit=crop', 'Secondary image of product #99', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(240, 99, 'https://images.unsplash.com/photo-1574629810360-7efbbe195018?q=80&w=1336&auto=format&fit=crop', 'Additional view of product #99', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(241, 100, 'https://images.unsplash.com/photo-1550009158-9ebf69173e03?q=80&w=1301&auto=format&fit=crop', 'Primary image of product #100', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(242, 100, 'https://images.unsplash.com/photo-1606904825846-f7356321a929?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #100', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(243, 100, 'https://images.unsplash.com/photo-1606904825846-f7356321a929?q=80&w=1287&auto=format&fit=crop', '360-degree view of product #100', '360-view', 0, 1, '2025-05-23 15:03:39', NULL),
(244, 101, 'https://images.unsplash.com/photo-1580910051074-3eb694886505?q=80&w=1335&auto=format&fit=crop', 'Primary image of product #101', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(245, 101, 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #101', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(246, 102, 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?q=80&w=1329&auto=format&fit=crop', 'Primary image of product #102', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(247, 102, 'https://images.unsplash.com/photo-1612442443949-af0cd4a05e2b?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #102', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(248, 102, 'https://images.unsplash.com/photo-1612442443949-af0cd4a05e2b?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #102', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(249, 103, 'https://images.unsplash.com/photo-1616410011236-7a42121dd981?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #103', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(250, 103, 'https://images.unsplash.com/photo-1598327105854-c8674faddf79?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #103', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(251, 104, 'https://images.unsplash.com/photo-1617686693347-1705b2f10b01?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #104', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(252, 104, 'https://images.unsplash.com/photo-1617686693997-9a28459c2635?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #104', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(253, 105, 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #105', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(254, 105, 'https://images.unsplash.com/photo-1563203369-26f2e4a5ccf7?q=80&w=1529&auto=format&fit=crop', 'Secondary image of product #105', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(255, 105, 'https://images.unsplash.com/photo-1563203369-26f2e4a5ccf7?q=80&w=1529&auto=format&fit=crop', 'Additional view of product #105', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(256, 106, 'https://images.unsplash.com/photo-1580910051074-3eb694886505?q=80&w=1335&auto=format&fit=crop', 'Primary image of product #106', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(257, 106, 'https://images.unsplash.com/photo-1605236453806-6ff36851218e?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #106', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(258, 107, 'https://images.unsplash.com/photo-1592899677977-9c10ca588bbd?q=80&w=1329&auto=format&fit=crop', 'Primary image of product #107', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(259, 107, 'https://images.unsplash.com/photo-1612442443949-af0cd4a05e2b?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #107', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(260, 108, 'https://images.unsplash.com/photo-1616410011236-7a42121dd981?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #108', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(261, 108, 'https://images.unsplash.com/photo-1598327105854-c8674faddf79?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #108', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(262, 108, 'https://images.unsplash.com/photo-1598327105854-c8674faddf79?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #108', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(263, 109, 'https://images.unsplash.com/photo-1617686693347-1705b2f10b01?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #109', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(264, 109, 'https://images.unsplash.com/photo-1617686693997-9a28459c2635?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #109', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(265, 110, 'https://images.unsplash.com/photo-1510557880182-3d4d3cba35a5?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #110', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(266, 110, 'https://images.unsplash.com/photo-1563203369-26f2e4a5ccf7?q=80&w=1529&auto=format&fit=crop', 'Secondary image of product #110', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(267, 110, 'https://images.unsplash.com/photo-1563203369-26f2e4a5ccf7?q=80&w=1529&auto=format&fit=crop', '360-degree view of product #110', '360-view', 0, 1, '2025-05-23 15:03:39', NULL),
(268, 111, 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=1472&auto=format&fit=crop', 'Primary image of product #111', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(269, 111, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?q=80&w=1471&auto=format&fit=crop', 'Secondary image of product #111', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL);
INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `alt_text`, `image_type`, `is_primary`, `uploaded_by`, `uploaded_at`, `deleted_at`) VALUES
(270, 111, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?q=80&w=1471&auto=format&fit=crop', 'Additional view of product #111', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(271, 112, 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #112', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(272, 112, 'https://images.unsplash.com/photo-1542393545-10f5cde2c810?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #112', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(273, 113, 'https://images.unsplash.com/photo-1600861194942-f883de0dfe96?q=80&w=1649&auto=format&fit=crop', 'Primary image of product #113', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(274, 113, 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?q=80&w=1305&auto=format&fit=crop', 'Secondary image of product #113', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(275, 114, 'https://images.unsplash.com/photo-1542744095-fcf48d80b0fd?q=80&w=1476&auto=format&fit=crop', 'Primary image of product #114', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(276, 114, 'https://images.unsplash.com/photo-1661961112951-f2bfd1f253ce?q=80&w=1372&auto=format&fit=crop', 'Secondary image of product #114', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(277, 114, 'https://images.unsplash.com/photo-1661961112951-f2bfd1f253ce?q=80&w=1372&auto=format&fit=crop', 'Additional view of product #114', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(278, 115, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?q=80&w=1468&auto=format&fit=crop', 'Primary image of product #115', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(279, 115, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?q=80&w=1364&auto=format&fit=crop', 'Secondary image of product #115', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(280, 116, 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=1472&auto=format&fit=crop', 'Primary image of product #116', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(281, 116, 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?q=80&w=1471&auto=format&fit=crop', 'Secondary image of product #116', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(282, 117, 'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #117', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(283, 117, 'https://images.unsplash.com/photo-1542393545-10f5cde2c810?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #117', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(284, 117, 'https://images.unsplash.com/photo-1542393545-10f5cde2c810?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #117', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(285, 118, 'https://images.unsplash.com/photo-1600861194942-f883de0dfe96?q=80&w=1649&auto=format&fit=crop', 'Primary image of product #118', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(286, 118, 'https://images.unsplash.com/photo-1593642632823-8f785ba67e45?q=80&w=1305&auto=format&fit=crop', 'Secondary image of product #118', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(287, 119, 'https://images.unsplash.com/photo-1542744095-fcf48d80b0fd?q=80&w=1476&auto=format&fit=crop', 'Primary image of product #119', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(288, 119, 'https://images.unsplash.com/photo-1661961112951-f2bfd1f253ce?q=80&w=1372&auto=format&fit=crop', 'Secondary image of product #119', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(289, 120, 'https://images.unsplash.com/photo-1603302576837-37561b2e2302?q=80&w=1468&auto=format&fit=crop', 'Primary image of product #120', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(290, 120, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?q=80&w=1364&auto=format&fit=crop', 'Secondary image of product #120', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(291, 120, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?q=80&w=1364&auto=format&fit=crop', 'Additional view of product #120', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(292, 120, 'https://images.unsplash.com/photo-1525547719571-a2d4ac8945e2?q=80&w=1364&auto=format&fit=crop', '360-degree view of product #120', '360-view', 0, 1, '2025-05-23 15:03:39', NULL),
(293, 121, 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?q=80&w=1298&auto=format&fit=crop', 'Primary image of product #121', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(294, 121, 'https://images.unsplash.com/photo-1605408499391-6368c628ef42?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #121', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(295, 122, 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=1374&auto=format&fit=crop', 'Primary image of product #122', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(296, 122, 'https://images.unsplash.com/photo-1554735490-5974588cbc4f?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #122', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(297, 123, 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #123', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(298, 123, 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=1480&auto=format&fit=crop', 'Secondary image of product #123', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(299, 123, 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=1480&auto=format&fit=crop', 'Additional view of product #123', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(300, 124, 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #124', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(301, 124, 'https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?q=80&w=1288&auto=format&fit=crop', 'Secondary image of product #124', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(302, 125, 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?q=80&w=1364&auto=format&fit=crop', 'Primary image of product #125', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(303, 125, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #125', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(304, 126, 'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?q=80&w=1298&auto=format&fit=crop', 'Primary image of product #126', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(305, 126, 'https://images.unsplash.com/photo-1605408499391-6368c628ef42?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #126', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(306, 126, 'https://images.unsplash.com/photo-1605408499391-6368c628ef42?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #126', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(307, 127, 'https://images.unsplash.com/photo-1608231387042-66d1773070a5?q=80&w=1374&auto=format&fit=crop', 'Primary image of product #127', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(308, 127, 'https://images.unsplash.com/photo-1554735490-5974588cbc4f?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #127', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(309, 128, 'https://images.unsplash.com/photo-1511556532299-8f662fc26c06?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #128', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(310, 128, 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?q=80&w=1480&auto=format&fit=crop', 'Secondary image of product #128', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(311, 129, 'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #129', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(312, 129, 'https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?q=80&w=1288&auto=format&fit=crop', 'Secondary image of product #129', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(313, 129, 'https://images.unsplash.com/photo-1515347619252-60a4bf4fff4f?q=80&w=1288&auto=format&fit=crop', 'Additional view of product #129', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(314, 130, 'https://images.unsplash.com/photo-1606107557195-0e29a4b5b4aa?q=80&w=1364&auto=format&fit=crop', 'Primary image of product #130', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(315, 130, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=1470&auto=format&fit=crop', 'Secondary image of product #130', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(316, 130, 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?q=80&w=1470&auto=format&fit=crop', '360-degree view of product #130', '360-view', 0, 1, '2025-05-23 15:03:39', NULL),
(317, 131, 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #131', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(318, 131, 'https://images.unsplash.com/photo-1532012197267-da84d127e765?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #131', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(319, 132, 'https://images.unsplash.com/photo-1589998059171-988d887df646?q=80&w=1376&auto=format&fit=crop', 'Primary image of product #132', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(320, 132, 'https://images.unsplash.com/photo-1569728723358-d1a317aa7fba?q=80&w=1374&auto=format&fit=crop', 'Secondary image of product #132', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(321, 132, 'https://images.unsplash.com/photo-1569728723358-d1a317aa7fba?q=80&w=1374&auto=format&fit=crop', 'Additional view of product #132', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(322, 133, 'https://images.unsplash.com/photo-1607923432780-7a9c30adecaa?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #133', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(323, 133, 'https://images.unsplash.com/photo-1589492477829-5e65395b66cc?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #133', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(324, 134, 'https://images.unsplash.com/photo-1614332287897-cdc485fa562d?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #134', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(325, 134, 'https://images.unsplash.com/photo-1629808955872-922a85fa64e2?q=80&w=1374&auto=format&fit=crop', 'Secondary image of product #134', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(326, 135, 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=1290&auto=format&fit=crop', 'Primary image of product #135', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(327, 135, 'https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=1374&auto=format&fit=crop', 'Secondary image of product #135', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(328, 135, 'https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=1374&auto=format&fit=crop', 'Additional view of product #135', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(329, 136, 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=1287&auto=format&fit=crop', 'Primary image of product #136', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(330, 136, 'https://images.unsplash.com/photo-1532012197267-da84d127e765?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #136', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(331, 137, 'https://images.unsplash.com/photo-1589998059171-988d887df646?q=80&w=1376&auto=format&fit=crop', 'Primary image of product #137', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(332, 137, 'https://images.unsplash.com/photo-1569728723358-d1a317aa7fba?q=80&w=1374&auto=format&fit=crop', 'Secondary image of product #137', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(333, 138, 'https://images.unsplash.com/photo-1607923432780-7a9c30adecaa?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #138', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(334, 138, 'https://images.unsplash.com/photo-1589492477829-5e65395b66cc?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #138', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(335, 138, 'https://images.unsplash.com/photo-1589492477829-5e65395b66cc?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #138', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(336, 139, 'https://images.unsplash.com/photo-1614332287897-cdc485fa562d?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #139', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(337, 139, 'https://images.unsplash.com/photo-1629808955872-922a85fa64e2?q=80&w=1374&auto=format&fit=crop', 'Secondary image of product #139', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(338, 140, 'https://images.unsplash.com/photo-1507842217343-583bb7270b66?q=80&w=1290&auto=format&fit=crop', 'Primary image of product #140', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(339, 140, 'https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=1374&auto=format&fit=crop', 'Secondary image of product #140', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(340, 140, 'https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=1374&auto=format&fit=crop', '360-degree view of product #140', '360-view', 0, 1, '2025-05-23 15:03:39', NULL),
(341, 141, 'https://images.unsplash.com/photo-1516734212186-a967f81ad0d7?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #141', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(342, 141, 'https://images.unsplash.com/photo-1602584386319-fa8eb4361c2c?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #141', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(343, 141, 'https://images.unsplash.com/photo-1602584386319-fa8eb4361c2c?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #141', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(344, 142, 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?q=80&w=1471&auto=format&fit=crop', 'Primary image of product #142', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(345, 142, 'https://images.unsplash.com/photo-1594053186687-7788421edc93?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #142', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(346, 143, 'https://images.unsplash.com/photo-1610554675829-18eeee40ef11?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #143', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(347, 143, 'https://images.unsplash.com/photo-1581467655410-0c2bf55d9d6c?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #143', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(348, 144, 'https://images.unsplash.com/photo-1576201836106-db1758fd1c97?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #144', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(349, 144, 'https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #144', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(350, 144, 'https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #144', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(351, 145, 'https://images.unsplash.com/photo-1548767797-d8c844163c4c?q=80&w=1171&auto=format&fit=crop', 'Primary image of product #145', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(352, 145, 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?q=80&w=1364&auto=format&fit=crop', 'Secondary image of product #145', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(353, 146, 'https://images.unsplash.com/photo-1516734212186-a967f81ad0d7?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #146', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(354, 146, 'https://images.unsplash.com/photo-1602584386319-fa8eb4361c2c?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #146', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(355, 147, 'https://images.unsplash.com/photo-1589924691995-400dc9ecc119?q=80&w=1471&auto=format&fit=crop', 'Primary image of product #147', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(356, 147, 'https://images.unsplash.com/photo-1594053186687-7788421edc93?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #147', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(357, 147, 'https://images.unsplash.com/photo-1594053186687-7788421edc93?q=80&w=1287&auto=format&fit=crop', 'Additional view of product #147', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(358, 148, 'https://images.unsplash.com/photo-1610554675829-18eeee40ef11?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #148', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(359, 148, 'https://images.unsplash.com/photo-1581467655410-0c2bf55d9d6c?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #148', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(360, 149, 'https://images.unsplash.com/photo-1576201836106-db1758fd1c97?q=80&w=1470&auto=format&fit=crop', 'Primary image of product #149', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(361, 149, 'https://images.unsplash.com/photo-1526336024174-e58f5cdd8e13?q=80&w=1287&auto=format&fit=crop', 'Secondary image of product #149', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(362, 150, 'https://images.unsplash.com/photo-1548767797-d8c844163c4c?q=80&w=1171&auto=format&fit=crop', 'Primary image of product #150', 'thumbnail', 1, 1, '2025-05-23 15:03:39', NULL),
(363, 150, 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?q=80&w=1364&auto=format&fit=crop', 'Secondary image of product #150', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(364, 150, 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?q=80&w=1364&auto=format&fit=crop', 'Additional view of product #150', 'gallery', 0, 1, '2025-05-23 15:03:39', NULL),
(365, 150, 'https://images.unsplash.com/photo-1583337130417-3346a1be7dee?q=80&w=1364&auto=format&fit=crop', '360-degree view of product #150', '360-view', 0, 1, '2025-05-23 15:03:39', NULL);

--
-- Triggers `product_images`
--
DROP TRIGGER IF EXISTS `before_product_image_insert`;
DELIMITER $$
CREATE TRIGGER `before_product_image_insert` BEFORE INSERT ON `product_images` FOR EACH ROW BEGIN
    IF NEW.is_primary = 1 THEN
        UPDATE product_images SET is_primary = 0 WHERE product_id = NEW.product_id;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
CREATE TABLE IF NOT EXISTS `product_variants` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` bigint UNSIGNED NOT NULL,
  `sku` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `price_adjustment` decimal(10,2) NOT NULL DEFAULT '0.00',
  `attributes` json NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_sku_unique` (`sku`),
  KEY `product_variants_product_id_index` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promo_codes`
--

DROP TABLE IF EXISTS `promo_codes`;
CREATE TABLE IF NOT EXISTS `promo_codes` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `discount_type` enum('percentage','fixed','free_shipping') COLLATE utf8mb4_general_ci NOT NULL,
  `discount_value` decimal(10,2) NOT NULL,
  `min_order_amount` decimal(10,2) DEFAULT NULL,
  `max_uses` int DEFAULT NULL,
  `target_audience` json DEFAULT NULL,
  `valid_from` timestamp NOT NULL,
  `valid_until` timestamp NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `promo_codes_code_unique` (`code`),
  KEY `promo_codes_is_active_index` (`is_active`),
  KEY `promo_codes_created_by_index` (`created_by`),
  KEY `promo_codes_is_active_valid_until_index` (`is_active`,`valid_until`),
  KEY `promo_codes_code_valid_until_is_active_index` (`code`,`valid_until`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_codes`
--

INSERT INTO `promo_codes` (`id`, `code`, `discount_type`, `discount_value`, `min_order_amount`, `max_uses`, `target_audience`, `valid_from`, `valid_until`, `is_active`, `created_by`, `created_at`, `deleted_at`) VALUES
(1, 'WELCOME25', 'percentage', 25.00, 50.00, 1000, '{\"new_users\": true}', '2025-04-23 15:03:42', '2025-07-22 15:03:42', 1, 1, '2025-05-23 15:03:42', NULL),
(2, 'SUMMER2025', 'percentage', 15.00, 75.00, 500, '{\"all\": true}', '2025-05-08 15:03:42', '2025-07-07 15:03:42', 1, 1, '2025-05-23 15:03:42', NULL),
(3, 'FREESHIP100', 'free_shipping', 0.00, 100.00, 2000, '{\"all\": true}', '2025-03-24 15:03:42', '2025-06-22 15:03:42', 1, 2, '2025-05-23 15:03:42', NULL),
(4, 'FLASH50', 'fixed', 50.00, 200.00, 100, '{\"vip_customers\": true}', '2025-05-18 15:03:42', '2025-05-25 15:03:42', 1, 3, '2025-05-23 15:03:42', NULL),
(5, 'LOYALTY10', 'percentage', 10.00, 0.00, NULL, '{\"repeat_customers\": true}', '2025-02-22 15:03:42', '2026-02-22 15:03:42', 1, 1, '2025-05-23 15:03:42', NULL);

--
-- Triggers `promo_codes`
--
DROP TRIGGER IF EXISTS `before_promo_insert`;
DELIMITER $$
CREATE TRIGGER `before_promo_insert` BEFORE INSERT ON `promo_codes` FOR EACH ROW BEGIN
                IF NEW.valid_from >= NEW.valid_until THEN
                    SIGNAL SQLSTATE "45000"
                    SET MESSAGE_TEXT = "Valid from date must be before valid until date";
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `promo_code_usages`
--

DROP TABLE IF EXISTS `promo_code_usages`;
CREATE TABLE IF NOT EXISTS `promo_code_usages` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `promo_code_id` bigint UNSIGNED NOT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `discount_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `used_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_promo_usage_per_order` (`user_id`,`promo_code_id`,`order_id`),
  KEY `promo_code_usages_promo_code_id_foreign` (`promo_code_id`),
  KEY `promo_code_usages_order_id_foreign` (`order_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `promo_code_usages`
--

INSERT INTO `promo_code_usages` (`id`, `user_id`, `promo_code_id`, `order_id`, `discount_amount`, `used_at`, `created_at`, `updated_at`) VALUES
(1, 11, 1, 10, 0.00, '2025-05-23 17:58:40', '2025-05-23 17:58:40', '2025-05-23 17:58:40'),
(2, 11, 1, 11, 0.00, '2025-05-23 18:12:06', '2025-05-23 18:12:06', '2025-05-23 18:12:06'),
(3, 11, 1, 12, 0.00, '2025-05-23 18:13:10', '2025-05-23 18:13:10', '2025-05-23 18:13:10');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text COLLATE utf8mb4_general_ci NOT NULL,
  `sentiment_score` double DEFAULT NULL,
  `helpful_count` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `moderation_status` enum('pending','approved','rejected') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `moderated_by` bigint UNSIGNED DEFAULT NULL,
  `moderated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reviews_product_id_index` (`product_id`),
  KEY `reviews_sentiment_score_index` (`sentiment_score`),
  KEY `reviews_moderation_status_index` (`moderation_status`),
  KEY `reviews_user_id_index` (`user_id`),
  KEY `reviews_moderated_by_index` (`moderated_by`),
  KEY `reviews_product_id_rating_created_at_index` (`product_id`,`rating`,`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=751 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `sentiment_score`, `helpful_count`, `created_at`, `moderation_status`, `moderated_by`, `moderated_at`, `deleted_at`) VALUES
(1, 10, 1, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9598054572565533, 23, '2024-12-27 15:03:39', 'pending', NULL, NULL, NULL),
(2, 8, 1, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6527875443667057, 10, '2025-02-09 15:03:39', 'approved', 2, '2025-05-17 15:03:39', NULL),
(3, 5, 1, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8035173136773681, 17, '2025-01-16 15:03:39', 'approved', 7, '2025-05-22 15:03:39', NULL),
(4, 2, 1, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8248115745790411, 21, '2025-05-23 15:03:39', 'approved', 8, '2025-05-20 15:03:39', NULL),
(5, 4, 1, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6814513767151641, 40, '2025-03-21 15:03:39', 'approved', 7, '2025-05-22 15:03:39', NULL),
(6, 9, 2, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8332851690736461, 9, '2025-02-25 15:03:39', 'approved', 8, '2025-05-20 15:03:39', NULL),
(7, 2, 2, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7702615988350116, 3, '2024-11-29 15:03:39', 'approved', 7, '2025-05-23 15:03:39', NULL),
(8, 7, 2, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8011086621518327, 19, '2025-05-14 15:03:39', 'approved', 6, '2025-05-20 15:03:39', NULL),
(9, 10, 2, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9904288243413241, 25, '2025-03-02 15:03:39', 'approved', 3, '2025-05-18 15:03:39', NULL),
(10, 1, 2, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6593036141053807, 7, '2025-02-12 15:03:39', 'approved', 7, '2025-05-19 15:03:39', NULL),
(11, 2, 3, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.2755358790564685, 40, '2024-12-26 15:03:39', 'approved', 10, '2025-05-22 15:03:39', NULL),
(12, 3, 3, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7503769383489871, 43, '2025-03-06 15:03:39', 'approved', 6, '2025-05-20 15:03:39', NULL),
(13, 10, 3, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5202274545656773, 17, '2024-12-06 15:03:39', 'approved', 5, '2025-05-20 15:03:39', NULL),
(14, 2, 3, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8066857753383795, 17, '2025-02-05 15:03:39', 'approved', 9, '2025-05-19 15:03:39', NULL),
(15, 3, 3, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8063404198049944, 11, '2025-01-04 15:03:39', 'approved', 9, '2025-05-23 15:03:39', NULL),
(16, 6, 4, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7820152482967966, 48, '2025-03-30 15:03:39', 'approved', 5, '2025-05-23 15:03:39', NULL),
(17, 4, 4, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8997685535808733, 11, '2024-12-22 15:03:39', 'approved', 8, '2025-05-20 15:03:39', NULL),
(18, 9, 4, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9583460086568688, 13, '2024-12-22 15:03:39', 'approved', 10, '2025-05-23 15:03:39', NULL),
(19, 6, 4, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5016031892426341, 12, '2025-05-19 15:03:39', 'pending', NULL, NULL, NULL),
(20, 8, 4, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7275008202041507, 21, '2025-01-29 15:03:39', 'approved', 1, '2025-05-22 15:03:39', NULL),
(21, 2, 5, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6960323712006513, 48, '2025-02-03 15:03:39', 'approved', 6, '2025-05-23 15:03:39', NULL),
(22, 4, 5, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9287963549874689, 5, '2025-02-12 15:03:39', 'approved', 2, '2025-05-22 15:03:39', NULL),
(23, 8, 5, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9596358628567643, 49, '2025-03-24 15:03:39', 'approved', 9, '2025-05-22 15:03:39', NULL),
(24, 6, 5, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6143965023145047, 1, '2025-04-15 15:03:39', 'approved', 6, '2025-05-22 15:03:39', NULL),
(25, 1, 5, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5041739725919198, 24, '2024-12-18 15:03:39', 'approved', 7, '2025-05-18 15:03:39', NULL),
(26, 2, 6, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6258387489857513, 30, '2025-05-18 15:03:39', 'approved', 9, '2025-05-23 15:03:39', NULL),
(27, 9, 6, 1, 'I am disappointed with this product. The quality is much lower than advertised. I regret this purchase.', 0.2960644373447303, 13, '2025-02-07 15:03:39', 'approved', 5, '2025-05-20 15:03:39', NULL),
(28, 1, 6, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5095887910105183, 12, '2024-12-31 15:03:39', 'approved', 10, '2025-05-21 15:03:39', NULL),
(29, 7, 6, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.4959552255048931, 17, '2025-02-19 15:03:39', 'approved', 9, '2025-05-18 15:03:39', NULL),
(30, 6, 6, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5737766981253183, 16, '2025-04-20 15:03:39', 'approved', 1, '2025-05-19 15:03:39', NULL),
(31, 2, 7, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8393127532296933, 10, '2025-05-09 15:03:39', 'approved', 4, '2025-05-21 15:03:39', NULL),
(32, 1, 7, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.563068151290592, 47, '2025-01-20 15:03:39', 'approved', 9, '2025-05-22 15:03:39', NULL),
(33, 9, 7, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.639429761915868, 48, '2025-02-26 15:03:39', 'approved', 3, '2025-05-19 15:03:39', NULL),
(34, 6, 7, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.42350310383690815, 17, '2025-01-03 15:03:39', 'approved', 4, '2025-05-21 15:03:39', NULL),
(35, 5, 7, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8551409004769669, 17, '2025-02-25 15:03:39', 'pending', NULL, NULL, NULL),
(36, 8, 8, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7635631757635281, 31, '2025-05-06 15:03:39', 'approved', 8, '2025-05-22 15:03:39', NULL),
(37, 1, 8, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8300524802413327, 30, '2025-02-14 15:03:39', 'approved', 9, '2025-05-21 15:03:39', NULL),
(38, 7, 8, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5000557138957602, 11, '2025-02-13 15:03:39', 'approved', 10, '2025-05-17 15:03:39', NULL),
(39, 2, 8, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6170682473639661, 36, '2025-04-30 15:03:39', 'approved', 1, '2025-05-17 15:03:39', NULL),
(40, 8, 8, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7855954675987321, 6, '2025-04-10 15:03:39', 'approved', 10, '2025-05-17 15:03:39', NULL),
(41, 7, 9, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9051496013115623, 7, '2025-05-09 15:03:39', 'approved', 2, '2025-05-20 15:03:39', NULL),
(42, 9, 9, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.544924640082684, 44, '2025-02-11 15:03:39', 'approved', 4, '2025-05-22 15:03:39', NULL),
(43, 3, 9, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6895759487613812, 30, '2025-02-11 15:03:39', 'approved', 9, '2025-05-20 15:03:39', NULL),
(44, 2, 9, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5780008588153877, 13, '2025-04-09 15:03:39', 'approved', 1, '2025-05-19 15:03:39', NULL),
(45, 10, 9, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7467218753571826, 17, '2025-05-02 15:03:39', 'approved', 5, '2025-05-17 15:03:39', NULL),
(46, 6, 10, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5475218918058293, 47, '2025-01-28 15:03:39', 'approved', 8, '2025-05-23 15:03:39', NULL),
(47, 8, 10, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7010700844424498, 25, '2025-01-28 15:03:39', 'approved', 3, '2025-05-21 15:03:39', NULL),
(48, 3, 10, 1, 'I am disappointed with this product. The quality is much lower than advertised. I regret this purchase.', 0.24702420674918613, 2, '2025-01-06 15:03:39', 'pending', NULL, NULL, NULL),
(49, 8, 10, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6291503802585885, 12, '2025-03-05 15:03:39', 'approved', 5, '2025-05-18 15:03:39', NULL),
(50, 5, 10, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7730757591250127, 0, '2025-04-09 15:03:39', 'approved', 2, '2025-05-22 15:03:39', NULL),
(51, 3, 11, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7964778701928239, 44, '2025-03-14 15:03:39', 'approved', 1, '2025-05-23 15:03:39', NULL),
(52, 5, 11, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5859401405099222, 49, '2025-05-13 15:03:39', 'approved', 1, '2025-05-23 15:03:39', NULL),
(53, 10, 11, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6584844624423277, 13, '2024-12-12 15:03:39', 'approved', 4, '2025-05-18 15:03:39', NULL),
(54, 1, 11, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5806316549150567, 18, '2025-04-28 15:03:39', 'approved', 6, '2025-05-22 15:03:39', NULL),
(55, 6, 11, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6294552250480794, 6, '2025-03-29 15:03:39', 'approved', 3, '2025-05-20 15:03:39', NULL),
(56, 7, 12, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6090782383913903, 32, '2025-04-21 15:03:39', 'approved', 3, '2025-05-18 15:03:39', NULL),
(57, 10, 12, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9555466826591145, 15, '2025-04-05 15:03:39', 'approved', 3, '2025-05-19 15:03:39', NULL),
(58, 2, 12, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.694207744872391, 2, '2024-12-30 15:03:39', 'approved', 8, '2025-05-20 15:03:39', NULL),
(59, 4, 12, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.792658624157923, 27, '2025-04-24 15:03:39', 'approved', 5, '2025-05-18 15:03:39', NULL),
(60, 8, 12, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5200750548020704, 21, '2025-01-17 15:03:39', 'approved', 6, '2025-05-18 15:03:39', NULL),
(61, 5, 13, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6686261368064453, 24, '2025-04-22 15:03:39', 'approved', 10, '2025-05-21 15:03:39', NULL),
(62, 9, 13, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5918035148007827, 34, '2025-03-15 15:03:39', 'approved', 8, '2025-05-21 15:03:39', NULL),
(63, 9, 13, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8454641592180955, 30, '2025-03-01 15:03:39', 'approved', 9, '2025-05-17 15:03:39', NULL),
(64, 9, 13, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5360239775628075, 9, '2025-05-19 15:03:39', 'approved', 2, '2025-05-19 15:03:39', NULL),
(65, 5, 13, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8250757530565148, 13, '2025-02-18 15:03:39', 'approved', 6, '2025-05-23 15:03:39', NULL),
(66, 9, 14, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.432288168177277, 31, '2025-01-25 15:03:39', 'approved', 7, '2025-05-23 15:03:39', NULL),
(67, 6, 14, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8226288398193464, 41, '2025-02-01 15:03:39', 'approved', 7, '2025-05-20 15:03:39', NULL),
(68, 7, 14, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.49808478312388504, 8, '2025-05-05 15:03:39', 'approved', 1, '2025-05-18 15:03:39', NULL),
(69, 10, 14, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5532741974976605, 4, '2025-04-24 15:03:39', 'approved', 5, '2025-05-17 15:03:39', NULL),
(70, 2, 14, 1, 'I am disappointed with this product. The quality is much lower than advertised. I regret this purchase.', 0.2846701871274693, 3, '2025-03-30 15:03:39', 'approved', 6, '2025-05-18 15:03:39', NULL),
(71, 5, 15, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7481259413697998, 14, '2025-02-18 15:03:39', 'approved', 8, '2025-05-21 15:03:39', NULL),
(72, 8, 15, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6789435481642778, 19, '2025-04-13 15:03:39', 'approved', 4, '2025-05-21 15:03:39', NULL),
(73, 3, 15, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7061542535444296, 0, '2024-12-09 15:03:40', 'approved', 2, '2025-05-23 15:03:40', NULL),
(74, 8, 15, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6413067919028055, 45, '2025-02-04 15:03:40', 'approved', 9, '2025-05-20 15:03:40', NULL),
(75, 1, 15, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5686841152689271, 8, '2025-01-13 15:03:40', 'approved', 5, '2025-05-22 15:03:40', NULL),
(76, 7, 16, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6794453140343031, 46, '2025-02-22 15:03:40', 'approved', 10, '2025-05-21 15:03:40', NULL),
(77, 8, 16, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9383285402677288, 34, '2025-04-11 15:03:40', 'approved', 5, '2025-05-19 15:03:40', NULL),
(78, 9, 16, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.4785360180200413, 46, '2025-01-10 15:03:40', 'approved', 4, '2025-05-21 15:03:40', NULL),
(79, 1, 16, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9965202383664625, 11, '2025-02-16 15:03:40', 'approved', 7, '2025-05-19 15:03:40', NULL),
(80, 1, 16, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5637557104264905, 4, '2025-05-13 15:03:40', 'approved', 2, '2025-05-20 15:03:40', NULL),
(81, 2, 17, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7467567943658278, 33, '2025-01-07 15:03:40', 'approved', 4, '2025-05-21 15:03:40', NULL),
(82, 10, 17, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7496665251158797, 45, '2025-04-27 15:03:40', 'pending', NULL, NULL, NULL),
(83, 2, 17, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8992177083149717, 25, '2025-05-14 15:03:40', 'approved', 5, '2025-05-22 15:03:40', NULL),
(84, 5, 17, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.3816311405428044, 17, '2025-04-06 15:03:40', 'approved', 8, '2025-05-18 15:03:40', NULL),
(85, 8, 17, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6600757282041718, 26, '2025-05-21 15:03:40', 'approved', 7, '2025-05-21 15:03:40', NULL),
(86, 1, 18, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7705250403755578, 4, '2025-04-05 15:03:40', 'approved', 3, '2025-05-22 15:03:40', NULL),
(87, 6, 18, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8957048292231792, 47, '2025-02-05 15:03:40', 'approved', 4, '2025-05-22 15:03:40', NULL),
(88, 7, 18, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7940828302074996, 5, '2025-03-29 15:03:40', 'approved', 1, '2025-05-22 15:03:40', NULL),
(89, 1, 18, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8466749469253002, 48, '2025-01-10 15:03:40', 'approved', 2, '2025-05-18 15:03:40', NULL),
(90, 3, 18, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8410783820236832, 8, '2025-04-10 15:03:40', 'approved', 1, '2025-05-20 15:03:40', NULL),
(91, 2, 19, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6576596184611876, 39, '2025-05-02 15:03:40', 'approved', 4, '2025-05-22 15:03:40', NULL),
(92, 9, 19, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9085661840704886, 1, '2025-03-09 15:03:40', 'approved', 3, '2025-05-18 15:03:40', NULL),
(93, 5, 19, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7729025152445794, 48, '2025-03-05 15:03:40', 'approved', 10, '2025-05-20 15:03:40', NULL),
(94, 7, 19, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6736819241137075, 38, '2025-01-13 15:03:40', 'approved', 8, '2025-05-19 15:03:40', NULL),
(95, 8, 19, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.47230378634510917, 31, '2024-12-15 15:03:40', 'approved', 3, '2025-05-19 15:03:40', NULL),
(96, 7, 20, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.42204324787672914, 13, '2025-01-06 15:03:40', 'approved', 3, '2025-05-23 15:03:40', NULL),
(97, 7, 20, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.673271293671123, 18, '2024-12-18 15:03:40', 'pending', NULL, NULL, NULL),
(98, 10, 20, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7786987672547798, 11, '2025-02-16 15:03:40', 'approved', 3, '2025-05-18 15:03:40', NULL),
(99, 10, 20, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.4272049512594985, 24, '2025-04-18 15:03:40', 'approved', 9, '2025-05-18 15:03:40', NULL),
(100, 4, 20, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.700297296438643, 37, '2025-01-14 15:03:40', 'approved', 7, '2025-05-17 15:03:40', NULL),
(101, 8, 21, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6472816052728161, 36, '2025-02-23 15:03:40', 'approved', 10, '2025-05-23 15:03:40', NULL),
(102, 9, 21, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8442221807727798, 11, '2024-12-22 15:03:40', 'approved', 7, '2025-05-20 15:03:40', NULL),
(103, 6, 21, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8101762009879353, 42, '2024-12-01 15:03:40', 'approved', 7, '2025-05-20 15:03:40', NULL),
(104, 6, 21, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.735071915066868, 19, '2025-04-27 15:03:40', 'approved', 10, '2025-05-22 15:03:40', NULL),
(105, 3, 21, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9820062991064101, 28, '2025-03-01 15:03:40', 'approved', 6, '2025-05-20 15:03:40', NULL),
(106, 7, 22, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.18094950808300592, 20, '2025-02-02 15:03:40', 'approved', 2, '2025-05-22 15:03:40', NULL),
(107, 2, 22, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.4125715397042889, 4, '2024-12-11 15:03:40', 'approved', 3, '2025-05-21 15:03:40', NULL),
(108, 5, 22, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6965458398838955, 19, '2025-04-13 15:03:40', 'approved', 9, '2025-05-20 15:03:40', NULL),
(109, 1, 22, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7176422871999837, 9, '2025-03-12 15:03:40', 'approved', 6, '2025-05-21 15:03:40', NULL),
(110, 10, 22, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8656134615332014, 10, '2025-02-26 15:03:40', 'approved', 2, '2025-05-22 15:03:40', NULL),
(111, 10, 23, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7991680520765186, 36, '2024-12-15 15:03:40', 'approved', 9, '2025-05-19 15:03:40', NULL),
(112, 8, 23, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7956164301518505, 11, '2025-02-17 15:03:40', 'approved', 7, '2025-05-20 15:03:40', NULL),
(113, 5, 23, 1, 'I am disappointed with this product. The quality is much lower than advertised. Would not recommend.', 0.29105093606845567, 10, '2025-04-16 15:03:40', 'approved', 8, '2025-05-21 15:03:40', NULL),
(114, 4, 23, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7648196381375376, 30, '2025-01-29 15:03:40', 'approved', 5, '2025-05-19 15:03:40', NULL),
(115, 4, 23, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9581075803917904, 49, '2025-04-09 15:03:40', 'approved', 6, '2025-05-23 15:03:40', NULL),
(116, 8, 24, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7777687146307609, 8, '2025-05-16 15:03:40', 'approved', 3, '2025-05-22 15:03:40', NULL),
(117, 5, 24, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9189030404378689, 1, '2025-01-11 15:03:40', 'approved', 6, '2025-05-21 15:03:40', NULL),
(118, 2, 24, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9798331113344367, 6, '2025-04-16 15:03:40', 'pending', NULL, NULL, NULL),
(119, 10, 24, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6033952493624717, 41, '2024-12-05 15:03:40', 'approved', 5, '2025-05-19 15:03:40', NULL),
(120, 9, 24, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9342724417655472, 46, '2025-05-19 15:03:40', 'approved', 10, '2025-05-20 15:03:40', NULL),
(121, 1, 25, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6442710362042031, 1, '2025-05-23 15:03:40', 'approved', 2, '2025-05-19 15:03:40', NULL),
(122, 8, 25, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6891726819883778, 6, '2024-12-24 15:03:40', 'pending', NULL, NULL, NULL),
(123, 4, 25, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.3351225766307885, 5, '2025-03-29 15:03:40', 'approved', 2, '2025-05-17 15:03:40', NULL),
(124, 10, 25, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9290827780301523, 13, '2024-12-09 15:03:40', 'approved', 9, '2025-05-21 15:03:40', NULL),
(125, 4, 25, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.622544741502539, 4, '2025-02-13 15:03:40', 'approved', 3, '2025-05-20 15:03:40', NULL),
(126, 9, 26, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7007336264669277, 15, '2025-01-10 15:03:40', 'approved', 2, '2025-05-20 15:03:40', NULL),
(127, 9, 26, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7434245704332596, 5, '2025-02-17 15:03:40', 'pending', NULL, NULL, NULL),
(128, 9, 26, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7253076281820495, 40, '2024-11-28 15:03:40', 'approved', 8, '2025-05-19 15:03:40', NULL),
(129, 1, 26, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8296478683405071, 49, '2024-12-27 15:03:40', 'approved', 2, '2025-05-20 15:03:40', NULL),
(130, 10, 26, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7109098017503599, 17, '2025-03-06 15:03:40', 'approved', 5, '2025-05-22 15:03:40', NULL),
(131, 4, 27, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6507361490006895, 11, '2025-01-21 15:03:40', 'approved', 2, '2025-05-19 15:03:40', NULL),
(132, 1, 27, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8147000388565474, 4, '2024-12-23 15:03:40', 'approved', 6, '2025-05-23 15:03:40', NULL),
(133, 8, 27, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5477612454330187, 2, '2025-03-07 15:03:40', 'approved', 5, '2025-05-18 15:03:40', NULL),
(134, 5, 27, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8243493394407904, 42, '2025-05-10 15:03:40', 'approved', 2, '2025-05-20 15:03:40', NULL),
(135, 3, 27, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9852546956252817, 30, '2024-12-20 15:03:40', 'approved', 9, '2025-05-17 15:03:40', NULL),
(136, 9, 28, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6787411124620037, 22, '2025-02-25 15:03:40', 'approved', 6, '2025-05-20 15:03:40', NULL),
(137, 5, 28, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.47722009107211616, 44, '2025-04-19 15:03:40', 'approved', 5, '2025-05-17 15:03:40', NULL),
(138, 10, 28, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.44164610877786403, 46, '2025-01-30 15:03:40', 'approved', 5, '2025-05-22 15:03:40', NULL),
(139, 7, 28, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9684091254774567, 40, '2025-01-20 15:03:40', 'approved', 7, '2025-05-22 15:03:40', NULL),
(140, 10, 28, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6327737697146588, 26, '2025-03-22 15:03:40', 'approved', 4, '2025-05-19 15:03:40', NULL),
(141, 3, 29, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7488691177860547, 46, '2025-02-27 15:03:40', 'approved', 9, '2025-05-18 15:03:40', NULL),
(142, 7, 29, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.5090378776835631, 24, '2025-05-05 15:03:40', 'approved', 1, '2025-05-17 15:03:40', NULL),
(143, 7, 29, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5678511938153311, 32, '2025-04-16 15:03:40', 'pending', NULL, NULL, NULL),
(144, 8, 29, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6460610922109905, 49, '2025-05-17 15:03:40', 'approved', 6, '2025-05-21 15:03:40', NULL),
(145, 6, 29, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8011568932991073, 2, '2024-11-28 15:03:40', 'approved', 4, '2025-05-17 15:03:40', NULL),
(146, 4, 30, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7530174289578735, 31, '2025-01-15 15:03:40', 'approved', 6, '2025-05-20 15:03:40', NULL),
(147, 2, 30, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.553302798227689, 25, '2024-12-02 15:03:40', 'approved', 2, '2025-05-18 15:03:40', NULL),
(148, 7, 30, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.625551911877051, 1, '2025-05-23 15:03:40', 'pending', NULL, NULL, NULL),
(149, 2, 30, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6542195462567914, 43, '2025-01-10 15:03:40', 'approved', 1, '2025-05-17 15:03:40', NULL),
(150, 5, 30, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.3449619569861907, 24, '2025-05-04 15:03:40', 'approved', 5, '2025-05-22 15:03:40', NULL),
(151, 4, 31, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7736251057066257, 6, '2025-04-01 15:03:40', 'approved', 7, '2025-05-21 15:03:40', NULL),
(152, 6, 31, 1, 'I am disappointed with this product. It did not meet my expectations at all. I regret this purchase.', 0.2405797570204174, 45, '2024-12-28 15:03:40', 'approved', 1, '2025-05-23 15:03:40', NULL),
(153, 10, 31, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6955988826375444, 48, '2025-05-13 15:03:40', 'approved', 3, '2025-05-18 15:03:40', NULL),
(154, 6, 31, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8915820426229221, 19, '2025-03-16 15:03:40', 'approved', 3, '2025-05-23 15:03:40', NULL),
(155, 7, 31, 1, 'I am disappointed with this product. The quality is much lower than advertised. Would not recommend.', 0.2076580854949151, 3, '2025-03-30 15:03:40', 'approved', 4, '2025-05-18 15:03:40', NULL),
(156, 8, 32, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9610584076131308, 24, '2025-01-26 15:03:40', 'approved', 9, '2025-05-22 15:03:40', NULL),
(157, 6, 32, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7124679539655037, 33, '2025-02-26 15:03:40', 'approved', 8, '2025-05-20 15:03:40', NULL),
(158, 2, 32, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6932707252104494, 44, '2025-03-04 15:03:40', 'approved', 7, '2025-05-23 15:03:40', NULL),
(159, 6, 32, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8434796028244119, 10, '2025-01-11 15:03:40', 'approved', 4, '2025-05-21 15:03:40', NULL),
(160, 7, 32, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8124808622640379, 38, '2025-03-18 15:03:40', 'approved', 3, '2025-05-21 15:03:40', NULL),
(161, 10, 33, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.4057921357134284, 3, '2025-04-17 15:03:40', 'approved', 8, '2025-05-23 15:03:40', NULL),
(162, 1, 33, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7558624061531037, 17, '2025-04-07 15:03:40', 'pending', NULL, NULL, NULL),
(163, 10, 33, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.783818074971268, 29, '2024-12-31 15:03:40', 'approved', 9, '2025-05-18 15:03:40', NULL),
(164, 4, 33, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.47943171992845063, 48, '2025-02-28 15:03:40', 'approved', 7, '2025-05-23 15:03:40', NULL),
(165, 1, 33, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7015761267594771, 0, '2025-02-20 15:03:40', 'approved', 4, '2025-05-22 15:03:40', NULL),
(166, 9, 34, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9041233026460962, 47, '2025-05-22 15:03:40', 'approved', 8, '2025-05-20 15:03:40', NULL),
(167, 4, 34, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9886440455807783, 0, '2025-05-04 15:03:40', 'approved', 8, '2025-05-20 15:03:40', NULL),
(168, 5, 34, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.46525686888513795, 28, '2025-05-13 15:03:40', 'approved', 4, '2025-05-21 15:03:40', NULL),
(169, 9, 34, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8533083649848666, 49, '2025-04-29 15:03:40', 'approved', 8, '2025-05-20 15:03:40', NULL),
(170, 1, 34, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8322219732703846, 1, '2025-04-20 15:03:40', 'approved', 1, '2025-05-17 15:03:40', NULL),
(171, 4, 35, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8338056584389933, 16, '2025-02-10 15:03:40', 'approved', 6, '2025-05-17 15:03:40', NULL),
(172, 9, 35, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5274035187134551, 28, '2025-03-14 15:03:40', 'approved', 2, '2025-05-19 15:03:40', NULL),
(173, 9, 35, 1, 'I am disappointed with this product. The quality is much lower than advertised. Would not recommend.', 0.30426658893401415, 26, '2025-03-22 15:03:40', 'approved', 8, '2025-05-20 15:03:40', NULL),
(174, 5, 35, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7248887291596167, 40, '2025-02-25 15:03:40', 'approved', 6, '2025-05-23 15:03:40', NULL),
(175, 9, 35, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8085982473647206, 18, '2025-03-04 15:03:40', 'approved', 1, '2025-05-17 15:03:40', NULL),
(176, 4, 36, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7670039319312236, 35, '2025-04-12 15:03:40', 'approved', 7, '2025-05-19 15:03:40', NULL),
(177, 3, 36, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8077331484553714, 32, '2025-03-23 15:03:40', 'approved', 6, '2025-05-19 15:03:40', NULL),
(178, 9, 36, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.30703874415442234, 27, '2024-12-03 15:03:40', 'approved', 9, '2025-05-22 15:03:40', NULL),
(179, 5, 36, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.825230508134915, 11, '2025-01-17 15:03:40', 'approved', 4, '2025-05-20 15:03:40', NULL),
(180, 6, 36, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9686320572799371, 39, '2024-12-18 15:03:40', 'approved', 9, '2025-05-18 15:03:40', NULL),
(181, 4, 37, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8162837743165752, 15, '2025-01-28 15:03:40', 'approved', 5, '2025-05-22 15:03:40', NULL),
(182, 9, 37, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7661052142140504, 9, '2025-02-04 15:03:40', 'approved', 4, '2025-05-23 15:03:40', NULL),
(183, 4, 37, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6189805048135859, 41, '2025-03-26 15:03:40', 'approved', 2, '2025-05-19 15:03:40', NULL),
(184, 9, 37, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8296741556652582, 25, '2025-03-04 15:03:40', 'approved', 9, '2025-05-17 15:03:40', NULL),
(185, 3, 37, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6156826248911047, 19, '2025-04-02 15:03:40', 'approved', 4, '2025-05-18 15:03:40', NULL),
(186, 7, 38, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.42194623908209267, 22, '2025-03-08 15:03:40', 'approved', 3, '2025-05-23 15:03:40', NULL),
(187, 5, 38, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6732602590632255, 41, '2024-12-24 15:03:40', 'approved', 2, '2025-05-23 15:03:40', NULL),
(188, 3, 38, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7655271964944221, 34, '2025-04-30 15:03:40', 'approved', 7, '2025-05-18 15:03:40', NULL),
(189, 9, 38, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7090041760625356, 16, '2025-05-20 15:03:40', 'approved', 5, '2025-05-21 15:03:40', NULL),
(190, 3, 38, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8263507159672219, 39, '2025-05-07 15:03:40', 'approved', 10, '2025-05-20 15:03:40', NULL),
(191, 4, 39, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6600843404420506, 9, '2025-05-23 15:03:40', 'approved', 6, '2025-05-19 15:03:40', NULL),
(192, 4, 39, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7267863367188595, 24, '2024-11-26 15:03:40', 'approved', 10, '2025-05-18 15:03:40', NULL),
(193, 2, 39, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6503664105109558, 44, '2025-03-25 15:03:40', 'approved', 3, '2025-05-20 15:03:40', NULL),
(194, 6, 39, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6125016255793176, 31, '2025-01-19 15:03:40', 'approved', 9, '2025-05-17 15:03:40', NULL),
(195, 5, 39, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9861197264735772, 35, '2025-04-17 15:03:40', 'approved', 10, '2025-05-17 15:03:40', NULL),
(196, 9, 40, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.787283289532441, 10, '2025-03-03 15:03:40', 'approved', 7, '2025-05-18 15:03:40', NULL),
(197, 8, 40, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7074630860495037, 14, '2025-03-14 15:03:40', 'approved', 8, '2025-05-19 15:03:40', NULL),
(198, 9, 40, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7731373191747231, 28, '2024-12-04 15:03:40', 'approved', 7, '2025-05-22 15:03:40', NULL),
(199, 6, 40, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8374172924434927, 7, '2025-04-28 15:03:40', 'approved', 4, '2025-05-22 15:03:40', NULL),
(200, 10, 40, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5629118371782003, 48, '2025-02-11 15:03:40', 'approved', 9, '2025-05-19 15:03:40', NULL),
(201, 5, 41, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5153703897775807, 38, '2025-05-23 15:03:40', 'approved', 4, '2025-05-18 15:03:40', NULL),
(202, 6, 41, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.33208855231487056, 4, '2025-02-17 15:03:40', 'approved', 1, '2025-05-18 15:03:40', NULL),
(203, 10, 41, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6983660506255609, 43, '2025-03-17 15:03:40', 'approved', 3, '2025-05-21 15:03:40', NULL),
(204, 10, 41, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.445079207238815, 45, '2025-05-19 15:03:40', 'approved', 5, '2025-05-22 15:03:40', NULL),
(205, 8, 41, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7659651559646848, 26, '2024-12-25 15:03:40', 'approved', 6, '2025-05-23 15:03:40', NULL),
(206, 10, 42, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7869769879681776, 20, '2024-11-27 15:03:40', 'approved', 1, '2025-05-21 15:03:40', NULL),
(207, 9, 42, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6607590276568747, 4, '2025-03-02 15:03:40', 'approved', 7, '2025-05-18 15:03:40', NULL),
(208, 10, 42, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8338084640296255, 4, '2025-05-10 15:03:40', 'approved', 7, '2025-05-22 15:03:40', NULL),
(209, 10, 42, 1, 'I am disappointed with this product. The quality is much lower than advertised. Would not recommend.', 0.23032644140564507, 34, '2024-12-29 15:03:40', 'approved', 9, '2025-05-18 15:03:40', NULL),
(210, 2, 42, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.653458918047565, 24, '2025-05-15 15:03:40', 'approved', 5, '2025-05-23 15:03:40', NULL),
(211, 10, 43, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6502247590294339, 13, '2025-05-11 15:03:40', 'approved', 8, '2025-05-19 15:03:40', NULL),
(212, 1, 43, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7528882185676026, 32, '2024-12-07 15:03:40', 'approved', 5, '2025-05-22 15:03:40', NULL),
(213, 10, 43, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.888715279184948, 13, '2025-02-23 15:03:40', 'pending', NULL, NULL, NULL),
(214, 8, 43, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8439327452740938, 13, '2025-03-30 15:03:40', 'approved', 4, '2025-05-18 15:03:40', NULL),
(215, 9, 43, 1, 'I am disappointed with this product. The quality is much lower than advertised. Would not recommend.', 0.24711945702053556, 7, '2025-02-11 15:03:40', 'approved', 7, '2025-05-19 15:03:40', NULL),
(216, 6, 44, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7416741507702267, 45, '2024-12-11 15:03:40', 'approved', 8, '2025-05-17 15:03:40', NULL),
(217, 4, 44, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6492252958651867, 23, '2025-02-23 15:03:40', 'approved', 3, '2025-05-17 15:03:40', NULL),
(218, 10, 44, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7130246430943018, 11, '2025-01-15 15:03:40', 'approved', 10, '2025-05-20 15:03:40', NULL),
(219, 6, 44, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5767248498431639, 40, '2025-01-21 15:03:40', 'approved', 1, '2025-05-22 15:03:40', NULL),
(220, 2, 44, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7424110390827163, 18, '2024-12-31 15:03:40', 'approved', 8, '2025-05-22 15:03:40', NULL),
(221, 1, 45, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6110560440002532, 2, '2025-04-12 15:03:40', 'approved', 9, '2025-05-18 15:03:40', NULL),
(222, 1, 45, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6035921201515869, 33, '2024-12-17 15:03:40', 'approved', 8, '2025-05-17 15:03:40', NULL),
(223, 6, 45, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5344555994257849, 13, '2025-04-25 15:03:40', 'approved', 2, '2025-05-22 15:03:40', NULL),
(224, 5, 45, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6427592617299028, 39, '2025-03-10 15:03:40', 'approved', 9, '2025-05-22 15:03:40', NULL),
(225, 1, 45, 1, 'I am disappointed with this product. The quality is much lower than advertised. I regret this purchase.', 0.3462907156220551, 26, '2024-12-04 15:03:40', 'approved', 1, '2025-05-21 15:03:40', NULL),
(226, 10, 46, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.512277712255975, 16, '2024-12-09 15:03:40', 'approved', 6, '2025-05-17 15:03:40', NULL),
(227, 4, 46, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8796987083551462, 9, '2025-02-14 15:03:40', 'approved', 10, '2025-05-17 15:03:40', NULL),
(228, 8, 46, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8224087930679403, 1, '2025-02-20 15:03:40', 'approved', 6, '2025-05-21 15:03:40', NULL),
(229, 9, 46, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.482406770179427, 1, '2025-01-01 15:03:40', 'approved', 10, '2025-05-22 15:03:40', NULL),
(230, 4, 46, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6689391455696329, 10, '2025-01-27 15:03:40', 'approved', 1, '2025-05-21 15:03:40', NULL),
(231, 4, 47, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9091993278909469, 41, '2025-05-06 15:03:40', 'approved', 5, '2025-05-23 15:03:40', NULL),
(232, 10, 47, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8129389366255505, 37, '2025-02-15 15:03:40', 'approved', 4, '2025-05-17 15:03:40', NULL),
(233, 7, 47, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7121045733169695, 4, '2025-04-13 15:03:40', 'approved', 1, '2025-05-20 15:03:40', NULL),
(234, 7, 47, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7959087222031399, 23, '2024-12-06 15:03:40', 'approved', 1, '2025-05-20 15:03:40', NULL),
(235, 5, 47, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7171471128586188, 33, '2024-12-30 15:03:40', 'approved', 3, '2025-05-18 15:03:40', NULL),
(236, 5, 48, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.4946915758165452, 38, '2025-04-08 15:03:40', 'approved', 8, '2025-05-22 15:03:40', NULL),
(237, 6, 48, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.38075962210144815, 13, '2024-12-15 15:03:40', 'approved', 6, '2025-05-21 15:03:40', NULL),
(238, 9, 48, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6597312168960751, 44, '2025-03-16 15:03:40', 'approved', 5, '2025-05-23 15:03:40', NULL),
(239, 3, 48, 1, 'I am disappointed with this product. The quality is much lower than advertised. Would not recommend.', 0.20371413518089254, 2, '2025-03-05 15:03:40', 'approved', 10, '2025-05-20 15:03:40', NULL),
(240, 4, 48, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8175681216060818, 47, '2025-01-25 15:03:40', 'approved', 9, '2025-05-22 15:03:40', NULL),
(241, 7, 49, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7269619694416989, 25, '2024-12-14 15:03:40', 'approved', 8, '2025-05-23 15:03:40', NULL),
(242, 10, 49, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8073388010331791, 18, '2025-03-29 15:03:40', 'approved', 1, '2025-05-21 15:03:40', NULL),
(243, 7, 49, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7935224932744378, 28, '2025-05-22 15:03:40', 'approved', 1, '2025-05-22 15:03:40', NULL),
(244, 9, 49, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7205363102634775, 4, '2025-02-13 15:03:40', 'approved', 5, '2025-05-18 15:03:40', NULL),
(245, 4, 49, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9761410841496113, 9, '2025-02-09 15:03:40', 'approved', 5, '2025-05-21 15:03:40', NULL),
(246, 4, 50, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6716706535887631, 30, '2025-01-07 15:03:40', 'approved', 5, '2025-05-18 15:03:40', NULL),
(247, 8, 50, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9333049974714454, 25, '2025-04-12 15:03:40', 'approved', 4, '2025-05-23 15:03:40', NULL),
(248, 5, 50, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7656186313234443, 9, '2025-04-11 15:03:40', 'approved', 5, '2025-05-21 15:03:40', NULL),
(249, 8, 50, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7495799568198435, 45, '2024-12-03 15:03:40', 'pending', NULL, NULL, NULL);
INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `sentiment_score`, `helpful_count`, `created_at`, `moderation_status`, `moderated_by`, `moderated_at`, `deleted_at`) VALUES
(250, 4, 50, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8612713507015923, 4, '2025-03-18 15:03:40', 'approved', 3, '2025-05-22 15:03:40', NULL),
(251, 1, 51, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7582144302113116, 0, '2025-02-05 15:03:40', 'approved', 9, '2025-05-19 15:03:40', NULL),
(252, 7, 51, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7573784543921971, 16, '2025-05-05 15:03:40', 'approved', 8, '2025-05-19 15:03:40', NULL),
(253, 7, 51, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5042472756879844, 19, '2025-05-01 15:03:40', 'approved', 4, '2025-05-21 15:03:40', NULL),
(254, 1, 51, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6488353502832682, 6, '2025-02-14 15:03:40', 'approved', 3, '2025-05-20 15:03:40', NULL),
(255, 1, 51, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.3549549675685866, 40, '2025-05-16 15:03:40', 'approved', 2, '2025-05-18 15:03:40', NULL),
(256, 2, 52, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.3610375789190061, 39, '2025-01-15 15:03:40', 'approved', 7, '2025-05-21 15:03:40', NULL),
(257, 5, 52, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8704541313186792, 9, '2025-03-29 15:03:40', 'approved', 10, '2025-05-18 15:03:40', NULL),
(258, 10, 52, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6479268408268009, 36, '2025-01-05 15:03:40', 'approved', 4, '2025-05-22 15:03:40', NULL),
(259, 3, 52, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5154818070637824, 22, '2025-01-03 15:03:40', 'approved', 10, '2025-05-20 15:03:40', NULL),
(260, 5, 52, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8134300859770087, 46, '2025-02-05 15:03:40', 'approved', 1, '2025-05-19 15:03:40', NULL),
(261, 7, 53, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.807304694063314, 25, '2025-04-04 15:03:40', 'approved', 7, '2025-05-19 15:03:40', NULL),
(262, 10, 53, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5094715617126521, 19, '2025-03-21 15:03:40', 'approved', 9, '2025-05-23 15:03:40', NULL),
(263, 8, 53, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.4970809469531113, 16, '2025-03-18 15:03:40', 'pending', NULL, NULL, NULL),
(264, 2, 53, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6261584817116693, 18, '2025-05-21 15:03:40', 'approved', 7, '2025-05-22 15:03:40', NULL),
(265, 3, 53, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.3796638274003415, 15, '2025-03-09 15:03:40', 'approved', 5, '2025-05-17 15:03:40', NULL),
(266, 6, 54, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6441966702828152, 43, '2025-03-22 15:03:40', 'approved', 2, '2025-05-17 15:03:40', NULL),
(267, 2, 54, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6471438503704442, 1, '2025-05-20 15:03:40', 'approved', 7, '2025-05-21 15:03:40', NULL),
(268, 5, 54, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8277160777037182, 30, '2025-04-07 15:03:40', 'approved', 1, '2025-05-19 15:03:40', NULL),
(269, 2, 54, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8309987042387936, 19, '2025-02-17 15:03:40', 'approved', 5, '2025-05-19 15:03:40', NULL),
(270, 1, 54, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8285366348442963, 27, '2025-05-03 15:03:40', 'approved', 7, '2025-05-23 15:03:40', NULL),
(271, 4, 55, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.892427215066205, 48, '2024-11-26 15:03:40', 'approved', 2, '2025-05-19 15:03:40', NULL),
(272, 10, 55, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.625831577429391, 49, '2025-02-21 15:03:40', 'approved', 3, '2025-05-18 15:03:40', NULL),
(273, 5, 55, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5602902879941187, 25, '2025-01-14 15:03:40', 'approved', 7, '2025-05-22 15:03:40', NULL),
(274, 9, 55, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8857263947704122, 24, '2025-03-11 15:03:40', 'approved', 3, '2025-05-17 15:03:40', NULL),
(275, 2, 55, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6390566750234521, 17, '2025-04-06 15:03:40', 'approved', 2, '2025-05-18 15:03:40', NULL),
(276, 5, 56, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8080959792510569, 12, '2025-05-20 15:03:40', 'approved', 7, '2025-05-22 15:03:40', NULL),
(277, 3, 56, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7576843385376821, 36, '2025-05-08 15:03:40', 'pending', NULL, NULL, NULL),
(278, 4, 56, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.757976048884891, 27, '2025-01-11 15:03:40', 'approved', 8, '2025-05-21 15:03:40', NULL),
(279, 6, 56, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7829745638211952, 12, '2025-02-14 15:03:40', 'approved', 4, '2025-05-17 15:03:40', NULL),
(280, 6, 56, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6965603707512472, 39, '2025-03-23 15:03:40', 'approved', 9, '2025-05-21 15:03:40', NULL),
(281, 2, 57, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9968615997553446, 47, '2025-01-26 15:03:40', 'approved', 1, '2025-05-21 15:03:40', NULL),
(282, 4, 57, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5118841366394276, 38, '2024-12-23 15:03:40', 'approved', 9, '2025-05-18 15:03:40', NULL),
(283, 7, 57, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9509572838907664, 43, '2025-01-01 15:03:40', 'approved', 6, '2025-05-23 15:03:40', NULL),
(284, 2, 57, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.3620696778614723, 0, '2025-03-18 15:03:40', 'approved', 5, '2025-05-17 15:03:40', NULL),
(285, 4, 57, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5844212642166962, 14, '2024-11-26 15:03:40', 'pending', NULL, NULL, NULL),
(286, 5, 58, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.3324439852428101, 9, '2025-04-28 15:03:40', 'approved', 6, '2025-05-22 15:03:40', NULL),
(287, 8, 58, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8259232657271655, 40, '2025-02-02 15:03:40', 'approved', 6, '2025-05-22 15:03:40', NULL),
(288, 10, 58, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7278167626334511, 25, '2024-12-14 15:03:40', 'approved', 8, '2025-05-23 15:03:40', NULL),
(289, 1, 58, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6661046970506243, 0, '2024-12-07 15:03:40', 'approved', 4, '2025-05-18 15:03:40', NULL),
(290, 1, 58, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6855253890208205, 44, '2025-03-10 15:03:40', 'approved', 10, '2025-05-18 15:03:40', NULL),
(291, 7, 59, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9581258093548248, 39, '2025-01-16 15:03:40', 'approved', 6, '2025-05-17 15:03:40', NULL),
(292, 6, 59, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8261477253829573, 49, '2025-01-23 15:03:40', 'approved', 6, '2025-05-18 15:03:40', NULL),
(293, 3, 59, 1, 'I am disappointed with this product. It did not meet my expectations at all. I regret this purchase.', 0.20091937564399037, 0, '2025-05-03 15:03:40', 'approved', 1, '2025-05-23 15:03:40', NULL),
(294, 3, 59, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.704246797248951, 2, '2025-03-09 15:03:40', 'approved', 7, '2025-05-22 15:03:40', NULL),
(295, 10, 59, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5447323139614726, 27, '2025-05-03 15:03:40', 'approved', 6, '2025-05-21 15:03:40', NULL),
(296, 10, 60, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6848682327241341, 21, '2025-05-09 15:03:40', 'approved', 9, '2025-05-23 15:03:40', NULL),
(297, 10, 60, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6754705870481865, 20, '2024-12-15 15:03:40', 'approved', 10, '2025-05-22 15:03:40', NULL),
(298, 1, 60, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.4452378146399165, 49, '2025-04-24 15:03:40', 'approved', 8, '2025-05-21 15:03:40', NULL),
(299, 5, 60, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8101562051197106, 7, '2025-01-17 15:03:40', 'approved', 2, '2025-05-19 15:03:40', NULL),
(300, 1, 60, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8448667273343231, 49, '2025-03-10 15:03:40', 'approved', 5, '2025-05-18 15:03:40', NULL),
(301, 10, 61, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.43737511839473164, 44, '2025-05-04 15:03:40', 'approved', 6, '2025-05-21 15:03:40', NULL),
(302, 4, 61, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6433128629655697, 32, '2025-03-30 15:03:40', 'approved', 6, '2025-05-19 15:03:40', NULL),
(303, 10, 61, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8010610831166255, 36, '2025-01-12 15:03:40', 'approved', 4, '2025-05-19 15:03:40', NULL),
(304, 2, 61, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.8355714907455924, 38, '2025-01-05 15:03:40', 'pending', NULL, NULL, NULL),
(305, 7, 61, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6220861591976938, 24, '2025-02-15 15:03:40', 'approved', 1, '2025-05-21 15:03:40', NULL),
(306, 1, 62, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.3413565591176567, 24, '2025-04-01 15:03:40', 'approved', 8, '2025-05-18 15:03:40', NULL),
(307, 7, 62, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8228389741320526, 41, '2024-11-30 15:03:40', 'approved', 2, '2025-05-23 15:03:40', NULL),
(308, 6, 62, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6217042629250364, 24, '2024-12-28 15:03:40', 'approved', 1, '2025-05-17 15:03:40', NULL),
(309, 7, 62, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9727319629608951, 7, '2025-04-02 15:03:40', 'approved', 4, '2025-05-18 15:03:40', NULL),
(310, 1, 62, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.8155987029295403, 36, '2025-03-17 15:03:40', 'approved', 6, '2025-05-19 15:03:40', NULL),
(311, 8, 63, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.3915589667405551, 29, '2025-01-08 15:03:40', 'approved', 6, '2025-05-20 15:03:40', NULL),
(312, 1, 63, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9862563380843554, 48, '2024-12-21 15:03:40', 'approved', 6, '2025-05-23 15:03:40', NULL),
(313, 9, 63, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6944867365197155, 32, '2025-03-07 15:03:40', 'approved', 8, '2025-05-19 15:03:40', NULL),
(314, 9, 63, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6493065567401299, 2, '2024-11-29 15:03:40', 'approved', 2, '2025-05-23 15:03:40', NULL),
(315, 7, 63, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.22587016737635263, 25, '2025-04-04 15:03:40', 'approved', 3, '2025-05-20 15:03:40', NULL),
(316, 6, 64, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7958702056816502, 49, '2024-12-26 15:03:40', 'approved', 4, '2025-05-22 15:03:40', NULL),
(317, 3, 64, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6666143637025863, 0, '2025-01-06 15:03:40', 'approved', 7, '2025-05-22 15:03:40', NULL),
(318, 9, 64, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6458231122100941, 49, '2025-03-25 15:03:40', 'approved', 3, '2025-05-21 15:03:40', NULL),
(319, 8, 64, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.2869254974340326, 8, '2025-05-04 15:03:40', 'pending', NULL, NULL, NULL),
(320, 1, 64, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8822072510441833, 4, '2025-05-02 15:03:40', 'approved', 7, '2025-05-18 15:03:40', NULL),
(321, 10, 65, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5200019226968307, 27, '2024-12-27 15:03:40', 'approved', 4, '2025-05-22 15:03:40', NULL),
(322, 2, 65, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8091951485063835, 45, '2025-01-22 15:03:40', 'approved', 9, '2025-05-23 15:03:40', NULL),
(323, 9, 65, 1, 'I am disappointed with this product. The quality is much lower than advertised. I regret this purchase.', 0.30118901560193767, 19, '2025-04-29 15:03:40', 'approved', 9, '2025-05-18 15:03:40', NULL),
(324, 2, 65, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7289735641786583, 49, '2025-01-18 15:03:40', 'approved', 10, '2025-05-20 15:03:40', NULL),
(325, 10, 65, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6294478992647006, 21, '2025-02-27 15:03:40', 'pending', NULL, NULL, NULL),
(326, 2, 66, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6744811979257327, 26, '2025-04-20 15:03:40', 'approved', 5, '2025-05-20 15:03:40', NULL),
(327, 4, 66, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.980461195838136, 10, '2024-11-28 15:03:40', 'approved', 3, '2025-05-22 15:03:40', NULL),
(328, 3, 66, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7429668179368292, 1, '2025-05-21 15:03:40', 'approved', 1, '2025-05-22 15:03:40', NULL),
(329, 9, 66, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6049969790922449, 47, '2025-01-16 15:03:40', 'approved', 4, '2025-05-19 15:03:40', NULL),
(330, 2, 66, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5328378770247454, 32, '2025-02-17 15:03:40', 'approved', 5, '2025-05-19 15:03:40', NULL),
(331, 6, 67, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.5025985241519273, 16, '2025-05-20 15:03:40', 'approved', 8, '2025-05-19 15:03:40', NULL),
(332, 10, 67, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.48221207868513843, 35, '2025-05-14 15:03:40', 'approved', 7, '2025-05-17 15:03:40', NULL),
(333, 9, 67, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6923407597582236, 4, '2025-02-27 15:03:40', 'approved', 1, '2025-05-17 15:03:40', NULL),
(334, 4, 67, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8745012662136008, 41, '2025-02-10 15:03:40', 'pending', NULL, NULL, NULL),
(335, 8, 67, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.3588939809416365, 47, '2025-02-05 15:03:40', 'approved', 8, '2025-05-18 15:03:40', NULL),
(336, 7, 68, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.3365022479523926, 18, '2025-02-09 15:03:40', 'approved', 6, '2025-05-17 15:03:40', NULL),
(337, 3, 68, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8161837363021297, 11, '2025-01-23 15:03:40', 'approved', 7, '2025-05-21 15:03:40', NULL),
(338, 9, 68, 1, 'I am disappointed with this product. It did not meet my expectations at all. I regret this purchase.', 0.28516343363110297, 3, '2024-12-18 15:03:40', 'approved', 10, '2025-05-21 15:03:40', NULL),
(339, 6, 68, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.5174093504225923, 42, '2024-12-11 15:03:40', 'approved', 7, '2025-05-20 15:03:40', NULL),
(340, 3, 68, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9917103972208784, 40, '2025-04-20 15:03:40', 'approved', 10, '2025-05-17 15:03:40', NULL),
(341, 1, 69, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9088287237182527, 25, '2025-04-15 15:03:40', 'approved', 1, '2025-05-19 15:03:40', NULL),
(342, 1, 69, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9178926455954953, 46, '2025-04-01 15:03:40', 'approved', 3, '2025-05-20 15:03:40', NULL),
(343, 7, 69, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.4395542137320658, 39, '2025-02-18 15:03:40', 'approved', 3, '2025-05-17 15:03:40', NULL),
(344, 5, 69, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9463361361495556, 41, '2025-01-31 15:03:40', 'pending', NULL, NULL, NULL),
(345, 1, 69, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5486154016001293, 2, '2025-01-19 15:03:40', 'approved', 8, '2025-05-19 15:03:40', NULL),
(346, 8, 70, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8324334872443541, 16, '2025-05-06 15:03:40', 'approved', 1, '2025-05-18 15:03:40', NULL),
(347, 7, 70, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8812695893284582, 4, '2024-12-09 15:03:40', 'approved', 3, '2025-05-20 15:03:40', NULL),
(348, 7, 70, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8358803404084224, 13, '2025-03-12 15:03:40', 'approved', 7, '2025-05-18 15:03:40', NULL),
(349, 5, 70, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8574906513630326, 25, '2025-03-12 15:03:40', 'approved', 8, '2025-05-19 15:03:40', NULL),
(350, 9, 70, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8361599533224106, 30, '2025-02-03 15:03:40', 'approved', 3, '2025-05-21 15:03:40', NULL),
(351, 3, 71, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.48679243840909786, 45, '2025-01-20 15:03:40', 'approved', 1, '2025-05-21 15:03:40', NULL),
(352, 4, 71, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8158435953183506, 12, '2024-12-03 15:03:40', 'approved', 5, '2025-05-22 15:03:40', NULL),
(353, 7, 71, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5756553491723307, 6, '2025-04-06 15:03:40', 'approved', 7, '2025-05-22 15:03:40', NULL),
(354, 3, 71, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.89875125205028, 19, '2025-05-08 15:03:40', 'approved', 2, '2025-05-20 15:03:40', NULL),
(355, 9, 71, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6633754672327782, 11, '2025-03-13 15:03:40', 'approved', 2, '2025-05-21 15:03:40', NULL),
(356, 8, 72, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7239588508791839, 3, '2025-01-25 15:03:40', 'approved', 10, '2025-05-20 15:03:40', NULL),
(357, 1, 72, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.722194619516092, 18, '2024-12-07 15:03:40', 'approved', 7, '2025-05-19 15:03:40', NULL),
(358, 10, 72, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.3270175847103983, 49, '2025-02-09 15:03:40', 'approved', 2, '2025-05-17 15:03:40', NULL),
(359, 10, 72, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6190799208908155, 37, '2025-04-21 15:03:40', 'approved', 6, '2025-05-17 15:03:40', NULL),
(360, 5, 72, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5889203831822801, 0, '2024-12-31 15:03:40', 'approved', 4, '2025-05-22 15:03:40', NULL),
(361, 1, 73, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6753881879107917, 41, '2025-03-06 15:03:40', 'approved', 2, '2025-05-20 15:03:40', NULL),
(362, 3, 73, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9856043182179373, 35, '2025-01-01 15:03:40', 'approved', 2, '2025-05-22 15:03:40', NULL),
(363, 9, 73, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5956460792903286, 35, '2025-04-22 15:03:40', 'approved', 6, '2025-05-23 15:03:40', NULL),
(364, 9, 73, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9876495377976909, 22, '2025-04-25 15:03:40', 'approved', 7, '2025-05-18 15:03:40', NULL),
(365, 3, 73, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.4706294508936158, 26, '2025-02-27 15:03:40', 'approved', 8, '2025-05-20 15:03:40', NULL),
(366, 5, 74, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8349074203846114, 17, '2025-01-09 15:03:40', 'approved', 2, '2025-05-22 15:03:40', NULL),
(367, 1, 74, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6426004843438049, 44, '2025-05-23 15:03:40', 'approved', 8, '2025-05-18 15:03:40', NULL),
(368, 6, 74, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9772106267299603, 26, '2025-04-03 15:03:40', 'approved', 4, '2025-05-17 15:03:40', NULL),
(369, 8, 74, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6685253092167204, 0, '2025-03-02 15:03:40', 'approved', 9, '2025-05-17 15:03:40', NULL),
(370, 10, 74, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.725053911325572, 36, '2025-04-07 15:03:40', 'approved', 3, '2025-05-21 15:03:40', NULL),
(371, 9, 75, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6729396687009741, 17, '2025-04-17 15:03:40', 'approved', 4, '2025-05-22 15:03:40', NULL),
(372, 1, 75, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9732493493456853, 7, '2025-03-21 15:03:40', 'approved', 4, '2025-05-20 15:03:40', NULL),
(373, 5, 75, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5264643415496353, 34, '2025-03-09 15:03:40', 'approved', 5, '2025-05-23 15:03:40', NULL),
(374, 7, 75, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6881166812108054, 16, '2025-01-22 15:03:40', 'approved', 6, '2025-05-18 15:03:40', NULL),
(375, 3, 75, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.3789063967195399, 17, '2025-03-27 15:03:40', 'approved', 1, '2025-05-20 15:03:40', NULL),
(376, 4, 76, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.36997446634804315, 5, '2024-12-05 15:03:40', 'approved', 2, '2025-05-18 15:03:40', NULL),
(377, 5, 76, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9299170858505341, 48, '2025-01-10 15:03:40', 'pending', NULL, NULL, NULL),
(378, 4, 76, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.86409269931176, 1, '2025-01-15 15:03:40', 'approved', 6, '2025-05-20 15:03:40', NULL),
(379, 1, 76, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7432291078038785, 42, '2025-04-23 15:03:40', 'approved', 1, '2025-05-18 15:03:40', NULL),
(380, 10, 76, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5707475118439156, 36, '2024-11-29 15:03:40', 'approved', 3, '2025-05-21 15:03:40', NULL),
(381, 9, 77, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.980903545749284, 42, '2025-05-17 15:03:40', 'approved', 8, '2025-05-21 15:03:40', NULL),
(382, 9, 77, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.3325942325336805, 1, '2025-02-23 15:03:40', 'pending', NULL, NULL, NULL),
(383, 6, 77, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.823044563776855, 34, '2025-01-09 15:03:40', 'approved', 10, '2025-05-20 15:03:40', NULL),
(384, 5, 77, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.4375041661760809, 40, '2024-12-18 15:03:40', 'approved', 6, '2025-05-17 15:03:40', NULL),
(385, 3, 77, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5226070047939262, 41, '2025-03-06 15:03:40', 'approved', 7, '2025-05-17 15:03:40', NULL),
(386, 7, 78, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.668345592830652, 24, '2025-04-04 15:03:40', 'approved', 8, '2025-05-22 15:03:40', NULL),
(387, 4, 78, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8063939409390036, 44, '2024-12-08 15:03:40', 'approved', 10, '2025-05-23 15:03:40', NULL),
(388, 6, 78, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9316308849785765, 32, '2025-01-27 15:03:40', 'approved', 10, '2025-05-18 15:03:40', NULL),
(389, 2, 78, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.602360607350581, 18, '2025-01-05 15:03:40', 'approved', 10, '2025-05-19 15:03:40', NULL),
(390, 2, 78, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7798257728105651, 8, '2025-02-25 15:03:40', 'approved', 4, '2025-05-21 15:03:40', NULL),
(391, 7, 79, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.4224562242463755, 25, '2025-02-02 15:03:40', 'approved', 2, '2025-05-18 15:03:40', NULL),
(392, 8, 79, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9979532806183709, 39, '2025-05-17 15:03:40', 'pending', NULL, NULL, NULL),
(393, 8, 79, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6853046197493604, 30, '2025-03-28 15:03:40', 'approved', 1, '2025-05-23 15:03:40', NULL),
(394, 5, 79, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7087746544077757, 25, '2025-01-05 15:03:40', 'approved', 2, '2025-05-20 15:03:40', NULL),
(395, 10, 79, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8167627357102584, 14, '2025-01-02 15:03:40', 'approved', 10, '2025-05-22 15:03:40', NULL),
(396, 4, 80, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6179082431438456, 45, '2025-05-23 15:03:40', 'approved', 7, '2025-05-22 15:03:40', NULL),
(397, 4, 80, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9966362824632193, 17, '2025-02-17 15:03:40', 'approved', 2, '2025-05-22 15:03:40', NULL),
(398, 8, 80, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9656932849117492, 40, '2025-02-02 15:03:40', 'approved', 6, '2025-05-17 15:03:40', NULL),
(399, 2, 80, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.3895093567199161, 35, '2025-01-01 15:03:40', 'approved', 1, '2025-05-23 15:03:40', NULL),
(400, 4, 80, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5199078408627844, 18, '2025-04-19 15:03:40', 'approved', 2, '2025-05-17 15:03:40', NULL),
(401, 5, 81, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6499864135775588, 25, '2025-04-25 15:03:40', 'pending', NULL, NULL, NULL),
(402, 8, 81, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7526707718825627, 32, '2025-05-09 15:03:40', 'approved', 6, '2025-05-20 15:03:40', NULL),
(403, 8, 81, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6274156062560301, 29, '2025-04-28 15:03:40', 'approved', 10, '2025-05-21 15:03:40', NULL),
(404, 2, 81, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.8284869335112041, 30, '2025-04-02 15:03:40', 'approved', 4, '2025-05-19 15:03:40', NULL),
(405, 6, 81, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.644877815586401, 19, '2025-04-03 15:03:40', 'approved', 7, '2025-05-20 15:03:40', NULL),
(406, 6, 82, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.4823548198140737, 47, '2025-02-12 15:03:40', 'approved', 10, '2025-05-17 15:03:40', NULL),
(407, 9, 82, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.648045493371827, 28, '2025-02-12 15:03:40', 'approved', 9, '2025-05-19 15:03:40', NULL),
(408, 9, 82, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7209481433415302, 43, '2025-04-12 15:03:40', 'approved', 6, '2025-05-18 15:03:40', NULL),
(409, 8, 82, 1, 'I am disappointed with this product. The quality is much lower than advertised. Would not recommend.', 0.1614115164069566, 47, '2024-12-25 15:03:40', 'approved', 10, '2025-05-20 15:03:40', NULL),
(410, 5, 82, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8582938220894838, 25, '2025-03-11 15:03:40', 'approved', 4, '2025-05-19 15:03:40', NULL),
(411, 4, 83, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.39213874298347046, 34, '2025-01-04 15:03:40', 'approved', 5, '2025-05-23 15:03:40', NULL),
(412, 1, 83, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7700846350659474, 41, '2025-01-30 15:03:40', 'approved', 5, '2025-05-22 15:03:40', NULL),
(413, 7, 83, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7348220237110016, 7, '2025-03-21 15:03:40', 'approved', 8, '2025-05-20 15:03:40', NULL),
(414, 3, 83, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5351129200077736, 40, '2024-12-05 15:03:41', 'approved', 10, '2025-05-23 15:03:41', NULL),
(415, 6, 83, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7368215786822341, 40, '2025-03-03 15:03:41', 'approved', 6, '2025-05-20 15:03:41', NULL),
(416, 6, 84, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7894060685386938, 19, '2024-12-29 15:03:41', 'approved', 6, '2025-05-20 15:03:41', NULL),
(417, 7, 84, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.49002350115219456, 30, '2025-02-15 15:03:41', 'pending', NULL, NULL, NULL),
(418, 4, 84, 1, 'I am disappointed with this product. The quality is much lower than advertised. I regret this purchase.', 0.1779603722113747, 20, '2024-12-08 15:03:41', 'approved', 1, '2025-05-19 15:03:41', NULL),
(419, 9, 84, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.48280835721903326, 34, '2025-04-13 15:03:41', 'approved', 6, '2025-05-23 15:03:41', NULL),
(420, 5, 84, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6468771928612862, 31, '2025-02-21 15:03:41', 'approved', 7, '2025-05-18 15:03:41', NULL),
(421, 7, 85, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.47577620934301634, 30, '2025-04-15 15:03:41', 'approved', 9, '2025-05-19 15:03:41', NULL),
(422, 7, 85, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6650787856290851, 37, '2025-03-22 15:03:41', 'approved', 9, '2025-05-21 15:03:41', NULL),
(423, 5, 85, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.878893088436586, 2, '2025-03-18 15:03:41', 'approved', 2, '2025-05-18 15:03:41', NULL),
(424, 2, 85, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7435830370183876, 22, '2025-05-13 15:03:41', 'approved', 6, '2025-05-19 15:03:41', NULL),
(425, 5, 85, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.834107091123338, 1, '2025-03-15 15:03:41', 'approved', 3, '2025-05-23 15:03:41', NULL),
(426, 8, 86, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.821929248629072, 48, '2025-05-10 15:03:41', 'approved', 7, '2025-05-18 15:03:41', NULL),
(427, 4, 86, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6805169286211179, 19, '2024-12-19 15:03:41', 'approved', 10, '2025-05-17 15:03:41', NULL),
(428, 1, 86, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.8202544779891656, 36, '2025-01-30 15:03:41', 'pending', NULL, NULL, NULL),
(429, 6, 86, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.24044390145730593, 45, '2025-02-11 15:03:41', 'approved', 8, '2025-05-17 15:03:41', NULL),
(430, 2, 86, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9865871734792248, 0, '2025-03-10 15:03:41', 'approved', 1, '2025-05-22 15:03:41', NULL),
(431, 8, 87, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6795567655186697, 33, '2025-03-18 15:03:41', 'approved', 1, '2025-05-22 15:03:41', NULL),
(432, 7, 87, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6718899084179568, 37, '2025-02-27 15:03:41', 'approved', 8, '2025-05-20 15:03:41', NULL),
(433, 2, 87, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.3294168257055961, 3, '2025-01-16 15:03:41', 'approved', 10, '2025-05-19 15:03:41', NULL),
(434, 4, 87, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7010653201686827, 18, '2025-04-10 15:03:41', 'approved', 4, '2025-05-23 15:03:41', NULL),
(435, 1, 87, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6978548148813237, 47, '2025-03-19 15:03:41', 'approved', 10, '2025-05-19 15:03:41', NULL),
(436, 4, 88, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9512632065930062, 12, '2025-05-21 15:03:41', 'pending', NULL, NULL, NULL),
(437, 2, 88, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6453418702681939, 47, '2025-04-09 15:03:41', 'approved', 3, '2025-05-20 15:03:41', NULL),
(438, 9, 88, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8271276922404093, 12, '2024-12-09 15:03:41', 'approved', 7, '2025-05-19 15:03:41', NULL),
(439, 4, 88, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7742162059007364, 42, '2025-03-29 15:03:41', 'approved', 5, '2025-05-22 15:03:41', NULL),
(440, 9, 88, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7379724408108485, 42, '2024-12-29 15:03:41', 'approved', 9, '2025-05-18 15:03:41', NULL),
(441, 6, 89, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.49460086250174873, 11, '2025-04-12 15:03:41', 'approved', 4, '2025-05-21 15:03:41', NULL),
(442, 3, 89, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6763426687534365, 40, '2025-01-11 15:03:41', 'approved', 6, '2025-05-20 15:03:41', NULL),
(443, 1, 89, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8905374026769189, 12, '2025-03-15 15:03:41', 'pending', NULL, NULL, NULL),
(444, 6, 89, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8404290527481857, 0, '2024-12-03 15:03:41', 'approved', 7, '2025-05-22 15:03:41', NULL),
(445, 1, 89, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8767346712543953, 39, '2025-03-28 15:03:41', 'approved', 10, '2025-05-19 15:03:41', NULL),
(446, 2, 90, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.5143142019159292, 33, '2024-12-04 15:03:41', 'approved', 6, '2025-05-23 15:03:41', NULL),
(447, 8, 90, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.5077789242079285, 24, '2025-01-23 15:03:41', 'approved', 7, '2025-05-20 15:03:41', NULL),
(448, 4, 90, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9185677777217401, 19, '2025-01-14 15:03:41', 'approved', 2, '2025-05-21 15:03:41', NULL),
(449, 8, 90, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7191501345850063, 5, '2024-12-07 15:03:41', 'approved', 3, '2025-05-21 15:03:41', NULL),
(450, 4, 90, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.48573792357532114, 6, '2025-05-01 15:03:41', 'approved', 2, '2025-05-20 15:03:41', NULL),
(451, 1, 91, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.491929079901361, 28, '2024-12-09 15:03:41', 'approved', 5, '2025-05-18 15:03:41', NULL),
(452, 2, 91, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5217513616771915, 20, '2025-04-17 15:03:41', 'approved', 8, '2025-05-21 15:03:41', NULL),
(453, 6, 91, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.4334619983969834, 37, '2025-03-10 15:03:41', 'approved', 6, '2025-05-22 15:03:41', NULL),
(454, 9, 91, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6686871345980905, 41, '2025-05-11 15:03:41', 'approved', 5, '2025-05-22 15:03:41', NULL),
(455, 5, 91, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6829265061793165, 15, '2024-12-12 15:03:41', 'approved', 6, '2025-05-23 15:03:41', NULL),
(456, 7, 92, 1, 'I am disappointed with this product. The quality is much lower than advertised. I regret this purchase.', 0.2140380331259575, 11, '2025-04-20 15:03:41', 'approved', 5, '2025-05-18 15:03:41', NULL),
(457, 9, 92, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.49274391320789596, 28, '2025-03-09 15:03:41', 'approved', 7, '2025-05-17 15:03:41', NULL),
(458, 10, 92, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7784065053783418, 4, '2025-03-03 15:03:41', 'approved', 10, '2025-05-21 15:03:41', NULL),
(459, 10, 92, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6698384919295446, 9, '2025-04-20 15:03:41', 'approved', 5, '2025-05-20 15:03:41', NULL),
(460, 5, 92, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7804807196375735, 24, '2025-04-06 15:03:41', 'approved', 7, '2025-05-21 15:03:41', NULL),
(461, 8, 93, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6804972898219687, 37, '2025-01-12 15:03:41', 'approved', 2, '2025-05-21 15:03:41', NULL),
(462, 7, 93, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9523164731937614, 3, '2025-03-01 15:03:41', 'approved', 2, '2025-05-21 15:03:41', NULL),
(463, 4, 93, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.42886801975673816, 24, '2025-02-02 15:03:41', 'approved', 8, '2025-05-17 15:03:41', NULL),
(464, 3, 93, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9102830442695721, 26, '2025-01-21 15:03:41', 'pending', NULL, NULL, NULL),
(465, 1, 93, 1, 'I am disappointed with this product. The quality is much lower than advertised. I regret this purchase.', 0.17715615374702604, 17, '2025-03-08 15:03:41', 'approved', 2, '2025-05-20 15:03:41', NULL),
(466, 10, 94, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6563342947292461, 40, '2025-03-15 15:03:41', 'approved', 9, '2025-05-21 15:03:41', NULL),
(467, 10, 94, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7512288112856716, 1, '2024-12-05 15:03:41', 'approved', 10, '2025-05-17 15:03:41', NULL),
(468, 8, 94, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.3358762907757203, 10, '2024-12-06 15:03:41', 'approved', 7, '2025-05-19 15:03:41', NULL),
(469, 9, 94, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6457633871266277, 16, '2024-12-04 15:03:41', 'approved', 6, '2025-05-23 15:03:41', NULL),
(470, 4, 94, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.567543063319794, 49, '2024-11-25 15:03:41', 'approved', 7, '2025-05-23 15:03:41', NULL),
(471, 6, 95, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7399840338714272, 7, '2025-03-06 15:03:41', 'approved', 8, '2025-05-21 15:03:41', NULL),
(472, 5, 95, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6437033439834633, 30, '2024-12-22 15:03:41', 'approved', 4, '2025-05-23 15:03:41', NULL),
(473, 4, 95, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9000449921004894, 29, '2025-04-04 15:03:41', 'approved', 6, '2025-05-18 15:03:41', NULL),
(474, 2, 95, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9048240833960699, 6, '2025-01-16 15:03:41', 'approved', 6, '2025-05-20 15:03:41', NULL),
(475, 2, 95, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9949281126213523, 46, '2025-02-21 15:03:41', 'approved', 8, '2025-05-22 15:03:41', NULL),
(476, 10, 96, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.8177746902571755, 14, '2024-11-28 15:03:41', 'approved', 1, '2025-05-21 15:03:41', NULL),
(477, 9, 96, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7575013783550835, 11, '2024-12-08 15:03:41', 'approved', 5, '2025-05-19 15:03:41', NULL),
(478, 10, 96, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7382130567526567, 13, '2024-12-09 15:03:41', 'pending', NULL, NULL, NULL),
(479, 9, 96, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8318933299108346, 1, '2024-12-29 15:03:41', 'approved', 4, '2025-05-20 15:03:41', NULL),
(480, 3, 96, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.4156248675432288, 5, '2025-05-07 15:03:41', 'approved', 2, '2025-05-21 15:03:41', NULL),
(481, 6, 97, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6830768238781736, 25, '2024-12-05 15:03:41', 'approved', 9, '2025-05-19 15:03:41', NULL),
(482, 5, 97, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7964035109769586, 24, '2025-04-26 15:03:41', 'approved', 9, '2025-05-19 15:03:41', NULL),
(483, 8, 97, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6988095706503927, 1, '2025-05-21 15:03:41', 'approved', 3, '2025-05-21 15:03:41', NULL),
(484, 8, 97, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6014296126006446, 30, '2024-12-15 15:03:41', 'approved', 5, '2025-05-18 15:03:41', NULL),
(485, 6, 97, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8065772102461916, 16, '2025-03-10 15:03:41', 'approved', 4, '2025-05-20 15:03:41', NULL),
(486, 7, 98, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.45080217626951835, 9, '2025-03-01 15:03:41', 'approved', 2, '2025-05-21 15:03:41', NULL),
(487, 6, 98, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6034007428618154, 32, '2025-01-17 15:03:41', 'approved', 1, '2025-05-23 15:03:41', NULL),
(488, 5, 98, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8500065723899813, 44, '2025-01-06 15:03:41', 'approved', 7, '2025-05-23 15:03:41', NULL),
(489, 4, 98, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7262518546043447, 19, '2025-04-14 15:03:41', 'approved', 3, '2025-05-21 15:03:41', NULL),
(490, 4, 98, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5776162970975193, 40, '2025-04-14 15:03:41', 'approved', 2, '2025-05-22 15:03:41', NULL),
(491, 5, 99, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9842279979812243, 24, '2024-12-01 15:03:41', 'approved', 6, '2025-05-23 15:03:41', NULL),
(492, 6, 99, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.4700498389360028, 36, '2025-04-02 15:03:41', 'approved', 9, '2025-05-20 15:03:41', NULL),
(493, 7, 99, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7157003226091156, 39, '2025-02-28 15:03:41', 'approved', 9, '2025-05-23 15:03:41', NULL),
(494, 7, 99, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7801405836829363, 20, '2025-04-10 15:03:41', 'pending', NULL, NULL, NULL),
(495, 2, 99, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.8320741248802042, 17, '2024-12-04 15:03:41', 'approved', 1, '2025-05-19 15:03:41', NULL);
INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `sentiment_score`, `helpful_count`, `created_at`, `moderation_status`, `moderated_by`, `moderated_at`, `deleted_at`) VALUES
(496, 8, 100, 1, 'I am disappointed with this product. The quality is much lower than advertised. Would not recommend.', 0.3254003994217146, 2, '2024-12-29 15:03:41', 'approved', 9, '2025-05-19 15:03:41', NULL),
(497, 8, 100, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6378952902535864, 44, '2024-12-30 15:03:41', 'approved', 6, '2025-05-22 15:03:41', NULL),
(498, 6, 100, 1, 'I am disappointed with this product. It did not meet my expectations at all. I regret this purchase.', 0.25118830775021417, 11, '2025-05-10 15:03:41', 'approved', 4, '2025-05-20 15:03:41', NULL),
(499, 7, 100, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7722076705584374, 12, '2025-01-06 15:03:41', 'approved', 6, '2025-05-21 15:03:41', NULL),
(500, 10, 100, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7833510587954438, 28, '2025-04-27 15:03:41', 'approved', 8, '2025-05-20 15:03:41', NULL),
(501, 3, 101, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6416699623332079, 26, '2024-11-28 15:03:41', 'approved', 2, '2025-05-23 15:03:41', NULL),
(502, 6, 101, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.666434407594087, 21, '2025-03-26 15:03:41', 'approved', 2, '2025-05-18 15:03:41', NULL),
(503, 6, 101, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7374117233393823, 34, '2025-01-22 15:03:41', 'approved', 9, '2025-05-21 15:03:41', NULL),
(504, 4, 101, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6588090533193285, 27, '2025-03-21 15:03:41', 'approved', 6, '2025-05-17 15:03:41', NULL),
(505, 7, 101, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6860903192368246, 42, '2025-05-03 15:03:41', 'approved', 7, '2025-05-18 15:03:41', NULL),
(506, 3, 102, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.48734079156754617, 6, '2025-05-08 15:03:41', 'approved', 2, '2025-05-19 15:03:41', NULL),
(507, 7, 102, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7371284787143846, 17, '2025-04-03 15:03:41', 'approved', 5, '2025-05-21 15:03:41', NULL),
(508, 6, 102, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6734146134866538, 38, '2025-04-03 15:03:41', 'approved', 4, '2025-05-17 15:03:41', NULL),
(509, 6, 102, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5634720785575659, 45, '2024-12-19 15:03:41', 'approved', 2, '2025-05-21 15:03:41', NULL),
(510, 10, 102, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.829233326901899, 20, '2025-02-09 15:03:41', 'approved', 10, '2025-05-22 15:03:41', NULL),
(511, 1, 103, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9243326596211016, 24, '2025-02-22 15:03:41', 'approved', 4, '2025-05-21 15:03:41', NULL),
(512, 7, 103, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9747524022820895, 14, '2025-02-03 15:03:41', 'approved', 6, '2025-05-23 15:03:41', NULL),
(513, 10, 103, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6650862696627958, 23, '2025-04-18 15:03:41', 'approved', 9, '2025-05-18 15:03:41', NULL),
(514, 2, 103, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7888994998381469, 22, '2025-03-14 15:03:41', 'approved', 4, '2025-05-19 15:03:41', NULL),
(515, 2, 103, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8100727136526934, 2, '2025-03-20 15:03:41', 'approved', 8, '2025-05-20 15:03:41', NULL),
(516, 6, 104, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9850820083032195, 44, '2024-12-22 15:03:41', 'approved', 5, '2025-05-17 15:03:41', NULL),
(517, 3, 104, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.38716426281925687, 32, '2025-05-13 15:03:41', 'approved', 4, '2025-05-19 15:03:41', NULL),
(518, 3, 104, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8603447195704512, 44, '2025-02-09 15:03:41', 'approved', 2, '2025-05-22 15:03:41', NULL),
(519, 4, 104, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.4639673097282344, 17, '2025-05-20 15:03:41', 'approved', 8, '2025-05-18 15:03:41', NULL),
(520, 5, 104, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7430780256754514, 17, '2025-02-25 15:03:41', 'approved', 3, '2025-05-17 15:03:41', NULL),
(521, 9, 105, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6471317217565437, 7, '2025-04-15 15:03:41', 'approved', 2, '2025-05-23 15:03:41', NULL),
(522, 3, 105, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6697134466746016, 4, '2025-05-06 15:03:41', 'pending', NULL, NULL, NULL),
(523, 9, 105, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5368311079003206, 40, '2025-03-29 15:03:41', 'approved', 2, '2025-05-17 15:03:41', NULL),
(524, 10, 105, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5846552115163348, 16, '2025-05-07 15:03:41', 'approved', 10, '2025-05-21 15:03:41', NULL),
(525, 9, 105, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7901463096124551, 13, '2025-03-15 15:03:41', 'approved', 7, '2025-05-21 15:03:41', NULL),
(526, 5, 106, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5283684769350742, 7, '2025-04-03 15:03:41', 'approved', 4, '2025-05-18 15:03:41', NULL),
(527, 2, 106, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9631164481519875, 15, '2025-05-18 15:03:41', 'approved', 6, '2025-05-20 15:03:41', NULL),
(528, 7, 106, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7987111159774578, 23, '2024-12-22 15:03:41', 'pending', NULL, NULL, NULL),
(529, 4, 106, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8756658830453324, 2, '2025-03-21 15:03:41', 'approved', 8, '2025-05-20 15:03:41', NULL),
(530, 4, 106, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.562110917830943, 44, '2025-02-13 15:03:41', 'approved', 4, '2025-05-17 15:03:41', NULL),
(531, 7, 107, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9428660366152097, 25, '2025-03-15 15:03:41', 'approved', 8, '2025-05-19 15:03:41', NULL),
(532, 10, 107, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7692883861151416, 29, '2024-12-14 15:03:41', 'approved', 7, '2025-05-20 15:03:41', NULL),
(533, 9, 107, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8347239026378132, 19, '2025-03-30 15:03:41', 'approved', 9, '2025-05-21 15:03:41', NULL),
(534, 3, 107, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9200671174750358, 10, '2025-04-20 15:03:41', 'approved', 10, '2025-05-21 15:03:41', NULL),
(535, 9, 107, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.3294534730626768, 49, '2025-02-18 15:03:41', 'approved', 1, '2025-05-18 15:03:41', NULL),
(536, 8, 108, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8252240832384901, 27, '2025-03-05 15:03:41', 'approved', 5, '2025-05-18 15:03:41', NULL),
(537, 10, 108, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.24012539081287143, 46, '2025-01-06 15:03:41', 'approved', 8, '2025-05-19 15:03:41', NULL),
(538, 1, 108, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8253990983827032, 40, '2025-01-23 15:03:41', 'approved', 9, '2025-05-22 15:03:41', NULL),
(539, 9, 108, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7940789728556564, 40, '2025-03-11 15:03:41', 'approved', 1, '2025-05-23 15:03:41', NULL),
(540, 10, 108, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.752046055227561, 47, '2025-04-08 15:03:41', 'approved', 5, '2025-05-19 15:03:41', NULL),
(541, 1, 109, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6083426258045646, 23, '2025-01-05 15:03:41', 'approved', 1, '2025-05-17 15:03:41', NULL),
(542, 7, 109, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.836414629739164, 22, '2025-03-26 15:03:41', 'approved', 7, '2025-05-22 15:03:41', NULL),
(543, 9, 109, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.820808613319703, 17, '2024-12-02 15:03:41', 'approved', 1, '2025-05-22 15:03:41', NULL),
(544, 1, 109, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9649057916970046, 5, '2025-01-04 15:03:41', 'approved', 2, '2025-05-20 15:03:41', NULL),
(545, 8, 109, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9907947597939473, 14, '2024-12-15 15:03:41', 'approved', 9, '2025-05-19 15:03:41', NULL),
(546, 6, 110, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8258710280301711, 11, '2025-02-04 15:03:41', 'approved', 1, '2025-05-19 15:03:41', NULL),
(547, 1, 110, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7571804118130174, 25, '2025-04-15 15:03:41', 'approved', 1, '2025-05-18 15:03:41', NULL),
(548, 9, 110, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5207095095521859, 20, '2025-02-03 15:03:41', 'approved', 10, '2025-05-18 15:03:41', NULL),
(549, 5, 110, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.667788210686099, 18, '2025-05-05 15:03:41', 'approved', 1, '2025-05-18 15:03:41', NULL),
(550, 9, 110, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.8106488521496289, 29, '2025-03-03 15:03:41', 'approved', 5, '2025-05-18 15:03:41', NULL),
(551, 8, 111, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.4736581107914989, 35, '2025-01-16 15:03:41', 'approved', 8, '2025-05-20 15:03:41', NULL),
(552, 2, 111, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.714540424788874, 24, '2025-05-02 15:03:41', 'approved', 7, '2025-05-23 15:03:41', NULL),
(553, 6, 111, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9878148104882006, 36, '2025-05-16 15:03:41', 'approved', 8, '2025-05-21 15:03:41', NULL),
(554, 10, 111, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6508494512092783, 3, '2025-05-08 15:03:41', 'approved', 6, '2025-05-19 15:03:41', NULL),
(555, 7, 111, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6534283241009604, 42, '2025-04-16 15:03:41', 'approved', 4, '2025-05-21 15:03:41', NULL),
(556, 7, 112, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9316799375523627, 9, '2025-03-10 15:03:41', 'approved', 10, '2025-05-20 15:03:41', NULL),
(557, 6, 112, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7655573830805322, 6, '2025-02-18 15:03:41', 'approved', 1, '2025-05-18 15:03:41', NULL),
(558, 10, 112, 1, 'I am disappointed with this product. It did not meet my expectations at all. I regret this purchase.', 0.2517390289639486, 9, '2024-12-13 15:03:41', 'approved', 3, '2025-05-21 15:03:41', NULL),
(559, 2, 112, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.668498064008074, 45, '2024-12-15 15:03:41', 'approved', 1, '2025-05-18 15:03:41', NULL),
(560, 7, 112, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5754431646461069, 19, '2025-02-11 15:03:41', 'approved', 6, '2025-05-18 15:03:41', NULL),
(561, 6, 113, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8047529225468197, 3, '2025-01-02 15:03:41', 'pending', NULL, NULL, NULL),
(562, 3, 113, 1, 'I am disappointed with this product. The quality is much lower than advertised. Would not recommend.', 0.2176420452982579, 18, '2025-05-05 15:03:41', 'pending', NULL, NULL, NULL),
(563, 1, 113, 1, 'I am disappointed with this product. It did not meet my expectations at all. I regret this purchase.', 0.3379509885031273, 19, '2025-01-07 15:03:41', 'approved', 6, '2025-05-20 15:03:41', NULL),
(564, 8, 113, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7934601134745964, 23, '2025-02-21 15:03:41', 'approved', 3, '2025-05-19 15:03:41', NULL),
(565, 6, 113, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9557086310868232, 1, '2025-02-24 15:03:41', 'approved', 10, '2025-05-20 15:03:41', NULL),
(566, 5, 114, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7432182029478421, 4, '2024-12-04 15:03:41', 'pending', NULL, NULL, NULL),
(567, 3, 114, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6839446006379506, 7, '2025-02-03 15:03:41', 'approved', 5, '2025-05-19 15:03:41', NULL),
(568, 6, 114, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5446681622272992, 3, '2025-02-02 15:03:41', 'approved', 7, '2025-05-20 15:03:41', NULL),
(569, 2, 114, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7747537323224859, 24, '2025-01-26 15:03:41', 'approved', 10, '2025-05-19 15:03:41', NULL),
(570, 5, 114, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6690726726400411, 8, '2025-03-15 15:03:41', 'approved', 8, '2025-05-19 15:03:41', NULL),
(571, 1, 115, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5739685364197646, 16, '2024-11-25 15:03:41', 'approved', 2, '2025-05-20 15:03:41', NULL),
(572, 4, 115, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6704405398950358, 28, '2024-12-23 15:03:41', 'approved', 6, '2025-05-23 15:03:41', NULL),
(573, 9, 115, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.679958895705602, 16, '2024-11-28 15:03:41', 'approved', 6, '2025-05-18 15:03:41', NULL),
(574, 3, 115, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.708129827890666, 25, '2025-04-08 15:03:41', 'approved', 9, '2025-05-20 15:03:41', NULL),
(575, 8, 115, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9517867536766331, 40, '2025-05-15 15:03:41', 'approved', 1, '2025-05-17 15:03:41', NULL),
(576, 4, 116, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5861232683305845, 1, '2025-02-20 15:03:41', 'approved', 9, '2025-05-18 15:03:41', NULL),
(577, 1, 116, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8248710787155397, 29, '2025-05-08 15:03:41', 'approved', 5, '2025-05-23 15:03:41', NULL),
(578, 8, 116, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7609154716887656, 33, '2025-02-08 15:03:41', 'approved', 8, '2025-05-23 15:03:41', NULL),
(579, 9, 116, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8585566567802398, 14, '2025-04-27 15:03:41', 'approved', 9, '2025-05-18 15:03:41', NULL),
(580, 6, 116, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7906222307222172, 49, '2025-01-28 15:03:41', 'approved', 8, '2025-05-19 15:03:41', NULL),
(581, 9, 117, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.915164807173577, 18, '2025-03-14 15:03:41', 'approved', 8, '2025-05-21 15:03:41', NULL),
(582, 6, 117, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7627575474630646, 37, '2024-11-27 15:03:41', 'approved', 3, '2025-05-20 15:03:41', NULL),
(583, 7, 117, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7563974330913252, 12, '2024-12-13 15:03:41', 'approved', 9, '2025-05-18 15:03:41', NULL),
(584, 3, 117, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7496852502875824, 36, '2025-05-02 15:03:41', 'approved', 5, '2025-05-17 15:03:41', NULL),
(585, 10, 117, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9950760448305644, 8, '2025-03-20 15:03:41', 'pending', NULL, NULL, NULL),
(586, 8, 118, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7533674387944559, 24, '2025-04-21 15:03:41', 'approved', 1, '2025-05-19 15:03:41', NULL),
(587, 4, 118, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.370723121176216, 11, '2025-01-19 15:03:41', 'approved', 6, '2025-05-17 15:03:41', NULL),
(588, 7, 118, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9888306686550665, 5, '2025-04-25 15:03:41', 'approved', 6, '2025-05-23 15:03:41', NULL),
(589, 8, 118, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8215557952798491, 6, '2024-12-06 15:03:41', 'pending', NULL, NULL, NULL),
(590, 9, 118, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5653173780118277, 31, '2025-04-09 15:03:41', 'approved', 5, '2025-05-21 15:03:41', NULL),
(591, 7, 119, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.811568336199567, 19, '2025-01-30 15:03:41', 'approved', 10, '2025-05-17 15:03:41', NULL),
(592, 5, 119, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.5179220882038792, 47, '2025-03-15 15:03:41', 'approved', 10, '2025-05-21 15:03:41', NULL),
(593, 4, 119, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7230263423575334, 44, '2025-04-07 15:03:41', 'approved', 1, '2025-05-20 15:03:41', NULL),
(594, 4, 119, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6554661290491616, 49, '2025-03-15 15:03:41', 'approved', 4, '2025-05-20 15:03:41', NULL),
(595, 5, 119, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7543008274159402, 29, '2025-02-18 15:03:41', 'approved', 5, '2025-05-18 15:03:41', NULL),
(596, 6, 120, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.522370615007701, 18, '2025-04-25 15:03:41', 'approved', 8, '2025-05-22 15:03:41', NULL),
(597, 1, 120, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7436282372694726, 7, '2025-02-01 15:03:41', 'approved', 7, '2025-05-20 15:03:41', NULL),
(598, 7, 120, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5641409024634779, 19, '2025-01-02 15:03:41', 'approved', 1, '2025-05-17 15:03:41', NULL),
(599, 4, 120, 1, 'I am disappointed with this product. The quality is much lower than advertised. Would not recommend.', 0.18234841251964534, 23, '2025-02-24 15:03:41', 'approved', 3, '2025-05-18 15:03:41', NULL),
(600, 4, 120, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5697157632650023, 42, '2025-03-06 15:03:41', 'approved', 10, '2025-05-19 15:03:41', NULL),
(601, 5, 121, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7249223607079335, 19, '2025-03-27 15:03:41', 'approved', 9, '2025-05-22 15:03:41', NULL),
(602, 9, 121, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9136667615861322, 48, '2024-11-28 15:03:41', 'approved', 2, '2025-05-19 15:03:41', NULL),
(603, 2, 121, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7977746763432163, 39, '2025-02-15 15:03:41', 'approved', 6, '2025-05-21 15:03:41', NULL),
(604, 7, 121, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8475421235408096, 9, '2025-01-02 15:03:41', 'approved', 2, '2025-05-20 15:03:41', NULL),
(605, 1, 121, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7470222073300017, 7, '2025-03-13 15:03:41', 'approved', 6, '2025-05-18 15:03:41', NULL),
(606, 10, 122, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8395826555412101, 47, '2025-01-01 15:03:41', 'approved', 7, '2025-05-17 15:03:41', NULL),
(607, 5, 122, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.5109457765435295, 28, '2025-04-24 15:03:41', 'pending', NULL, NULL, NULL),
(608, 1, 122, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6712146773852545, 22, '2025-04-28 15:03:41', 'approved', 9, '2025-05-17 15:03:41', NULL),
(609, 1, 122, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8056270537950351, 0, '2025-04-04 15:03:41', 'approved', 9, '2025-05-20 15:03:41', NULL),
(610, 1, 122, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8493445612949735, 38, '2025-02-02 15:03:41', 'approved', 7, '2025-05-20 15:03:41', NULL),
(611, 8, 123, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9531807496707707, 44, '2025-01-26 15:03:41', 'approved', 4, '2025-05-23 15:03:41', NULL),
(612, 10, 123, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.728937163249531, 2, '2025-04-10 15:03:41', 'pending', NULL, NULL, NULL),
(613, 7, 123, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8556796405982968, 15, '2025-03-07 15:03:41', 'approved', 8, '2025-05-21 15:03:41', NULL),
(614, 5, 123, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6830282758949588, 42, '2025-03-01 15:03:41', 'approved', 9, '2025-05-17 15:03:41', NULL),
(615, 4, 123, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6928014978885665, 34, '2025-02-20 15:03:41', 'approved', 5, '2025-05-20 15:03:41', NULL),
(616, 4, 124, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.3801938805172163, 16, '2024-12-24 15:03:41', 'approved', 2, '2025-05-20 15:03:41', NULL),
(617, 7, 124, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6828228622701232, 39, '2025-02-04 15:03:41', 'approved', 9, '2025-05-19 15:03:41', NULL),
(618, 7, 124, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8794640519464986, 4, '2025-02-27 15:03:41', 'approved', 2, '2025-05-21 15:03:41', NULL),
(619, 5, 124, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9796362539563667, 32, '2024-12-09 15:03:41', 'approved', 1, '2025-05-20 15:03:41', NULL),
(620, 6, 124, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.28791581091276913, 2, '2025-05-23 15:03:41', 'approved', 6, '2025-05-20 15:03:41', NULL),
(621, 2, 125, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9568859330908265, 21, '2025-05-08 15:03:41', 'approved', 7, '2025-05-23 15:03:41', NULL),
(622, 4, 125, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7043634113151239, 22, '2025-01-20 15:03:41', 'approved', 3, '2025-05-22 15:03:41', NULL),
(623, 6, 125, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.44878557753636095, 46, '2024-11-27 15:03:41', 'approved', 1, '2025-05-21 15:03:41', NULL),
(624, 6, 125, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7410821084501986, 6, '2024-12-17 15:03:41', 'approved', 3, '2025-05-19 15:03:41', NULL),
(625, 6, 125, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7220904810746112, 45, '2025-02-28 15:03:41', 'approved', 9, '2025-05-17 15:03:41', NULL),
(626, 2, 126, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9021939427612293, 26, '2025-04-20 15:03:41', 'approved', 4, '2025-05-23 15:03:41', NULL),
(627, 3, 126, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.47251283771592456, 32, '2025-01-19 15:03:41', 'pending', NULL, NULL, NULL),
(628, 10, 126, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9654552388614559, 49, '2025-01-04 15:03:41', 'approved', 2, '2025-05-20 15:03:41', NULL),
(629, 10, 126, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.3307864873584234, 6, '2025-04-28 15:03:41', 'approved', 8, '2025-05-20 15:03:41', NULL),
(630, 3, 126, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9421815921945327, 9, '2025-03-25 15:03:41', 'approved', 2, '2025-05-20 15:03:41', NULL),
(631, 3, 127, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.597759432008266, 41, '2025-02-28 15:03:41', 'approved', 3, '2025-05-19 15:03:41', NULL),
(632, 5, 127, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5056326047942346, 49, '2025-02-02 15:03:41', 'approved', 10, '2025-05-18 15:03:41', NULL),
(633, 3, 127, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5422336920930386, 1, '2024-11-26 15:03:41', 'approved', 9, '2025-05-22 15:03:41', NULL),
(634, 3, 127, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7421606397835172, 29, '2025-04-07 15:03:41', 'approved', 3, '2025-05-21 15:03:41', NULL),
(635, 1, 127, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9979644570480701, 48, '2025-03-30 15:03:41', 'pending', NULL, NULL, NULL),
(636, 10, 128, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.6549500854452607, 40, '2025-03-13 15:03:41', 'approved', 7, '2025-05-23 15:03:41', NULL),
(637, 4, 128, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6455013855970477, 46, '2025-04-29 15:03:41', 'approved', 6, '2025-05-21 15:03:41', NULL),
(638, 2, 128, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6854739006659705, 16, '2024-12-12 15:03:41', 'approved', 6, '2025-05-22 15:03:41', NULL),
(639, 3, 128, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6614791679209835, 26, '2025-03-07 15:03:41', 'approved', 4, '2025-05-20 15:03:41', NULL),
(640, 7, 128, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8301937538666593, 40, '2025-04-02 15:03:41', 'approved', 2, '2025-05-17 15:03:41', NULL),
(641, 1, 129, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8790402696272734, 22, '2025-01-08 15:03:41', 'approved', 4, '2025-05-21 15:03:41', NULL),
(642, 6, 129, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8087869347341237, 35, '2025-04-29 15:03:41', 'approved', 3, '2025-05-18 15:03:41', NULL),
(643, 5, 129, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6940978279468584, 9, '2024-12-24 15:03:41', 'approved', 4, '2025-05-22 15:03:41', NULL),
(644, 2, 129, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9815353384069496, 0, '2025-02-26 15:03:41', 'approved', 6, '2025-05-22 15:03:41', NULL),
(645, 5, 129, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9944838885166533, 44, '2025-01-25 15:03:41', 'approved', 1, '2025-05-22 15:03:41', NULL),
(646, 8, 130, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8231201444036516, 5, '2025-05-14 15:03:41', 'approved', 1, '2025-05-21 15:03:41', NULL),
(647, 4, 130, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.801257901937941, 12, '2025-03-12 15:03:41', 'approved', 8, '2025-05-20 15:03:41', NULL),
(648, 4, 130, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.3950408394961067, 32, '2025-04-09 15:03:41', 'approved', 10, '2025-05-23 15:03:41', NULL),
(649, 3, 130, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.765952844625295, 35, '2025-02-22 15:03:41', 'approved', 4, '2025-05-22 15:03:41', NULL),
(650, 10, 130, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.6739460177663211, 4, '2025-02-07 15:03:41', 'pending', NULL, NULL, NULL),
(651, 7, 131, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9938700331317913, 29, '2025-01-31 15:03:41', 'approved', 6, '2025-05-23 15:03:41', NULL),
(652, 6, 131, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8830319045884832, 10, '2024-12-09 15:03:41', 'approved', 2, '2025-05-18 15:03:41', NULL),
(653, 6, 131, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.5024775558546908, 22, '2024-12-21 15:03:41', 'approved', 1, '2025-05-17 15:03:41', NULL),
(654, 2, 131, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8327745100043477, 46, '2025-03-13 15:03:41', 'approved', 10, '2025-05-19 15:03:41', NULL),
(655, 2, 131, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8484095046747565, 19, '2025-01-19 15:03:41', 'approved', 2, '2025-05-17 15:03:41', NULL),
(656, 1, 132, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.49611963988851726, 14, '2025-03-03 15:03:41', 'approved', 8, '2025-05-22 15:03:41', NULL),
(657, 9, 132, 2, 'This product has some issues. The value for the price is questionable. Think twice before buying.', 0.46378258431682606, 23, '2025-05-18 15:03:41', 'approved', 1, '2025-05-22 15:03:41', NULL),
(658, 9, 132, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5695222402080169, 6, '2025-02-11 15:03:41', 'approved', 3, '2025-05-21 15:03:41', NULL),
(659, 1, 132, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8705830818699516, 42, '2025-04-08 15:03:41', 'approved', 7, '2025-05-20 15:03:41', NULL),
(660, 8, 132, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.5215798930838517, 24, '2025-03-17 15:03:41', 'approved', 2, '2025-05-19 15:03:41', NULL),
(661, 6, 133, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9867411306004423, 41, '2025-04-09 15:03:41', 'approved', 4, '2025-05-17 15:03:41', NULL),
(662, 7, 133, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5175384059152923, 15, '2025-05-21 15:03:41', 'approved', 3, '2025-05-20 15:03:41', NULL),
(663, 4, 133, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6904688226156578, 31, '2025-04-06 15:03:41', 'approved', 1, '2025-05-19 15:03:41', NULL),
(664, 9, 133, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7173245326032159, 11, '2025-03-24 15:03:41', 'approved', 7, '2025-05-21 15:03:41', NULL),
(665, 5, 133, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8205570589942458, 20, '2025-01-11 15:03:41', 'approved', 10, '2025-05-20 15:03:41', NULL),
(666, 8, 134, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.599255910179816, 12, '2025-02-23 15:03:41', 'approved', 5, '2025-05-17 15:03:41', NULL),
(667, 1, 134, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6916765945140986, 26, '2025-03-14 15:03:41', 'approved', 10, '2025-05-19 15:03:41', NULL),
(668, 7, 134, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.42004224954176905, 10, '2025-03-30 15:03:41', 'approved', 8, '2025-05-18 15:03:41', NULL),
(669, 5, 134, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6661798666288888, 16, '2024-12-07 15:03:41', 'approved', 2, '2025-05-18 15:03:41', NULL),
(670, 8, 134, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.6346759504402764, 12, '2025-01-15 15:03:41', 'pending', NULL, NULL, NULL),
(671, 9, 135, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6211012000787083, 15, '2025-03-08 15:03:41', 'approved', 8, '2025-05-20 15:03:41', NULL),
(672, 3, 135, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8660914127399134, 37, '2025-03-11 15:03:41', 'approved', 9, '2025-05-17 15:03:41', NULL),
(673, 3, 135, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8172379326235857, 1, '2025-01-30 15:03:41', 'pending', NULL, NULL, NULL),
(674, 5, 135, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7679944834560105, 37, '2025-04-21 15:03:41', 'approved', 6, '2025-05-20 15:03:41', NULL),
(675, 4, 135, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9844795305137333, 2, '2025-02-23 15:03:41', 'approved', 8, '2025-05-23 15:03:41', NULL),
(676, 1, 136, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5909269839813253, 15, '2025-01-14 15:03:41', 'approved', 8, '2025-05-19 15:03:41', NULL),
(677, 10, 136, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9635798316109738, 12, '2024-12-05 15:03:41', 'approved', 5, '2025-05-20 15:03:41', NULL),
(678, 10, 136, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5230191245330675, 36, '2025-02-03 15:03:41', 'pending', NULL, NULL, NULL),
(679, 9, 136, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8588533258613696, 46, '2025-01-11 15:03:41', 'approved', 6, '2025-05-21 15:03:41', NULL),
(680, 6, 136, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.9248680805087724, 5, '2024-12-09 15:03:41', 'approved', 7, '2025-05-20 15:03:41', NULL),
(681, 7, 137, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7064682228737215, 47, '2025-04-26 15:03:41', 'approved', 2, '2025-05-20 15:03:41', NULL),
(682, 10, 137, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.5054783080383002, 28, '2024-12-08 15:03:41', 'approved', 2, '2025-05-18 15:03:41', NULL),
(683, 8, 137, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8224076983913851, 20, '2025-05-10 15:03:41', 'approved', 7, '2025-05-22 15:03:41', NULL),
(684, 9, 137, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7169447351591147, 9, '2025-03-22 15:03:41', 'approved', 5, '2025-05-21 15:03:41', NULL),
(685, 2, 137, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.3877086121101944, 33, '2025-01-04 15:03:41', 'approved', 6, '2025-05-19 15:03:41', NULL),
(686, 4, 138, 1, 'I am disappointed with this product. The quality is much lower than advertised. Would not recommend.', 0.35717292878513496, 42, '2025-03-29 15:03:41', 'approved', 2, '2025-05-18 15:03:41', NULL),
(687, 8, 138, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.17431530044815996, 12, '2025-03-15 15:03:41', 'pending', NULL, NULL, NULL),
(688, 4, 138, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8360163937844489, 12, '2025-02-01 15:03:41', 'approved', 4, '2025-05-18 15:03:41', NULL),
(689, 1, 138, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5366076145103272, 2, '2025-05-22 15:03:41', 'approved', 5, '2025-05-22 15:03:41', NULL),
(690, 10, 138, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.871122334963756, 36, '2025-04-18 15:03:41', 'approved', 5, '2025-05-19 15:03:41', NULL),
(691, 1, 139, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7629201228571312, 1, '2024-12-23 15:03:41', 'approved', 8, '2025-05-22 15:03:41', NULL),
(692, 8, 139, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7529537348756136, 29, '2025-04-30 15:03:41', 'approved', 8, '2025-05-20 15:03:41', NULL),
(693, 2, 139, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9433913422221238, 36, '2025-01-26 15:03:41', 'approved', 8, '2025-05-23 15:03:41', NULL),
(694, 8, 139, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5123021656203104, 15, '2025-01-19 15:03:41', 'approved', 10, '2025-05-20 15:03:41', NULL),
(695, 1, 139, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.8992613629431104, 11, '2025-02-01 15:03:41', 'approved', 1, '2025-05-21 15:03:41', NULL),
(696, 6, 140, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.957217237872274, 42, '2024-12-17 15:03:41', 'approved', 7, '2025-05-18 15:03:41', NULL),
(697, 1, 140, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.622668629384291, 44, '2025-03-01 15:03:41', 'approved', 2, '2025-05-20 15:03:41', NULL),
(698, 9, 140, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.8270133899031332, 29, '2025-04-14 15:03:41', 'approved', 5, '2025-05-20 15:03:41', NULL),
(699, 1, 140, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9935975424885728, 24, '2025-02-03 15:03:41', 'approved', 8, '2025-05-23 15:03:41', NULL),
(700, 3, 140, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.548129993852349, 4, '2025-02-08 15:03:41', 'approved', 1, '2025-05-21 15:03:41', NULL),
(701, 5, 141, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.4826585709328377, 17, '2025-02-12 15:03:41', 'approved', 3, '2025-05-20 15:03:41', NULL),
(702, 7, 141, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.8233717128107061, 47, '2025-02-18 15:03:41', 'pending', NULL, NULL, NULL),
(703, 4, 141, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.8313049226545774, 28, '2025-02-11 15:03:41', 'pending', NULL, NULL, NULL),
(704, 8, 141, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.8298057773614356, 14, '2025-04-24 15:03:41', 'approved', 4, '2025-05-21 15:03:41', NULL),
(705, 7, 141, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8923230883593887, 23, '2025-02-04 15:03:41', 'pending', NULL, NULL, NULL),
(706, 1, 142, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7349163184453885, 33, '2024-12-09 15:03:41', 'pending', NULL, NULL, NULL),
(707, 6, 142, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.23202651265266028, 39, '2025-05-10 15:03:41', 'approved', 2, '2025-05-21 15:03:41', NULL),
(708, 2, 142, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.28619751573186136, 1, '2024-12-14 15:03:41', 'approved', 5, '2025-05-20 15:03:41', NULL),
(709, 2, 142, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9465780211115051, 38, '2025-04-04 15:03:41', 'approved', 3, '2025-05-20 15:03:41', NULL),
(710, 10, 142, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.6669960789261349, 10, '2025-02-16 15:03:41', 'approved', 2, '2025-05-22 15:03:41', NULL),
(711, 7, 143, 2, 'This product has some issues. It works but has several drawbacks. There are better alternatives available.', 0.3243869227211801, 1, '2025-03-13 15:03:41', 'approved', 9, '2025-05-17 15:03:41', NULL),
(712, 2, 143, 1, 'I am disappointed with this product. It did not meet my expectations at all. Would not recommend.', 0.2784311828747682, 46, '2025-02-09 15:03:41', 'approved', 9, '2025-05-19 15:03:41', NULL),
(713, 6, 143, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5555455608251929, 38, '2025-01-27 15:03:41', 'approved', 7, '2025-05-22 15:03:41', NULL),
(714, 2, 143, 1, 'I am disappointed with this product. It did not meet my expectations at all. I regret this purchase.', 0.3241868456864607, 48, '2025-05-03 15:03:41', 'approved', 1, '2025-05-18 15:03:41', NULL),
(715, 1, 143, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.5415245344690276, 17, '2025-02-16 15:03:41', 'approved', 9, '2025-05-17 15:03:41', NULL),
(716, 7, 144, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7469059939188007, 9, '2025-04-24 15:03:41', 'approved', 1, '2025-05-19 15:03:41', NULL),
(717, 5, 144, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.8109964479980958, 8, '2025-01-19 15:03:41', 'approved', 8, '2025-05-20 15:03:41', NULL),
(718, 4, 144, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.6761556500533182, 4, '2025-03-13 15:03:41', 'approved', 5, '2025-05-23 15:03:41', NULL),
(719, 1, 144, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8347449807773764, 43, '2025-01-16 15:03:41', 'approved', 4, '2025-05-20 15:03:41', NULL),
(720, 4, 144, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7143540934048166, 19, '2025-02-14 15:03:41', 'approved', 9, '2025-05-18 15:03:41', NULL),
(721, 6, 145, 1, 'I am disappointed with this product. The quality is much lower than advertised. I regret this purchase.', 0.2786726655053652, 44, '2025-03-06 15:03:41', 'approved', 3, '2025-05-17 15:03:41', NULL),
(722, 1, 145, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9270593614569487, 35, '2025-04-29 15:03:41', 'approved', 7, '2025-05-18 15:03:41', NULL),
(723, 3, 145, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8804459818456751, 43, '2025-03-03 15:03:41', 'approved', 8, '2025-05-20 15:03:41', NULL),
(724, 2, 145, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.390983894421704, 30, '2025-04-07 15:03:41', 'pending', NULL, NULL, NULL),
(725, 7, 145, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.9961033392661283, 38, '2025-02-12 15:03:41', 'approved', 3, '2025-05-20 15:03:41', NULL),
(726, 6, 146, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.817254113049483, 2, '2024-12-24 15:03:41', 'pending', NULL, NULL, NULL),
(727, 3, 146, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.3370076549211588, 9, '2025-03-03 15:03:41', 'approved', 3, '2025-05-17 15:03:41', NULL),
(728, 2, 146, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.947140646304135, 16, '2024-12-01 15:03:41', 'approved', 9, '2025-05-20 15:03:41', NULL),
(729, 10, 146, 3, 'This product is okay. Average quality for the price. Satisfactory but not impressive.', 0.6642315552609335, 46, '2025-02-02 15:03:41', 'approved', 9, '2025-05-20 15:03:41', NULL),
(730, 1, 146, 2, 'This product has some issues. It works but has several drawbacks. Think twice before buying.', 0.39390044468818275, 33, '2025-05-18 15:03:41', 'approved', 2, '2025-05-19 15:03:41', NULL),
(731, 8, 147, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6787085722374809, 27, '2024-12-26 15:03:41', 'approved', 7, '2025-05-17 15:03:41', NULL),
(732, 5, 147, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.8673866212995599, 42, '2025-03-15 15:03:41', 'approved', 9, '2025-05-22 15:03:41', NULL),
(733, 4, 147, 4, 'I like this product. Good quality and value for money. Very satisfied with my purchase.', 0.7178508390093696, 28, '2025-02-21 15:03:41', 'approved', 1, '2025-05-19 15:03:41', NULL),
(734, 2, 147, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.9407005752815871, 20, '2025-01-26 15:03:41', 'pending', NULL, NULL, NULL),
(735, 3, 147, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.8338372206630532, 43, '2025-04-10 15:03:41', 'approved', 5, '2025-05-21 15:03:41', NULL),
(736, 5, 148, 5, 'Excellent product! Exceeded my expectations in every way. One of the best purchases I have made.', 0.836076415922564, 48, '2025-03-16 15:03:41', 'approved', 3, '2025-05-17 15:03:41', NULL),
(737, 3, 148, 4, 'I like this product. Good quality and value for money. Would recommend to friends.', 0.7798870629629931, 45, '2025-02-14 15:03:41', 'approved', 3, '2025-05-18 15:03:41', NULL),
(738, 3, 148, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.5104312634751492, 31, '2025-03-16 15:03:41', 'approved', 6, '2025-05-20 15:03:41', NULL),
(739, 8, 148, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.7988295572985239, 21, '2025-02-01 15:03:41', 'approved', 6, '2025-05-18 15:03:41', NULL),
(740, 2, 148, 5, 'Excellent product! Perfect quality and amazing value. Absolutely recommend to everyone!', 0.9579355854149308, 26, '2025-01-16 15:03:41', 'approved', 9, '2025-05-23 15:03:41', NULL),
(741, 1, 149, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6440906247907231, 25, '2024-11-25 15:03:41', 'approved', 9, '2025-05-23 15:03:41', NULL),
(742, 2, 149, 5, 'Excellent product! Exceeded my expectations in every way. Absolutely recommend to everyone!', 0.8300237681996299, 41, '2024-12-24 15:03:41', 'approved', 6, '2025-05-22 15:03:41', NULL);
INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `rating`, `comment`, `sentiment_score`, `helpful_count`, `created_at`, `moderation_status`, `moderated_by`, `moderated_at`, `deleted_at`) VALUES
(743, 7, 149, 5, 'Excellent product! Perfect quality and amazing value. One of the best purchases I have made.', 0.956368838396267, 46, '2025-02-25 15:03:41', 'approved', 6, '2025-05-22 15:03:41', NULL),
(744, 4, 149, 4, 'I like this product. It has great features and performs well. Very satisfied with my purchase.', 0.832938729927818, 32, '2025-03-05 15:03:41', 'approved', 5, '2025-05-22 15:03:41', NULL),
(745, 4, 149, 3, 'This product is okay. It does what it promises but nothing exceptional. Satisfactory but not impressive.', 0.5587055886152252, 30, '2024-12-16 15:03:42', 'approved', 5, '2025-05-18 15:03:42', NULL),
(746, 4, 150, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7190779865151997, 9, '2025-04-15 15:03:42', 'approved', 4, '2025-05-22 15:03:42', NULL),
(747, 2, 150, 4, 'I like this product. It has great features and performs well. Would recommend to friends.', 0.7065762169906649, 6, '2024-12-31 15:03:42', 'approved', 5, '2025-05-19 15:03:42', NULL),
(748, 1, 150, 2, 'This product has some issues. The value for the price is questionable. There are better alternatives available.', 0.3921151613370657, 39, '2025-02-22 15:03:42', 'approved', 1, '2025-05-19 15:03:42', NULL),
(749, 4, 150, 3, 'This product is okay. Average quality for the price. Might buy again if improved.', 0.6526761646314302, 8, '2025-04-08 15:03:42', 'approved', 10, '2025-05-18 15:03:42', NULL),
(750, 4, 150, 3, 'This product is okay. It does what it promises but nothing exceptional. Might buy again if improved.', 0.6709657707353736, 25, '2024-12-05 15:03:42', 'approved', 10, '2025-05-23 15:03:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_general_ci,
  `payload` longtext COLLATE utf8mb4_general_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('37qeUxfdI9nTyEruMUyHI7SDyvfmYhGrmmjRmorm', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoidW5oZ1ZaMkRBMlZ4MkFkbjNybVkwelZyaHlBOGJIZlc3T0hNU25vNSI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmZjY2YzZGM0NiI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAxMToxOTo0MyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748172001),
('4SjG10ysQCk5RpFoTyUplla7fIyW4ViV3vAwwtYK', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiM1NxOTN6TzRNMHduTkZCdm9iTERtb0FQdmxGNTlFZTJBNHRwNTZxRyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748167034),
('8bHE3hwTTXaUaE4EgnlMXBTBgVcOzpYyifwneQdM', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiMnl6d3pUMjhITXN4TTJDMFdXdEx4UkpGRUMwcEhNQ1FQT3RMTG9RQyI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmZjY2YzZGM0NiI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAxMToxOTo0MyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjI4OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvb3JkZXJzIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjI6IjgxIjt9fQ==', 1748172429),
('91eBxAg6GccaHbdcsslXmW3xzn9qAf9UKz7KcBzf', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.100.2 Chrome/132.0.6834.210 Electron/34.5.1 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoic1R6YmlGUVpUbkRjUWp4QlN1b3hMblhka0ZnZFpOZndBS0hnTlgwTCI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmU1MTEzYmM5MSI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOTozODoyNSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1748165905),
('9S7okNtr273hLtoWnT5qTPAPUsGcjkNOn7hNAKxQ', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiWVAxSkg4UGhWMFBkNlRHRnVRZU9aMU82ZHcyTGo0U0s0T2VWTWNrSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjM6IjEzMiI7fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1748171609),
('byPOqsiKOXiD7IGGOw4yKW3G624ZAoANwclSbrsy', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiajF0U1Q1REZvRkxVQThIRUo3b2pvY2k0bHViYWxhb0pEQ2doSkhCciI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmZjY2YzZGM0NiI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAxMToxOTo0MyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvYWRtaW4vZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748172129),
('cLNZNMoGPRfuEPcixfqa5twwAjuk4hPeupqbewQD', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiQUVqWU9SeVZpWThPdjBONDZVMUl4aExVbTZxVWZsZVZodDZTUG41UiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjM6IjEzMiI7fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1748167578),
('DmQQupDpgrXPTIyDz1crk0yTLyq7WtVcSeldbBgH', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiQ2dTOUZucHJIVU93cVNYc2t0TmpIVHM3RjVmejFPVnA0emtxbGV3aSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjM6IjEzMiI7fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1748170439),
('DToiVw6VzJ5haOSb7syjsXep4Z4E5dxtRVF0FPsW', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiS1JGRmZseGJxaWw5NEtTOUUxUHI1YXhTRzdkMEFLa0c2SUJoNDUyTiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjM6IjEzMiI7fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1748168920),
('F77qbsfW6fnLLa0iT03EaPwPHXoptMVQ6CMPjhYo', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiWGxsaDZkOE8wZUhKSXBEdVZTaWk2QVlQUGNBbmh1RTJLTFNLYTZFWSI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmZjY2YzZGM0NiI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAxMToxOTo0MyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748172018),
('FclOSwhGmmigk1XcmbFFGA0EzYfRZXPAxTZuHF1w', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.100.2 Chrome/132.0.6834.210 Electron/34.5.1 Safari/537.36', 'YTo1OntzOjY6Il90b2tlbiI7czo0MDoiZDFCdTRJU1VSczlWVFJsaVhGYnVOeVZocnVjOGJMbkdWbTk2QndwOCI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmU2MzE0ZWVlNSI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOTo0MzoxMyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly9sb2NhbGhvc3Q6ODAwMC9hZG1pbi9sb2dpbiI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=', 1748166193),
('fvGV7MTpdk6cjeRKmGAXUK1Q91n725KRjr5skhRx', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiQmRpU0tTS0dyNWY5NmJqVkJrUEI3SXU5dThaSzlDSlJHMGtLVTVkTyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748166780),
('ha7H8d2UUlaBGHuhnKpYrkQYzUF4ESJNJVDvlZ7g', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoibFA0aWt4cENHSEhoa2ZPalNKak15a29RT1lZaGVkS1ZQY3pDU0RZbyI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmZjY2YzZGM0NiI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAxMToxOTo0MyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvYWRtaW4vZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748172068),
('hVD5MaEP9o2BdNOyZe1equfhIfGPWiJJCHZH48fN', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoib09FcW5LbEtKdGVKNzVPU1BjN0ZaQnQxeklOVFZLQUJPU1ZiNmVjWSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjM6IjEzMiI7fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1748167221),
('hyMszeqPyMEwkxN6GT6q0hdATTJ0mccTS9lPx1Jt', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.100.2 Chrome/132.0.6834.210 Electron/34.5.1 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiY21jbGZFbWRpRWtURlY3ZUg5MVlUMWQ1UFQ2TWZJVVZLMDB6TUgzQyI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmU2MzEwZTczMiI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOTo0MzoxMyI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjExMDoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL2Rhc2hib2FyZD9pZD0zZWExYTY4ZS1jNTVkLTQ3N2UtODJkMy04MzAwYmI0YzU5OGImdnNjb2RlQnJvd3NlclJlcUlkPTE3NDgxNjYxOTI3MDgiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1748166193),
('lKw7kay3wxyJrrPPE2N3PwwdVanR5sbYEinfqYoH', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiS1pIMnJDUUJyZGR6dXRRUW14aGgxNWl1cFpFOFU2dmFYT2w2RGU4SCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMSI7fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMTtzOjE1OiJyZWNlbnRseV92aWV3ZWQiO2E6MTp7aTowO3M6MzoiMTMyIjt9czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748171574),
('LttM2m0fmuYLaWqogPnrPjNliOeCHddSUoK3xzjE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoialBGNDViQXFKYjljbkYxVk5TNkdLYmxadzNRVFpIblhoWHhJd1JBZiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9zZXR0aW5ncyI7fXM6MzoidXJsIjthOjA6e31zOjUyOiJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1748165429),
('M5zLCLUhrgj1XDUNHzaiT2DopJmBcAowHU7Mci1l', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoia1o2aHFlZTNzWGxXZ29Qd3RLSHc2QnRLZWxud1BBRkNoWVVESnBENCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjM6IjEzMiI7fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1748170427),
('NlOIsj6HbYJKONmq2Jh0ld1djaPYiQbe6AX14IJs', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiV0wwd1JPWEFCNk1melQ1Wjhucmx3eUdMVkRpWlJoaXA1QUV5cENUNyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjUyOiJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1748165312),
('nxZ5zgkFEQtMwE8iKJJ8wkajwFJqVgk4QDVE33BU', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiYWhGbnRhM1F3cHRSdmN1VEJObFZHUmlyY1hVcTV2VHhjYk92Q1dKZCI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmZjYzQzNDFlYiI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAxMToxOTozMiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMzOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vbG9naW4iO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUyOiJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7czoxNjoiYWRtaW5fbG9naW5fcG9ydCI7aTo4MDAwO30=', 1748171983),
('OgwDNdrbyWpVrvrPo4hLYQK69qG3w34gbSOuc21t', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoicnFQRnlsSXFZNGh4M3gwUU9XREtzNG1NSG5lb0UyTm53MHQzY0tvUCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjM6IjEzMiI7fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1748167753),
('OkLzTtodrwReLILs5hQ8I65cceqQgSYG8ZumyrM1', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo4OntzOjY6Il90b2tlbiI7czo0MDoiam1TOFQ4ejFrMjloYVhQaHdLaWVEeVk3aGFEWDZXRDZuZmQ2WXVEYSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748166638),
('QdcymNUFhfBQWnEYR7hSss8itZ0v1z0t1RlyvAGi', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiQzlkMUJZbk84N2FTNTJDZTJGa2taTGluMG1OUERQSXZmbU0zUFFUMCI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjM6IjEzMiI7fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1748168733),
('qYoDqXEqFavMiv0yRxUcpxyCnvrbMwBIBopwSxDL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiaENTSFNJTjdsRGdENlBmWXhwa0FLU0hGQzg0QjhEdEF0NEJ2ODVWRCI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmZkOTA5MTcyYSI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAxMToyMjo1NiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YTowOnt9czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748172201),
('R9I5U5k1VMn8SkehJqkvgZweo5we6T0ZshQblHTO', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoieWdRWTRUam02WXg0ZUtCMWRCMUtaMmx3bTdxOHIydERHZDlrR1VCbSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9vcmRlcnM/cGFnZT0xIjt9czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNToiaHR0cDovLzEyNy4wLjAuMTo4MDAwL29yZGVycz9wYWdlPTEiO31zOjUyOiJsb2dpbl9hZG1pbl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjE7fQ==', 1748166167),
('RDa5Ao7ghO1IKVHpeXzD8aFlNCexSfMxmTdVpgUB', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiYnJadGsyY0ZEaXBMdFlyM2VyR1ltQTl5cGIwSEVuT2lqWkNNM2JhSSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjM6IjEzMiI7fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1748167115),
('trEQjsrukYp2mBE2aruE4Qx5nO3lUQNDjW1hVdl0', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiUWlBVk91WFhZcFhtdUVvNXo3SW55UzFUWld3aFNUWFFiYUk2a2lHMyI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmZjY2YzZGM0NiI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAxMToxOTo0MyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM3OiJodHRwOi8vMTI3LjAuMC4xOjgwMDEvYWRtaW4vZGFzaGJvYXJkIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748172082),
('VngjnpKlJBNRL3KHqMY821B7OagM3KoTUfYxxdfE', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YToxMDp7czo2OiJfdG9rZW4iO3M6NDA6ImZ3WGxWUzdVNzg0eFRKbk9aZHoxalFOUm5aeEJUaThGUzNEOVc2NkUiO3M6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6ODoiZ3Vlc3RfaWQiO3M6MTk6Imd1ZXN0XzY4MzJlMWI5M2EzMDAiO3M6MTY6Imd1ZXN0X2NyZWF0ZWRfYXQiO3M6MTk6IjIwMjUtMDUtMjUgMDk6MjQ6MDkiO3M6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMzOiJodHRwOi8vMTI3LjAuMC4xOjgwMDAvYWRtaW4vbG9naW4iO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjM6IjEzMiI7fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTtzOjE2OiJhZG1pbl9sb2dpbl9wb3J0IjtpOjgwMDE7fQ==', 1748171972),
('VzNs93RdwCvm9DjH2w4GiQQ79UMsEzGhQjiBVboQ', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.100.2 Chrome/132.0.6834.210 Electron/34.5.1 Safari/537.36', 'YTo2OntzOjY6Il90b2tlbiI7czo0MDoiN0NNQkhxdzRKU3NvRFd2bUJPbzM2NEkwOTRFRFBwdnZKOUNqTzBWTiI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmU1MTEwMDdiMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOTozODoyNSI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czozNzoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL2Rhc2hib2FyZCI7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjExMDoiaHR0cDovL2xvY2FsaG9zdDo4MDAwL2FkbWluL2Rhc2hib2FyZD9pZD01MGI2ZWUyNy0yYzM3LTRjYzYtYjk3NC1hYjRiZjFiNDQzMDQmdnNjb2RlQnJvd3NlclJlcUlkPTE3NDgxNjU5MDQ2MzAiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19', 1748165905),
('WoZLaGZ4dp1N4rVJOBSHfdFSbyvJFte1IGj6FTvS', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiRHFuRndieEVBaFJCZldIV2ZOUjd0bnZMV1BsdWZpQ0VkNUVYZXUxSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9zZXR0aW5ncyI7fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMTtzOjE1OiJyZWNlbnRseV92aWV3ZWQiO2E6MTp7aTowO3M6MzoiMTMyIjt9czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748170616),
('WwrpbY5Cw84Qi73eR7XghPiSYDq6ahzqq9zTJaNt', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiYXBiNlNKNUZ0bHVUOGJrQ21BVlh2M2tzMWVWMGFuSlRoNWFHTW9RdiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MDoibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTE7czoxNToicmVjZW50bHlfdmlld2VkIjthOjE6e2k6MDtzOjM6IjEzMiI7fXM6NTI6ImxvZ2luX2FkbWluXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiO2k6MTt9', 1748170643),
('XP37lob23OXsOFQyp9y1fO1FCvcch0pwRtmBz5nE', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoia1hHWUVFNWRWUHp4TXZJMDYxT0xScklsc2ttRWl3VHZrN09BbzEyQSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hZG1pbi9kYXNoYm9hcmQiO31zOjM6InVybCI7YTowOnt9czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748165478),
('YP3aWilZrChUYOjktwyp6GoCnIPGU7DbO8zu9jPH', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo3OntzOjY6Il90b2tlbiI7czo0MDoiNUI1alMybUhFdEZ3NmxhOFU2dTRJMTFiVG9wM09DZFdrSHo0ZlpuYSI7czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmZkOTA5MTcyYSI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAxMToyMjo1NiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6Mzc6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9kYXNoYm9hcmQiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjM6InVybCI7YTowOnt9czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748174317),
('zckhip7Wf8bsQbI3WGAHebIsDmD0AY3UY5w2iUKB', 11, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36 Edg/136.0.0.0', 'YTo5OntzOjY6Il90b2tlbiI7czo0MDoiUjhrZjN2cnNXU3lRNlZTWUdRbWxYSldaME0wUExiTDJSOWQ2WGJ3RSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo4OiJndWVzdF9pZCI7czoxOToiZ3Vlc3RfNjgzMmUxYjkzYTMwMCI7czoxNjoiZ3Vlc3RfY3JlYXRlZF9hdCI7czoxOToiMjAyNS0wNS0yNSAwOToyNDowOSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzY6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMS9hZG1pbi9zZXR0aW5ncyI7fXM6MzoidXJsIjthOjA6e31zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxMTtzOjE1OiJyZWNlbnRseV92aWV3ZWQiO2E6MTp7aTowO3M6MzoiMTMyIjt9czo1MjoibG9naW5fYWRtaW5fNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aToxO30=', 1748170573);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `key` varchar(191) COLLATE utf8mb4_general_ci NOT NULL,
  `value` text COLLATE utf8mb4_general_ci,
  `type` varchar(191) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'string',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `settings_key_unique` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `created_at`, `updated_at`) VALUES
(1, 'site_description', 'PangAIaShop - AI-Powered E-Commerce Platform', 'string', NULL, '2025-05-25 07:56:55'),
(2, 'contact_email', 'contact@pangaiashop.com', 'string', NULL, '2025-05-25 07:56:55'),
(3, 'contact_phone', '+1-555-123-4567', 'string', NULL, '2025-05-25 07:56:55'),
(4, 'address', '123 Market St, San Francisco, CA 94103', 'string', NULL, '2025-05-25 07:56:55'),
(5, 'currency', 'USD', 'string', NULL, '2025-05-25 07:56:55'),
(6, 'currency_symbol', '$', 'string', NULL, '2025-05-25 07:56:55'),
(7, 'social_facebook', 'https://facebook.com/pangaiashop', 'string', NULL, '2025-05-25 07:56:55'),
(8, 'social_twitter', 'https://twitter.com/pangaiashop', 'string', NULL, '2025-05-25 07:56:55'),
(9, 'social_instagram', 'https://instagram.com/pangaiashop', 'string', NULL, '2025-05-25 07:56:55'),
(10, 'payment_stripe_enabled', '1', 'boolean', NULL, '2025-05-25 07:56:55'),
(11, 'payment_stripe_test_mode', '1', 'boolean', NULL, '2025-05-25 07:56:55'),
(12, 'payment_stripe_public_key', '', 'string', NULL, '2025-05-25 07:56:55'),
(13, 'payment_stripe_secret_key', '', 'string', NULL, '2025-05-25 07:56:55'),
(14, 'payment_paypal_enabled', '1', 'boolean', NULL, '2025-05-25 07:56:55'),
(15, 'payment_paypal_test_mode', '1', 'boolean', NULL, '2025-05-25 07:56:55'),
(16, 'payment_paypal_client_id', '', 'string', NULL, '2025-05-25 07:56:56'),
(17, 'payment_paypal_client_secret', '', 'string', NULL, '2025-05-25 07:56:56'),
(18, 'payment_cod_enabled', '1', 'boolean', NULL, '2025-05-25 07:56:56'),
(19, 'payment_cod_fee', '5', 'float', NULL, '2025-05-25 07:56:56'),
(20, 'tax_rate', '8.5', 'float', NULL, '2025-05-25 07:56:56'),
(21, 'allow_guest_checkout', '1', 'boolean', NULL, '2025-05-25 07:56:56'),
(22, 'shipping_standard_enabled', '1', 'boolean', NULL, '2025-05-25 07:56:56'),
(23, 'shipping_standard_name', 'Standard Shipping', 'string', NULL, '2025-05-25 07:56:56'),
(24, 'shipping_standard_cost', '5.99', 'float', NULL, '2025-05-25 07:56:56'),
(25, 'shipping_standard_min_days', '3', 'integer', NULL, '2025-05-25 07:56:56'),
(26, 'shipping_standard_max_days', '7', 'integer', NULL, '2025-05-25 07:56:56'),
(27, 'shipping_express_enabled', '1', 'boolean', NULL, '2025-05-25 07:56:56'),
(28, 'shipping_express_name', 'Express Shipping', 'string', NULL, '2025-05-25 07:56:56'),
(29, 'shipping_express_cost', '14.99', 'float', NULL, '2025-05-25 07:56:56'),
(30, 'shipping_express_min_days', '1', 'integer', NULL, '2025-05-25 07:56:56'),
(31, 'shipping_express_max_days', '3', 'integer', NULL, '2025-05-25 07:56:56'),
(32, 'shipping_free_enabled', '1', 'boolean', NULL, '2025-05-25 07:56:56'),
(33, 'shipping_free_name', 'Free Shipping', 'string', NULL, '2025-05-25 07:56:56'),
(34, 'shipping_free_min_order', '75', 'float', NULL, '2025-05-25 07:56:56'),
(35, 'shipping_free_min_days', '5', 'integer', NULL, '2025-05-25 07:56:56'),
(36, 'shipping_free_max_days', '10', 'integer', NULL, '2025-05-25 07:56:56'),
(37, 'shipping_allowed_countries', 'US,CA,UK,AU', 'string', NULL, '2025-05-25 07:56:56'),
(38, 'email_notification_new_order', '1', 'boolean', NULL, '2025-05-25 07:56:56'),
(39, 'email_notification_order_status', '1', 'boolean', NULL, '2025-05-25 07:56:56'),
(40, 'email_notification_low_inventory', '1', 'boolean', NULL, '2025-05-25 07:56:56'),
(41, 'email_template_welcome', '1', 'boolean', NULL, '2025-05-25 07:56:56'),
(42, 'email_template_order_confirmation', '1', 'boolean', NULL, '2025-05-25 07:56:56'),
(43, 'email_template_shipping_confirmation', '1', 'boolean', NULL, '2025-05-25 07:56:56'),
(44, 'email_template_delivery_confirmation', '1', 'boolean', NULL, '2025-05-25 07:56:56');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

DROP TABLE IF EXISTS `shipments`;
CREATE TABLE IF NOT EXISTS `shipments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` bigint UNSIGNED NOT NULL,
  `tracking_number` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `origin_country` char(2) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `destination_country` char(2) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `destination_region` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `destination_zip` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `weight` decimal(8,2) DEFAULT NULL,
  `shipping_zone` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('processing','shipped','delivered') COLLATE utf8mb4_general_ci NOT NULL,
  `actual_cost` decimal(10,2) NOT NULL,
  `shipping_method` enum('standard','express','priority','economy','overnight') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'standard',
  `service_level` enum('standard','express') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'standard',
  `base_cost` decimal(10,2) NOT NULL DEFAULT '0.00',
  `per_item_cost` decimal(8,2) NOT NULL DEFAULT '0.00',
  `per_weight_unit_cost` decimal(8,2) NOT NULL DEFAULT '0.00',
  `delivery_time_days` int DEFAULT NULL,
  `shipped_at` timestamp NOT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint UNSIGNED DEFAULT NULL,
  `updated_by` bigint UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shipments_order_id_index` (`order_id`),
  KEY `shipments_status_index` (`status`),
  KEY `shipments_created_by_index` (`created_by`),
  KEY `shipments_updated_by_index` (`updated_by`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`id`, `order_id`, `tracking_number`, `origin_country`, `destination_country`, `destination_region`, `destination_zip`, `weight`, `shipping_zone`, `status`, `actual_cost`, `shipping_method`, `service_level`, `base_cost`, `per_item_cost`, `per_weight_unit_cost`, `delivery_time_days`, `shipped_at`, `delivered_at`, `created_by`, `updated_by`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'TRK-KLXJLAVQ', 'US', 'AL', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 14:31:14', NULL, NULL, NULL, '2025-05-23 17:31:14', NULL),
(2, 2, 'TRK-3G9SBIMM', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 14:36:10', NULL, NULL, NULL, '2025-05-23 17:36:10', NULL),
(3, 3, 'TRK-ROMGSISF', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 14:38:23', NULL, NULL, NULL, '2025-05-23 17:38:23', NULL),
(4, 4, 'TRK-JH5HVWKD', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 14:45:10', NULL, NULL, NULL, '2025-05-23 17:45:10', NULL),
(5, 5, 'TRK-CHPDG1CE', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 14:47:34', NULL, NULL, NULL, '2025-05-23 17:47:34', NULL),
(6, 6, 'TRK-SVBXIO0I', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 14:50:58', NULL, NULL, NULL, '2025-05-23 17:50:58', NULL),
(7, 7, 'TRK-INAEBXZY', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 14:54:52', NULL, NULL, NULL, '2025-05-23 17:54:52', NULL),
(8, 8, 'TRK-C4GEQCIF', 'US', 'AL', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 15:18:40', NULL, NULL, NULL, '2025-05-23 18:18:40', NULL),
(9, 9, 'TRK-LMMHGK8J', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 16:05:31', NULL, NULL, NULL, '2025-05-23 19:05:31', NULL),
(10, 10, 'TRK-BVJBMMZU', 'US', 'AL', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 17:58:40', NULL, NULL, NULL, '2025-05-23 20:58:40', NULL),
(11, 11, 'TRK-LES1TNJI', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 18:12:06', NULL, NULL, NULL, '2025-05-23 21:12:06', NULL),
(12, 12, 'TRK-W6RD8GDD', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 18:13:10', NULL, NULL, NULL, '2025-05-23 21:13:10', NULL),
(13, 13, 'TRK-DVBRQVTI', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-23 18:14:05', NULL, NULL, NULL, '2025-05-23 21:14:05', NULL),
(14, 14, 'TRK-ZJUT7MX9', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-24 09:03:01', NULL, NULL, NULL, '2025-05-24 12:03:01', NULL),
(15, 15, 'TRK-6S6N3WVR', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-25 06:08:53', NULL, NULL, NULL, '2025-05-25 09:08:53', NULL),
(16, 16, 'TRK-37PGMXKW', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-25 06:51:53', NULL, NULL, NULL, '2025-05-25 09:51:53', NULL),
(17, 17, 'TRK-VAQCROUA', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-25 06:53:32', NULL, NULL, NULL, '2025-05-25 09:53:32', NULL),
(18, 18, 'TRK-8AJDSY2I', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-25 06:58:21', NULL, NULL, NULL, '2025-05-25 09:58:21', NULL),
(19, 19, 'TRK-OTDKVNAH', 'US', 'AL', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-25 06:59:38', NULL, NULL, NULL, '2025-05-25 09:59:38', NULL),
(20, 20, 'TRK-3RVOV2QW', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-25 07:07:12', NULL, NULL, NULL, '2025-05-25 10:07:12', NULL),
(21, 21, 'TRK-IHVS1XUN', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-25 08:10:40', NULL, NULL, NULL, '2025-05-25 11:10:40', NULL),
(22, 22, 'TRK-1PRLHIYN', 'US', 'AF', NULL, '09876', NULL, NULL, 'processing', 5.00, 'standard', 'standard', 0.00, 0.00, 0.00, 7, '2025-05-25 08:27:06', NULL, NULL, NULL, '2025-05-25 11:27:06', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `support_tickets`
--

DROP TABLE IF EXISTS `support_tickets`;
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('open','in_progress','waiting','resolved','closed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'open',
  `priority` enum('low','medium','high','urgent') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'medium',
  `department` enum('technical','billing','shipping','general') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'general',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `assigned_to` bigint UNSIGNED DEFAULT NULL,
  `order_id` bigint UNSIGNED DEFAULT NULL,
  `product_id` bigint UNSIGNED DEFAULT NULL,
  `resolution_time` int DEFAULT NULL COMMENT 'Time in seconds to resolve',
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `support_tickets_user_id_index` (`user_id`),
  KEY `support_tickets_status_index` (`status`),
  KEY `support_tickets_assigned_to_index` (`assigned_to`),
  KEY `support_tickets_order_id_index` (`order_id`),
  KEY `support_tickets_product_id_index` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_tickets`
--

INSERT INTO `support_tickets` (`id`, `user_id`, `subject`, `status`, `priority`, `department`, `created_at`, `updated_at`, `assigned_to`, `order_id`, `product_id`, `resolution_time`, `deleted_at`) VALUES
(1, 2, 'Order delivery delayed', 'open', 'medium', 'shipping', '2025-05-21 15:03:42', '2025-05-21 15:03:42', 9, 3, NULL, NULL, NULL),
(2, 5, 'Product arrived damaged', 'in_progress', 'high', 'shipping', '2025-05-18 15:03:42', '2025-05-22 15:03:42', 9, 7, 42, NULL, NULL),
(3, 8, 'Request for refund', 'waiting', 'medium', 'billing', '2025-05-19 15:03:42', '2025-05-21 15:03:42', 6, 12, NULL, NULL, NULL),
(4, 3, 'Wrong product received', 'open', 'high', 'shipping', '2025-05-22 15:03:42', '2025-05-22 15:03:42', 9, 5, 28, NULL, NULL),
(5, 10, 'Account login issues', 'in_progress', 'medium', 'technical', '2025-05-20 15:03:42', '2025-05-22 15:03:42', 7, NULL, NULL, NULL, NULL),
(6, 6, 'Product functionality questions', 'open', 'low', 'technical', '2025-05-22 15:03:42', '2025-05-22 15:03:42', 7, NULL, 95, NULL, NULL),
(7, 1, 'Billing discrepancy', 'resolved', 'medium', 'billing', '2025-05-16 15:03:42', '2025-05-22 15:03:42', 6, 1, NULL, 14400, NULL),
(8, 7, 'Return policy question', 'resolved', 'low', 'general', '2025-05-13 15:03:42', '2025-05-21 15:03:42', 10, NULL, NULL, 7200, NULL),
(9, 9, 'Missing parts in product', 'closed', 'high', 'general', '2025-05-08 15:03:42', '2025-05-20 15:03:42', 5, 15, 110, 28800, NULL),
(10, 4, 'Product recommendation request', 'resolved', 'low', 'general', '2025-05-15 15:03:42', '2025-05-21 15:03:42', 10, NULL, NULL, 3600, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Argon2 hash',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `avatar_url` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone_number` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `street` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `state` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` char(2) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `two_factor_secret` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_password_change` timestamp NULL DEFAULT NULL,
  `failed_login_count` tinyint NOT NULL DEFAULT '0',
  `account_status` enum('active','suspended','deactivated') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `last_login` timestamp NULL DEFAULT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `encrypted_recovery_email` blob,
  `two_factor_verified` tinyint(1) NOT NULL DEFAULT '0',
  `two_factor_method` enum('app','sms','email') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'app',
  `backup_codes` json DEFAULT NULL,
  `two_factor_enabled_at` timestamp NULL DEFAULT NULL,
  `two_factor_expires_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_email_password_hash_account_status_index` (`email`,`password_hash`,`account_status`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `updated_at`, `avatar_url`, `phone_number`, `street`, `city`, `state`, `postal_code`, `country`, `two_factor_secret`, `last_password_change`, `failed_login_count`, `account_status`, `last_login`, `is_verified`, `encrypted_recovery_email`, `two_factor_verified`, `two_factor_method`, `backup_codes`, `two_factor_enabled_at`, `two_factor_expires_at`, `deleted_at`) VALUES
(1, 'johndoe', 'john.doe@email.com', '$argon2id$v=19$m=65536,t=4,p=1$KTkySGVjX2ZWLjBPZU5YYg$R5E5hZ9gTTG9K1n80QJoE3GkLtvx40+yHwUOfLBpMiQ', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/men/11.jpg', '+1-555-123-7890', '123 Main St', 'New York', 'NY', '10001', 'US', NULL, NULL, 0, 'active', '2025-05-23 07:15:22', 1, NULL, 0, 'app', NULL, NULL, NULL, NULL),
(2, 'janesmith', 'jane.smith@email.com', '$argon2id$v=19$m=65536,t=4,p=1$WHBFSkdKLnJKaHB1SU1NVA$dX7aHzEiQgEQYsKrg80T50jFrLNB5PUBxJU+QC1aC64', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/women/12.jpg', '+1-555-234-5678', '456 Elm St', 'Los Angeles', 'CA', '90001', 'US', NULL, NULL, 0, 'active', '2025-05-22 11:30:15', 1, NULL, 0, 'app', NULL, NULL, NULL, NULL),
(3, 'mikebrown', 'mike.brown@email.com', '$argon2id$v=19$m=65536,t=4,p=1$QS5HVlU2aWcxTGZvRC5TTA$GUvU1P4kRcRrS0viHHHHYu9TgKaTg0SlRATJcPuddlo', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/men/13.jpg', '+1-555-345-6789', '789 Oak St', 'Chicago', 'IL', '60007', 'US', NULL, NULL, 0, 'active', '2025-05-21 06:12:43', 1, NULL, 0, 'app', NULL, NULL, NULL, NULL),
(4, 'sarahjones', 'sarah.jones@email.com', '$argon2id$v=19$m=65536,t=4,p=1$VEVLNmxERlA1Sm9sclh1Wg$s7hKkrxYUY3j06i4uKXECDktyQptKTcm8wFZZ+6bmM0', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/women/14.jpg', '+1-555-456-7890', '101 Pine St', 'Houston', 'TX', '77001', 'US', NULL, NULL, 0, 'active', '2025-05-23 14:45:30', 1, NULL, 0, 'app', NULL, NULL, NULL, NULL),
(5, 'davidwilson', 'david.wilson@email.com', '$argon2id$v=19$m=65536,t=4,p=1$UjdRV0lMSEh6d0JYYm9zdw$ueXvj7w7WWdZM1K8rMO8YH+1l7Rc34eqQwnPCIfSsvo', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/men/15.jpg', '+1-555-567-8901', '202 Maple St', 'Philadelphia', 'PA', '19019', 'US', NULL, NULL, 0, 'active', '2025-05-20 12:22:10', 1, NULL, 0, 'app', NULL, NULL, NULL, NULL),
(6, 'emilydavis', 'emily.davis@email.com', '$argon2id$v=19$m=65536,t=4,p=1$czRucWgwbGhkb1ZITnRBYg$7ttQ20isH/bEBv3PwaKtWvNYRUWPXTgqZq79a38aqiU', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/women/16.jpg', '+1-555-678-9012', '303 Cedar St', 'Phoenix', 'AZ', '85001', 'US', NULL, NULL, 0, 'active', '2025-05-22 08:05:17', 1, NULL, 0, 'app', NULL, NULL, NULL, NULL),
(7, 'robertmiller', 'robert.miller@email.com', '$argon2id$v=19$m=65536,t=4,p=1$R1V6YUdqZHdDZ3JOeVVISA$XEFqULzKgGBMgrjTjiQMUdX/SBojuI6qXRrLyO6SxN8', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/men/17.jpg', '+1-555-789-0123', '404 Birch St', 'San Antonio', 'TX', '78201', 'US', NULL, NULL, 0, 'active', '2025-05-23 13:38:25', 1, NULL, 0, 'app', NULL, NULL, NULL, NULL),
(8, 'jenniferwhite', 'jennifer.white@email.com', '$argon2id$v=19$m=65536,t=4,p=1$UlBIQ1Z4YnRpU21rZnZjSQ$xHBzLkBTmE0STdUgH4HM3jrIpRjCxmC8rl0/O21YSZU', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/women/18.jpg', '+1-555-890-1234', '505 Walnut St', 'San Diego', 'CA', '92101', 'US', NULL, NULL, 0, 'active', '2025-05-21 05:42:51', 1, NULL, 0, 'app', NULL, NULL, NULL, NULL),
(9, 'christopherlee', 'christopher.lee@email.com', '$argon2id$v=19$m=65536,t=4,p=1$cUkxTWpkck85ZzBGc0taTw$GnlERk0IKvWJvd86SxJBg33qsGj/W94SpBiKCJHxkXQ', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/men/19.jpg', '+1-555-901-2345', '606 Spruce St', 'Dallas', 'TX', '75201', 'US', NULL, NULL, 0, 'active', '2025-05-20 10:20:37', 1, NULL, 0, 'app', NULL, NULL, NULL, NULL),
(10, 'amandamorgan', 'amanda.morgan@email.com', '$argon2id$v=19$m=65536,t=4,p=1$cVJPZXJYQ0UwR2x6Zm8weA$oIBvMqbVK7y0ImLnitqNvtWhvAb3RvQ/w5jmBAGaMnQ', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'https://randomuser.me/api/portraits/women/20.jpg', '+1-555-012-3456', '707 Aspen St', 'San Jose', 'CA', '95101', 'US', NULL, NULL, 0, 'active', '2025-05-23 09:50:19', 1, NULL, 0, 'app', NULL, NULL, NULL, NULL),
(11, 'User0', 'User0@gmail.com', '$2y$12$ZrwH5VVWPLWb3FJ.2hDPEegzfLD4WvZ1JVy4JuY3.fKBpWRxriXRK', '2025-05-23 13:43:34', '2025-05-25 08:27:09', NULL, '0788888888', 'Taburbor', 'Amman', 'Amman, Jordan', '09876', 'JO', NULL, NULL, 0, 'active', '2025-05-25 08:24:39', 0, NULL, 0, 'app', NULL, NULL, NULL, NULL);

--
-- Triggers `users`
--
DROP TRIGGER IF EXISTS `validate_user_2fa`;
DELIMITER $$
CREATE TRIGGER `validate_user_2fa` BEFORE INSERT ON `users` FOR EACH ROW BEGIN
                IF NEW.two_factor_method = "sms" AND NEW.phone_number IS NULL THEN
                    SIGNAL SQLSTATE "45000" SET MESSAGE_TEXT = "Phone number required for SMS 2FA";
                END IF;
            END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

DROP TABLE IF EXISTS `user_preferences`;
CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `language` char(2) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'en',
  `currency` char(3) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'USD',
  `theme_preference` enum('light','dark','system') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'light',
  `notification_preferences` json DEFAULT NULL COMMENT 'JSON object with notification preferences',
  `ai_interaction_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `chat_history_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `last_interaction_date` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_preferences_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_preferences`
--

INSERT INTO `user_preferences` (`id`, `user_id`, `language`, `currency`, `theme_preference`, `notification_preferences`, `ai_interaction_enabled`, `chat_history_enabled`, `last_interaction_date`, `deleted_at`) VALUES
(1, 10, 'fr', 'CAD', 'system', '{\"order_updates\": true, \"email_marketing\": true, \"account_activity\": true, \"product_recommendations\": false}', 1, 0, '2025-05-14 15:03:42', NULL),
(2, 9, 'de', 'GBP', 'dark', '{\"order_updates\": true, \"email_marketing\": false, \"account_activity\": true, \"product_recommendations\": true}', 1, 1, '2025-05-10 15:03:42', NULL),
(3, 5, 'fr', 'CAD', 'system', '{\"order_updates\": true, \"email_marketing\": false, \"account_activity\": true, \"product_recommendations\": false}', 1, 0, '2025-05-11 15:03:42', NULL),
(4, 6, 'de', 'GBP', 'dark', '{\"order_updates\": true, \"email_marketing\": true, \"account_activity\": true, \"product_recommendations\": true}', 1, 1, '2025-05-14 15:03:42', NULL),
(5, 2, 'en', 'USD', 'light', '{\"order_updates\": true, \"email_marketing\": true, \"account_activity\": true, \"product_recommendations\": false}', 1, 1, '2025-05-16 15:03:42', NULL),
(6, 8, 'es', 'EUR', 'light', '{\"order_updates\": true, \"email_marketing\": true, \"account_activity\": true, \"product_recommendations\": false}', 0, 1, '2025-05-14 15:03:42', NULL),
(7, 1, 'en', 'USD', 'light', '{\"order_updates\": true, \"email_marketing\": false, \"account_activity\": true, \"product_recommendations\": false}', 1, 1, '2025-05-12 15:03:42', NULL),
(8, 3, 'de', 'GBP', 'dark', '{\"order_updates\": true, \"email_marketing\": false, \"account_activity\": true, \"product_recommendations\": true}', 1, 1, '2025-05-21 15:03:42', NULL),
(9, 7, 'en', 'USD', 'light', '{\"order_updates\": true, \"email_marketing\": false, \"account_activity\": true, \"product_recommendations\": false}', 1, 1, '2025-05-19 15:03:42', NULL),
(10, 4, 'es', 'EUR', 'light', '{\"order_updates\": true, \"email_marketing\": true, \"account_activity\": true, \"product_recommendations\": false}', 0, 1, '2025-05-22 15:03:42', NULL),
(16, 11, 'en', 'USD', 'light', '\"{\\\"email_notifications\\\":true,\\\"marketing_emails\\\":false,\\\"order_updates\\\":true,\\\"product_recommendations\\\":true}\"', 1, 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

DROP TABLE IF EXISTS `vendors`;
CREATE TABLE IF NOT EXISTS `vendors` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `payment_terms` text COLLATE utf8mb4_general_ci NOT NULL,
  `contact_email` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `contact_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_general_ci,
  `website` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('active','inactive','pending') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'active',
  `managed_by` bigint UNSIGNED DEFAULT NULL,
  `tax_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vendors_status_index` (`status`),
  KEY `vendors_managed_by_index` (`managed_by`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vendors`
--

INSERT INTO `vendors` (`id`, `name`, `payment_terms`, `contact_email`, `created_at`, `updated_at`, `contact_name`, `contact_phone`, `address`, `website`, `status`, `managed_by`, `tax_id`, `rating`, `deleted_at`) VALUES
(1, 'FashionHub Inc.', 'Net 30, 2% discount if paid within 10 days', 'contact@fashionhub.com', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'David Johnson', '+1-555-123-4567', '123 Fashion Ave, New York, NY 10001', 'https://www.fashionhub.com', 'active', 1, 'US-7890124', 4.5, NULL),
(2, 'ElectroTech Co.', 'Net 45', 'sales@electrotech.com', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'Linda Martinez', '+1-555-234-5678', '456 Circuit Rd, San Jose, CA 95123', 'https://www.electrotech.com', 'active', 2, 'US-6789012', 4.3, NULL),
(3, 'HomeStyle Solutions', 'Net 30', 'orders@homestyle.com', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'Michael Brown', '+1-555-345-6789', '789 Decor Blvd, Chicago, IL 60611', 'https://www.homestylesolutions.com', 'active', 3, 'US-5678901', 4.7, NULL),
(4, 'KidZone Supplies', 'Net 15, COD for orders below $1000', 'support@kidzone.com', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'Sarah Wilson', '+1-555-456-7890', '101 Toy Lane, Orlando, FL 32801', 'https://www.kidzonesupplies.com', 'active', 4, 'US-4567890', 4.2, NULL),
(5, 'PetParadise Wholesale', 'Net 30', 'wholesale@petparadise.com', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'James Taylor', '+1-555-567-8901', '202 Animal Way, Portland, OR 97201', 'https://www.petparadisewholesale.com', 'active', 5, 'US-3456789', 4.6, NULL),
(6, 'Bookworm Distributors', 'Net 45, 3% discount if paid within 15 days', 'orders@bookworm.com', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'Emma Davis', '+1-555-678-9012', '303 Reader St, Boston, MA 02108', 'https://www.bookwormdist.com', 'active', 6, 'US-2345678', 4.4, NULL),
(7, 'LuxWatch & Co.', 'Net 60', 'sales@luxwatch.com', '2025-05-23 15:03:38', '2025-05-23 15:03:38', 'Anthony Garcia', '+1-555-789-0123', '404 Time Square, Miami, FL 33131', 'https://www.luxwatchco.com', 'active', 7, 'US-1234567', 4.8, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlists`
--

DROP TABLE IF EXISTS `wishlists`;
CREATE TABLE IF NOT EXISTS `wishlists` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'My Wishlist',
  `wishlist_privacy` enum('private','public','shared') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'private',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wishlists_user_id_index` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlists`
--

INSERT INTO `wishlists` (`id`, `user_id`, `name`, `wishlist_privacy`, `created_at`, `deleted_at`) VALUES
(1, 10, 'Gift Ideas', 'shared', '2025-05-23 15:03:42', NULL),
(2, 9, 'My Favorites', 'private', '2025-05-23 15:03:42', NULL),
(3, 5, 'Wishlist', 'public', '2025-05-23 15:03:42', NULL),
(4, 6, 'My Favorites', 'private', '2025-05-23 15:03:42', NULL),
(5, 2, 'Wishlist', 'public', '2025-05-23 15:03:42', NULL),
(6, 8, 'Wishlist', 'public', '2025-05-23 15:03:42', NULL),
(7, 1, 'Gift Ideas', 'shared', '2025-05-23 15:03:42', NULL),
(8, 3, 'My Favorites', 'private', '2025-05-23 15:03:42', NULL),
(9, 7, 'Gift Ideas', 'shared', '2025-05-23 15:03:42', NULL),
(10, 4, 'Gift Ideas', 'shared', '2025-05-23 15:03:42', NULL),
(16, 11, 'My Wishlist', 'private', '2025-05-23 16:43:38', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wishlist_items`
--

DROP TABLE IF EXISTS `wishlist_items`;
CREATE TABLE IF NOT EXISTS `wishlist_items` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `wishlist_id` bigint UNSIGNED NOT NULL,
  `product_id` bigint UNSIGNED NOT NULL,
  `variant_id` bigint UNSIGNED DEFAULT NULL,
  `added_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Timestamp when item was added',
  `priority` enum('high','medium','low') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'medium' COMMENT 'Priority level of the wishlist item',
  `notes` text COLLATE utf8mb4_general_ci COMMENT 'Optional user notes about the item',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_wishlist_product_variant` (`wishlist_id`,`product_id`,`variant_id`),
  KEY `wishlist_items_wishlist_id_index` (`wishlist_id`),
  KEY `wishlist_items_product_id_index` (`product_id`),
  KEY `wishlist_items_variant_id_index` (`variant_id`)
) ENGINE=InnoDB AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist_items`
--

INSERT INTO `wishlist_items` (`id`, `wishlist_id`, `product_id`, `variant_id`, `added_at`, `priority`, `notes`, `deleted_at`, `created_at`, `updated_at`) VALUES
(1, 7, 140, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-03-28 15:03:42', NULL),
(2, 7, 134, NULL, '2025-05-23 15:03:42', 'medium', 'I really like the features of this item.', NULL, '2025-04-13 15:03:42', NULL),
(3, 7, 105, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-04-20 15:03:42', NULL),
(4, 7, 114, NULL, '2025-05-23 15:03:42', 'low', NULL, NULL, '2025-05-19 15:03:42', NULL),
(5, 5, 63, NULL, '2025-05-23 15:03:42', 'high', 'I really like the price of this item.', NULL, '2025-05-15 15:03:42', NULL),
(6, 5, 18, NULL, '2025-05-23 15:03:42', 'low', NULL, NULL, '2025-03-31 15:03:42', NULL),
(7, 5, 73, NULL, '2025-05-23 15:03:42', 'high', NULL, NULL, '2025-03-20 15:03:42', NULL),
(8, 5, 49, NULL, '2025-05-23 15:03:42', 'low', 'I really like the quality of this item.', NULL, '2025-05-11 15:03:42', NULL),
(9, 5, 149, NULL, '2025-05-23 15:03:42', 'medium', 'I really like the design of this item.', NULL, '2025-05-15 15:03:42', NULL),
(10, 8, 148, NULL, '2025-05-23 15:03:42', 'low', NULL, NULL, '2025-05-23 15:03:42', NULL),
(11, 8, 100, NULL, '2025-05-23 15:03:42', 'medium', 'I really like the price of this item.', NULL, '2025-02-23 15:03:42', NULL),
(12, 8, 138, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-03-27 15:03:42', NULL),
(13, 8, 10, NULL, '2025-05-23 15:03:42', 'medium', 'I really like the features of this item.', NULL, '2025-04-06 15:03:42', NULL),
(14, 8, 95, NULL, '2025-05-23 15:03:42', 'medium', 'I really like the design of this item.', NULL, '2025-05-05 15:03:42', NULL),
(15, 10, 125, NULL, '2025-05-23 15:03:42', 'high', NULL, NULL, '2025-03-13 15:03:42', NULL),
(16, 10, 96, NULL, '2025-05-23 15:03:42', 'low', NULL, NULL, '2025-03-21 15:03:42', NULL),
(17, 10, 93, NULL, '2025-05-23 15:03:42', 'medium', 'I really like the color of this item.', NULL, '2025-04-05 15:03:42', NULL),
(18, 10, 20, NULL, '2025-05-23 15:03:42', 'high', NULL, NULL, '2025-03-19 15:03:42', NULL),
(19, 10, 128, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-04-22 15:03:42', NULL),
(20, 10, 4, NULL, '2025-05-23 15:03:42', 'medium', 'I really like the design of this item.', NULL, '2025-03-21 15:03:42', NULL),
(21, 3, 130, NULL, '2025-05-23 15:03:42', 'high', 'I really like the color of this item.', NULL, '2025-04-10 15:03:42', NULL),
(22, 3, 144, NULL, '2025-05-23 15:03:42', 'low', 'I really like the price of this item.', NULL, '2025-05-07 15:03:42', NULL),
(23, 3, 132, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-03-03 15:03:42', NULL),
(24, 3, 42, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-04-13 15:03:42', NULL),
(25, 3, 18, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-05-05 15:03:42', NULL),
(26, 4, 143, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-04-27 15:03:42', NULL),
(27, 4, 135, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-04-04 15:03:42', NULL),
(28, 4, 51, NULL, '2025-05-23 15:03:42', 'high', 'I really like the color of this item.', NULL, '2025-04-15 15:03:42', NULL),
(29, 4, 95, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-03-12 15:03:42', NULL),
(30, 9, 124, NULL, '2025-05-23 15:03:42', 'low', 'I really like the price of this item.', NULL, '2025-05-11 15:03:42', NULL),
(31, 9, 113, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-03-16 15:03:42', NULL),
(32, 9, 25, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-05-20 15:03:42', NULL),
(33, 9, 55, NULL, '2025-05-23 15:03:42', 'high', 'I really like the color of this item.', NULL, '2025-04-23 15:03:42', NULL),
(34, 6, 117, NULL, '2025-05-23 15:03:42', 'medium', 'I really like the design of this item.', NULL, '2025-04-26 15:03:42', NULL),
(35, 6, 75, NULL, '2025-05-23 15:03:42', 'medium', 'I really like the quality of this item.', NULL, '2025-03-08 15:03:42', NULL),
(36, 6, 101, NULL, '2025-05-23 15:03:42', 'low', 'I really like the quality of this item.', NULL, '2025-04-29 15:03:42', NULL),
(37, 6, 146, NULL, '2025-05-23 15:03:42', 'low', 'I really like the features of this item.', NULL, '2025-03-06 15:03:42', NULL),
(38, 6, 93, NULL, '2025-05-23 15:03:42', 'low', NULL, NULL, '2025-03-16 15:03:42', NULL),
(39, 2, 48, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-04-29 15:03:42', NULL),
(40, 2, 22, NULL, '2025-05-23 15:03:42', 'high', 'I really like the price of this item.', NULL, '2025-03-27 15:03:42', NULL),
(41, 2, 129, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-05-01 15:03:42', NULL),
(42, 1, 33, NULL, '2025-05-23 15:03:42', 'medium', 'I really like the color of this item.', NULL, '2025-04-05 15:03:42', NULL),
(43, 1, 113, NULL, '2025-05-23 15:03:42', 'medium', 'I really like the price of this item.', NULL, '2025-02-26 15:03:42', NULL),
(44, 1, 35, NULL, '2025-05-23 15:03:42', 'medium', NULL, NULL, '2025-05-17 15:03:42', NULL),
(45, 1, 60, NULL, '2025-05-23 15:03:42', 'low', 'I really like the design of this item.', NULL, '2025-05-07 15:03:42', NULL),
(46, 1, 61, NULL, '2025-05-23 15:03:42', 'low', 'I really like the design of this item.', NULL, '2025-05-04 15:03:42', NULL),
(64, 16, 1, NULL, '2025-05-23 16:48:28', 'medium', NULL, '2025-05-23 14:27:45', '2025-05-23 13:48:28', '2025-05-23 14:27:45'),
(65, 16, 48, NULL, '2025-05-23 17:27:13', 'medium', NULL, NULL, '2025-05-23 14:27:13', '2025-05-23 14:27:13'),
(66, 16, 2, NULL, '2025-05-23 17:27:29', 'medium', NULL, '2025-05-23 14:27:45', '2025-05-23 14:27:29', '2025-05-23 14:27:45'),
(67, 16, 3, NULL, '2025-05-23 17:27:43', 'medium', NULL, '2025-05-24 06:51:32', '2025-05-23 14:27:43', '2025-05-24 06:51:32'),
(68, 16, 4, NULL, '2025-05-23 17:27:44', 'medium', NULL, NULL, '2025-05-23 14:27:44', '2025-05-23 14:27:44'),
(69, 16, 11, NULL, '2025-05-23 17:28:31', 'medium', NULL, NULL, '2025-05-23 14:28:31', '2025-05-23 14:28:31'),
(70, 16, 10, NULL, '2025-05-23 17:28:32', 'medium', NULL, NULL, '2025-05-23 14:28:32', '2025-05-23 14:28:32'),
(71, 16, 12, NULL, '2025-05-23 22:00:44', 'medium', NULL, NULL, '2025-05-23 19:00:44', '2025-05-23 19:00:44'),
(72, 16, 2, NULL, '2025-05-23 22:17:55', 'medium', NULL, '2025-05-23 19:18:07', '2025-05-23 19:17:55', '2025-05-23 19:18:07'),
(73, 16, 1, NULL, '2025-05-23 22:18:00', 'medium', NULL, '2025-05-23 19:18:08', '2025-05-23 19:18:00', '2025-05-23 19:18:08'),
(74, 16, 30, NULL, '2025-05-23 22:18:01', 'medium', NULL, NULL, '2025-05-23 19:18:01', '2025-05-23 19:18:01'),
(75, 16, 1, NULL, '2025-05-24 09:51:31', 'medium', NULL, NULL, '2025-05-24 06:51:31', '2025-05-24 06:51:31'),
(76, 16, 2, NULL, '2025-05-24 09:51:32', 'medium', NULL, NULL, '2025-05-24 06:51:32', '2025-05-24 06:51:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products` ADD FULLTEXT KEY `products_name_description_sku_fulltext` (`name`,`description`,`sku`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admin_audit_logs`
--
ALTER TABLE `admin_audit_logs`
  ADD CONSTRAINT `admin_audit_logs_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `carts_promo_code_id_foreign` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`),
  ADD CONSTRAINT `carts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `carts_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_cart_id_foreign` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `cart_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`);

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `categories_parent_category_id_foreign` FOREIGN KEY (`parent_category_id`) REFERENCES `categories` (`id`);

--
-- Constraints for table `inventories`
--
ALTER TABLE `inventories`
  ADD CONSTRAINT `inventories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `inventories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `inventories_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_handled_by_foreign` FOREIGN KEY (`handled_by`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `orders_promo_code_id_foreign` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`),
  ADD CONSTRAINT `orders_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `order_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`);

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_admin_id_foreign` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `password_reset_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `payments_processed_by_foreign` FOREIGN KEY (`processed_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `price_histories`
--
ALTER TABLE `price_histories`
  ADD CONSTRAINT `price_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `price_histories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `price_histories_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `products_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `products_vendor_id_foreign` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD CONSTRAINT `product_categories_added_by_foreign` FOREIGN KEY (`added_by`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `product_categories_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `product_categories_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `product_images_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `product_variants`
--
ALTER TABLE `product_variants`
  ADD CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `promo_codes`
--
ALTER TABLE `promo_codes`
  ADD CONSTRAINT `promo_codes_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `promo_code_usages`
--
ALTER TABLE `promo_code_usages`
  ADD CONSTRAINT `promo_code_usages_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `promo_code_usages_promo_code_id_foreign` FOREIGN KEY (`promo_code_id`) REFERENCES `promo_codes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `promo_code_usages_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_moderated_by_foreign` FOREIGN KEY (`moderated_by`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `shipments`
--
ALTER TABLE `shipments`
  ADD CONSTRAINT `shipments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `shipments_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `shipments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `support_tickets`
--
ALTER TABLE `support_tickets`
  ADD CONSTRAINT `support_tickets_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `admins` (`id`),
  ADD CONSTRAINT `support_tickets_order_id_foreign` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `support_tickets_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `support_tickets_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `vendors`
--
ALTER TABLE `vendors`
  ADD CONSTRAINT `vendors_managed_by_foreign` FOREIGN KEY (`managed_by`) REFERENCES `admins` (`id`);

--
-- Constraints for table `wishlists`
--
ALTER TABLE `wishlists`
  ADD CONSTRAINT `wishlists_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wishlist_items`
--
ALTER TABLE `wishlist_items`
  ADD CONSTRAINT `wishlist_items_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_items_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `product_variants` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wishlist_items_wishlist_id_foreign` FOREIGN KEY (`wishlist_id`) REFERENCES `wishlists` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
