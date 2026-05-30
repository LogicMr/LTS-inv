-- Inventory Management System Database Backup
-- Generated: 2026-05-30 14:04:52
-- Database: inventory_system
-- Compatible with MySQL 5.7+

-- Create database
CREATE DATABASE IF NOT EXISTS inventory_system;
USE inventory_system;

-- Table structure for `activity_logs`
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=327 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `activity_logs`
INSERT INTO `activity_logs` VALUES
('301', '2', 'Logout', 'User logged out', '2026-05-30 16:13:24'),
('302', '4', 'Login', 'User logged in successfully', '2026-05-30 16:13:41'),
('303', '4', 'Logout', 'User logged out', '2026-05-30 16:27:51'),
('304', '3', 'Login', 'User logged in successfully', '2026-05-30 16:27:59'),
('305', '3', 'Logout', 'User logged out', '2026-05-30 16:28:58'),
('306', '2', 'Login', 'User logged in successfully', '2026-05-30 16:29:06'),
('307', '2', 'Settings Updated', 'System settings were modified', '2026-05-30 16:29:54'),
('308', '2', 'Logout', 'User logged out', '2026-05-30 16:30:01'),
('309', '2', 'Login', 'User logged in successfully', '2026-05-30 16:30:13'),
('310', '2', 'Settings Updated', 'System settings were modified', '2026-05-30 16:30:55'),
('311', '2', 'Logout', 'User logged out', '2026-05-30 16:31:05'),
('312', '3', 'Login', 'User logged in successfully', '2026-05-30 16:31:14'),
('313', '3', 'Password Verification', 'User successfully verified password while logged in', '2026-05-30 16:38:21'),
('314', '3', 'Logout', 'User logged out', '2026-05-30 16:43:55'),
('315', '4', 'Login', 'User logged in successfully', '2026-05-30 16:44:23'),
('316', '4', 'Password Verification', 'User successfully verified password while logged in', '2026-05-30 16:54:47'),
('317', '4', 'Stock Reordered', 'Reordered 4 units of Hp MT 22 Mobile Think Client (Purchase ID: )', '2026-05-30 17:02:44'),
('318', '4', 'Logout', 'User logged out', '2026-05-30 17:03:05'),
('319', '2', 'Login', 'User logged in successfully', '2026-05-30 17:03:12'),
('320', '2', 'Settings Updated', 'System settings were modified', '2026-05-30 17:03:29'),
('321', '2', 'Settings Updated', 'System settings were modified', '2026-05-30 17:03:43'),
('322', '2', 'Settings Updated', 'System settings were modified', '2026-05-30 17:03:57'),
('323', '2', 'Settings Updated', 'System settings were modified', '2026-05-30 17:04:05'),
('324', '2', 'Settings Updated', 'System settings were modified', '2026-05-30 17:04:22'),
('325', '2', 'Logout', 'User logged out', '2026-05-30 17:04:34'),
('326', '2', 'Login', 'User logged in successfully', '2026-05-30 17:04:41');

-- Table structure for `categories`
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `categories`
INSERT INTO `categories` VALUES
('1', 'Drugs', 'Pharmaceutical products and medicines', '2026-02-06 17:19:43'),
('2', 'Groceries', 'Food items and household essentials', '2026-02-06 17:19:43'),
('3', 'Beverages', 'Drinks and liquids', '2026-02-06 17:19:43'),
('4', 'Electronics', 'Electronic devices and accessories', '2026-02-06 17:19:43'),
('5', 'Clothing', 'Apparel and textiles', '2026-02-06 17:19:43'),
('6', 'Other', 'Miscellaneous items', '2026-02-06 17:19:43'),
('7', 'Skin Products', 'Skin care products', '2026-02-17 13:07:01');

-- Table structure for `currencies`
CREATE TABLE IF NOT EXISTS `currencies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(3) NOT NULL,
  `name` varchar(100) NOT NULL,
  `symbol` varchar(10) NOT NULL,
  `exchange_rate` decimal(10,6) DEFAULT 1.000000,
  `is_active` tinyint(1) DEFAULT 1,
  `is_default` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `currencies`
INSERT INTO `currencies` VALUES
('4', 'TZS', 'Tanzanian Shilling', 'TSh', '1.000000', '1', '1', '2026-02-06 18:37:04', '2026-03-18 13:57:01');

-- Table structure for `customers`
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `products`
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) NOT NULL,
  `category_id` int(11) NOT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `quantity_in_stock` int(11) DEFAULT 0,
  `reorder_level` int(11) DEFAULT 10,
  `supplier_id` int(11) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `currency_id` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_products_category` (`category_id`),
  KEY `idx_products_supplier` (`supplier_id`),
  KEY `idx_products_barcode` (`barcode`),
  KEY `idx_products_expiry` (`expiry_date`)
) ENGINE=InnoDB AUTO_INCREMENT=86 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `products`
INSERT INTO `products` VALUES
('26', 'Microsoft Surface 4 1951', '1', 'ITM10000', '711600.00', '889500.00', '1', '10', NULL, NULL, 'BATCH-2026-001', 'Refurb Laptop, CPU i7-11TH, 16GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('27', 'MacBook Pro 2019', '1', 'ITM10001', '515600.00', '644500.00', '1', '10', NULL, NULL, 'BATCH-2026-002', 'Refurb Laptop, CPU i5-7TH, 8GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('28', 'Lenovo Thinkpad X1 Carbon', '1', 'ITM10002', '515600.00', '644500.00', '1', '10', NULL, NULL, 'BATCH-2026-003', 'Refurb Laptop, CPU i5-8TH, 8GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('29', 'Acer Spin SP314 -54N', '1', 'ITM10003', '515600.00', '644500.00', '1', '10', NULL, NULL, 'BATCH-2026-004', 'Used Laptop, CPU i5-10TH, 8GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('30', 'Hp Elitebook 840 G5', '1', 'ITM10004', '515600.00', '644500.00', '0', '10', NULL, NULL, 'BATCH-2026-005', 'Used Laptop, CPU i5-8TH, 8GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('31', 'Hp Elitebook 840 G5', '1', 'ITM10005', '515600.00', '644500.00', '3', '10', NULL, NULL, 'BATCH-2026-006', 'Used Laptop, CPU i5-8TH, 8GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('32', 'Hp Elitebook 840 G6', '1', 'ITM10006', '711600.00', '889500.00', '2', '10', NULL, NULL, 'BATCH-2026-007', 'Refurb Laptop, CPU i7-8TH, 16GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('33', 'Hp Elitebook 840 G8', '1', 'ITM10007', '611600.00', '764500.00', '1', '10', NULL, NULL, 'BATCH-2026-008', 'Used Laptop, CPU i5-11TH, 16GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('34', 'Hp ProBook 445 G9', '1', 'ITM10008', '461600.00', '577000.00', '5', '10', NULL, NULL, 'BATCH-2026-009', 'Refurb Laptop, CPU Ryzen 7 , 16GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('35', 'Hp Elitebook 845 G8', '1', 'ITM10009', '461600.00', '577000.00', '2', '10', NULL, NULL, 'BATCH-2026-010', 'Refurb Laptop, CPU Ryzen 5 , 16GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('36', 'Hp Hp Probook 430 G8', '1', 'ITM10010', '370800.00', '463500.00', '1', '10', NULL, NULL, 'BATCH-2026-011', 'Refurb Laptop, CPU i3-11TH, 8GB RAM, 128GB Storage, Screen 13\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('37', 'Hp Hp Probook 430/440 G5', '1', 'ITM10011', '415600.00', '519500.00', '2', '10', NULL, NULL, 'BATCH-2026-012', 'Refurb Laptop, CPU i3-7TH, 8GB RAM, 256GB Storage, Screen 13\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('38', 'Hp Hp Probook x360 11 G1', '1', 'ITM10012', '272800.00', '341000.00', '0', '10', NULL, NULL, 'BATCH-2026-013', 'Refurb Laptop, CPU celeron, 4GB RAM, 128GB Storage, Screen 12.6\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('39', 'Hp Hp Probook x360 11 G6', '1', 'ITM10013', '370800.00', '463500.00', '0', '10', NULL, NULL, 'BATCH-2026-014', 'Refurb Laptop, CPU i3-10TH, 8GB RAM, 128GB Storage, Screen 12.6\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('40', 'Hp Elitebook X360 830 G7', '1', 'ITM10014', '701200.00', '876500.00', '1', '10', NULL, NULL, 'BATCH-2026-015', 'Used Laptop, CPU  i5 10th gen , 16GB RAM, 512GB Storage, Screen 13\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('41', 'HP Elitebook x360 1030 G8', '1', 'ITM10015', '801200.00', '1001500.00', '3', '10', NULL, NULL, 'BATCH-2026-016', 'Refurb Laptop, CPU  i7 11th gen , 16GB RAM, 512GB Storage, Screen 13\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('42', 'HP Elitebook x360 1040 G8', '1', 'ITM10016', '801200.00', '1001500.00', '0', '10', NULL, NULL, 'BATCH-2026-017', 'Refurb Laptop, CPU  i7 11th gen , 16GB RAM, 512GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('43', 'HP Elitebook x360 1040 G8', '1', 'ITM10017', '611600.00', '764500.00', '1', '10', NULL, NULL, 'BATCH-2026-018', 'Refurb Laptop, CPU  i5 11th gen , 16GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('44', 'Hp Elitebook 830 G5', '1', 'ITM10018', '415600.00', '519500.00', '0', '10', NULL, NULL, 'BATCH-2026-019', 'Refurb Laptop, CPU i3-8TH, 8GB RAM, 256GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('45', 'Hp Zbook Firefly 14 G10', '1', 'ITM10019', '993200.00', '1241500.00', '1', '10', NULL, NULL, 'BATCH-2026-020', 'Refurb Laptop, CPU i7-13TH, 32GB RAM, 512GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('46', 'Hp Elitebook 830 G10', '1', 'ITM10020', '701200.00', '876500.00', '2', '10', NULL, NULL, 'BATCH-2026-021', 'New Laptop, CPU i5-13TH, 16GB RAM, 512GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('47', 'Hp Probook 635 Aero G8', '1', 'ITM10021', '461600.00', '577000.00', '0', '10', NULL, NULL, 'BATCH-2026-022', 'Refurb Laptop, CPU Ryzen 5 , 16GB RAM, 256GB Storage, Screen 13\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('48', 'Dell Latitude 3190', '1', 'ITM10022', '272800.00', '341000.00', '1', '10', NULL, NULL, 'BATCH-2026-023', 'Refurb Laptop, CPU intel celeron, 4GB RAM, 128GB Storage, Screen 12.6\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('49', 'Hp Elite Dragonfly', '1', 'ITM10023', '515600.00', '644500.00', '1', '10', NULL, NULL, 'BATCH-2026-024', 'Refurb Laptop, CPU i5-11TH, 8GB RAM, 256GB Storage, Screen 13\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('50', 'Hp MT 22 Mobile Think Client', '1', 'ITM10024', '510000.00', '555000.00', '4', '10', '3', NULL, 'BATCH-2026-025', 'Refurb Laptop, CPU intel celeron, 8GB RAM, 480GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 17:02:44', '1'),
('51', 'Hp Probook 450 G10 ', '1', 'ITM10025', '701200.00', '876500.00', '0', '10', NULL, NULL, 'BATCH-2026-026', 'New Laptop, CPU i5-13TH, 16GB RAM, 512GB Storage, Screen 15.6\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('52', 'Hp 15-S', '1', 'ITM10026', '455200.00', '569000.00', '2', '10', NULL, NULL, 'BATCH-2026-027', 'Refurb Laptop, CPU AMD Athlon Silver, 8GB RAM, 512GB Storage, Screen 15.6\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('53', 'Hp 15-S', '1', 'ITM10027', '455200.00', '569000.00', '2', '10', NULL, NULL, 'BATCH-2026-028', 'Refurb Laptop, CPU Ryzen 3, 8GB RAM, 512GB Storage, Screen 15.6\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('54', 'Hp 15-S', '1', 'ITM10028', '365600.00', '457000.00', '1', '10', NULL, NULL, 'BATCH-2026-029', 'Refurb Laptop, CPU Ryzen 3, 8GB RAM, 256GB Storage, Screen 15.6\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('55', 'Hp 15-S', '1', 'ITM10029', '365600.00', '457000.00', '1', '10', NULL, NULL, 'BATCH-2026-030', 'Refurb Laptop, CPU Ryzen 5 , 8GB RAM, 256GB Storage, Screen 15.6\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('56', 'HP ProBook x360 435 G8', '1', 'ITM10030', '551200.00', '689000.00', '1', '10', NULL, NULL, 'BATCH-2026-031', 'Refurb Laptop, CPU Ryzen 7 , 16GB RAM, 512GB Storage, Screen 14\"', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('57', 'nan nan', '2', 'ITM10031', '272800.00', '341000.00', '0', '10', NULL, NULL, 'BATCH-2026-032', 'nan nan, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('58', 'Hp Prodesk 600 G3 Mini', '2', 'ITM10032', '618800.00', '773500.00', '2', '10', NULL, NULL, 'BATCH-2026-033', 'Refurb Desktop, CPU i7-7TH, 12GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('59', 'Lenovo Thinkcentre M900', '2', 'ITM10033', '553000.00', '691250.00', '0', '10', NULL, NULL, 'BATCH-2026-034', 'Refurb Desktop, CPU i5-6TH, 4GB RAM, 500GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('60', 'Hp EliteDesk 800 G5 SFF', '2', 'ITM10034', '451000.00', '563750.00', '0', '10', NULL, NULL, 'BATCH-2026-035', 'Refurb Desktop, CPU i9-9TH, 8GB RAM, 500GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('61', 'Dell Optiplex 9010/990/7010/3010', '2', 'ITM10035', '553000.00', '691250.00', '2', '10', NULL, NULL, 'BATCH-2026-036', 'Used Desktop, CPU i5-4TH, 4GB RAM, 500GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('62', 'Hp Compaq 6200 Microtower', '2', 'ITM10036', '441000.00', '551250.00', '1', '10', NULL, NULL, 'BATCH-2026-037', 'Used Desktop, CPU i3-2ND, 3GB RAM, 500GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('63', 'nan nan', '2', 'ITM10037', '272800.00', '341000.00', '0', '10', NULL, NULL, 'BATCH-2026-038', 'nan nan, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('64', 'Hp Eliteone 800 G6', '2', 'ITM10038', '701200.00', '876500.00', '1', '10', NULL, NULL, 'BATCH-2026-039', 'Refurb AIO, CPU i5-9TH, 16GB RAM, 512GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('65', 'Hp  Eliteone 800 G5', '2', 'ITM10039', '515600.00', '644500.00', '1', '10', NULL, NULL, 'BATCH-2026-040', 'Refurb AIO, CPU i5-9TH, 8GB RAM, 256GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('66', 'Lenovo Thinkcentre M910', '2', 'ITM10040', '422800.00', '528500.00', '1', '10', NULL, NULL, 'BATCH-2026-041', 'Refurb AIO, CPU i5-6TH, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('67', 'Hp  ProOne 400 G2', '2', 'ITM10041', '515600.00', '644500.00', '1', '10', NULL, NULL, 'BATCH-2026-042', 'Refurb AIO, CPU i5-9TH, 8GB RAM, 256GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('68', 'nan nan', '2', 'ITM10042', '272800.00', '341000.00', '0', '10', NULL, NULL, 'BATCH-2026-043', 'nan nan, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('69', 'Dell  U2520D', '2', 'ITM10043', '272800.00', '341000.00', '0', '10', NULL, NULL, 'BATCH-2026-044', 'Used Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('70', 'Dell  U2518D', '2', 'ITM10044', '272800.00', '341000.00', '1', '10', NULL, NULL, 'BATCH-2026-045', 'Used Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('71', 'Hp M22f', '2', 'ITM10045', '272800.00', '341000.00', '1', '10', NULL, NULL, 'BATCH-2026-046', 'refurb Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('72', 'Lenovo Thinkvision T24i-2L', '2', 'ITM10046', '272800.00', '341000.00', '1', '10', NULL, NULL, 'BATCH-2026-047', 'refurb Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('73', 'Dell  P2419H', '2', 'ITM10047', '272800.00', '341000.00', '2', '10', NULL, NULL, 'BATCH-2026-048', 'Used Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('74', 'Dell  P2417H', '2', 'ITM10048', '272800.00', '341000.00', '7', '10', NULL, NULL, 'BATCH-2026-049', 'Used Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('75', 'Dell  P190ST', '2', 'ITM10049', '272800.00', '341000.00', '1', '10', NULL, NULL, 'BATCH-2026-050', 'Used Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('76', 'Dell  P1917S', '2', 'ITM10050', '272800.00', '341000.00', '3', '10', NULL, NULL, 'BATCH-2026-051', 'Used Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('77', 'Hp ZR220', '2', 'ITM10051', '272800.00', '341000.00', '1', '10', NULL, NULL, 'BATCH-2026-052', 'refurb Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('78', 'Hp P201', '2', 'ITM10052', '272800.00', '341000.00', '2', '10', NULL, NULL, 'BATCH-2026-053', 'refurb Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('79', 'Hp E233', '2', 'ITM10053', '272800.00', '341000.00', '1', '10', NULL, NULL, 'BATCH-2026-054', 'Refurb Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('80', 'Hp E223', '2', 'ITM10054', '272800.00', '341000.00', '5', '10', NULL, NULL, 'BATCH-2026-055', 'Refurb Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('81', 'Dell  E1910C', '2', 'ITM10055', '272800.00', '341000.00', '1', '10', NULL, NULL, 'BATCH-2026-056', 'Used Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('82', 'Dell  E1917S', '2', 'ITM10056', '272800.00', '341000.00', '1', '10', NULL, NULL, 'BATCH-2026-057', 'Used Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('83', 'Samsung S24A310NHN', '2', 'ITM10057', '272800.00', '341000.00', '2', '10', NULL, NULL, 'BATCH-2026-058', 'Used Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('84', 'Hp E24G4', '2', 'ITM10058', '272800.00', '341000.00', '4', '10', NULL, NULL, 'BATCH-2026-059', 'refurb Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1'),
('85', 'Hp Z23', '2', 'ITM10059', '272800.00', '341000.00', '1', '10', NULL, NULL, 'BATCH-2026-060', 'Refurb Monitor, CPU nan, 0GB RAM, 0GB Storage, Screen nan', '1', '2026-05-30 16:25:26', '2026-05-30 16:25:26', '1');

-- Table structure for `purchase_items`
CREATE TABLE IF NOT EXISTS `purchase_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `cost_price` decimal(10,2) NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `batch_number` varchar(50) DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `barcode` varchar(100) DEFAULT NULL COMMENT 'Product barcode for purchase item',
  `currency_id` int(11) DEFAULT NULL COMMENT 'Currency ID for purchase item',
  PRIMARY KEY (`id`),
  KEY `purchase_id` (`purchase_id`),
  KEY `idx_purchase_items_product` (`product_id`),
  KEY `fk_purchase_items_currency` (`currency_id`),
  CONSTRAINT `fk_purchase_items_currency` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `purchase_summary`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `purchase_summary` AS select `purchases`.`purchase_date` AS `purchase_date`,count(0) AS `total_purchases`,sum(`purchases`.`total_amount`) AS `total_cost` from `purchases` group by `purchases`.`purchase_date`;

-- Table structure for `purchases`
CREATE TABLE IF NOT EXISTS `purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(11) NOT NULL,
  `purchase_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `currency_id` int(11) DEFAULT 1,
  `exchange_rate` decimal(10,6) DEFAULT 1.000000,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_purchases_supplier` (`supplier_id`),
  KEY `idx_purchases_date` (`purchase_date`),
  KEY `currency_id` (`currency_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `roles`
CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `roles`
INSERT INTO `roles` VALUES
('1', 'Admin', 'System administrator with full access', '2026-02-06 17:19:43'),
('2', 'Manager', 'Store manager/pharmacist with inventory management access', '2026-02-06 17:19:43'),
('3', 'Cashier', 'Cashier with sales access only', '2026-02-06 17:19:43');

-- Table structure for `sale_items`
CREATE TABLE IF NOT EXISTS `sale_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `currency_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `idx_sale_items_product` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `sales`
CREATE TABLE IF NOT EXISTS `sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(10,2) NOT NULL,
  `discount_amount` decimal(10,2) DEFAULT 0.00,
  `final_amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT 'Cash',
  `customer_name` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `currency_id` int(11) DEFAULT 1,
  `exchange_rate` decimal(10,6) DEFAULT 1.000000,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  KEY `idx_sales_date` (`sale_date`),
  KEY `currency_id` (`currency_id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table structure for `sales_summary`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `sales_summary` AS select cast(`sales`.`sale_date` as date) AS `sale_date`,count(0) AS `total_sales`,sum(`sales`.`total_amount`) AS `total_revenue`,sum(`sales`.`final_amount`) AS `net_revenue` from `sales` group by cast(`sales`.`sale_date` as date);

-- Table structure for `stock_alerts`
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `stock_alerts` AS select `p`.`id` AS `id`,`p`.`name` AS `name`,`p`.`quantity_in_stock` AS `quantity_in_stock`,`p`.`reorder_level` AS `reorder_level`,`p`.`expiry_date` AS `expiry_date`,`c`.`name` AS `category_name`,case when `p`.`quantity_in_stock` <= `p`.`reorder_level` then 'Low Stock' when `p`.`expiry_date` <= curdate() + interval 30 day then 'Expiring Soon' else 'OK' end AS `alert_status` from (`products` `p` left join `categories` `c` on(`p`.`category_id` = `c`.`id`)) where `p`.`is_active` = 1 and (`p`.`quantity_in_stock` <= `p`.`reorder_level` or `p`.`expiry_date` <= curdate() + interval 30 day);

-- Data for `stock_alerts`
INSERT INTO `stock_alerts` VALUES
('26', 'Microsoft Surface 4 1951', '1', '10', NULL, 'Drugs', 'Low Stock'),
('27', 'MacBook Pro 2019', '1', '10', NULL, 'Drugs', 'Low Stock'),
('28', 'Lenovo Thinkpad X1 Carbon', '1', '10', NULL, 'Drugs', 'Low Stock'),
('29', 'Acer Spin SP314 -54N', '1', '10', NULL, 'Drugs', 'Low Stock'),
('30', 'Hp Elitebook 840 G5', '0', '10', NULL, 'Drugs', 'Low Stock'),
('31', 'Hp Elitebook 840 G5', '3', '10', NULL, 'Drugs', 'Low Stock'),
('32', 'Hp Elitebook 840 G6', '2', '10', NULL, 'Drugs', 'Low Stock'),
('33', 'Hp Elitebook 840 G8', '1', '10', NULL, 'Drugs', 'Low Stock'),
('34', 'Hp ProBook 445 G9', '5', '10', NULL, 'Drugs', 'Low Stock'),
('35', 'Hp Elitebook 845 G8', '2', '10', NULL, 'Drugs', 'Low Stock'),
('36', 'Hp Hp Probook 430 G8', '1', '10', NULL, 'Drugs', 'Low Stock'),
('37', 'Hp Hp Probook 430/440 G5', '2', '10', NULL, 'Drugs', 'Low Stock'),
('38', 'Hp Hp Probook x360 11 G1', '0', '10', NULL, 'Drugs', 'Low Stock'),
('39', 'Hp Hp Probook x360 11 G6', '0', '10', NULL, 'Drugs', 'Low Stock'),
('40', 'Hp Elitebook X360 830 G7', '1', '10', NULL, 'Drugs', 'Low Stock'),
('41', 'HP Elitebook x360 1030 G8', '3', '10', NULL, 'Drugs', 'Low Stock'),
('42', 'HP Elitebook x360 1040 G8', '0', '10', NULL, 'Drugs', 'Low Stock'),
('43', 'HP Elitebook x360 1040 G8', '1', '10', NULL, 'Drugs', 'Low Stock'),
('44', 'Hp Elitebook 830 G5', '0', '10', NULL, 'Drugs', 'Low Stock'),
('45', 'Hp Zbook Firefly 14 G10', '1', '10', NULL, 'Drugs', 'Low Stock'),
('46', 'Hp Elitebook 830 G10', '2', '10', NULL, 'Drugs', 'Low Stock'),
('47', 'Hp Probook 635 Aero G8', '0', '10', NULL, 'Drugs', 'Low Stock'),
('48', 'Dell Latitude 3190', '1', '10', NULL, 'Drugs', 'Low Stock'),
('49', 'Hp Elite Dragonfly', '1', '10', NULL, 'Drugs', 'Low Stock'),
('50', 'Hp MT 22 Mobile Think Client', '4', '10', NULL, 'Drugs', 'Low Stock'),
('51', 'Hp Probook 450 G10 ', '0', '10', NULL, 'Drugs', 'Low Stock'),
('52', 'Hp 15-S', '2', '10', NULL, 'Drugs', 'Low Stock'),
('53', 'Hp 15-S', '2', '10', NULL, 'Drugs', 'Low Stock'),
('54', 'Hp 15-S', '1', '10', NULL, 'Drugs', 'Low Stock'),
('55', 'Hp 15-S', '1', '10', NULL, 'Drugs', 'Low Stock'),
('56', 'HP ProBook x360 435 G8', '1', '10', NULL, 'Drugs', 'Low Stock'),
('57', 'nan nan', '0', '10', NULL, 'Groceries', 'Low Stock'),
('58', 'Hp Prodesk 600 G3 Mini', '2', '10', NULL, 'Groceries', 'Low Stock'),
('59', 'Lenovo Thinkcentre M900', '0', '10', NULL, 'Groceries', 'Low Stock'),
('60', 'Hp EliteDesk 800 G5 SFF', '0', '10', NULL, 'Groceries', 'Low Stock'),
('61', 'Dell Optiplex 9010/990/7010/3010', '2', '10', NULL, 'Groceries', 'Low Stock'),
('62', 'Hp Compaq 6200 Microtower', '1', '10', NULL, 'Groceries', 'Low Stock'),
('63', 'nan nan', '0', '10', NULL, 'Groceries', 'Low Stock'),
('64', 'Hp Eliteone 800 G6', '1', '10', NULL, 'Groceries', 'Low Stock'),
('65', 'Hp  Eliteone 800 G5', '1', '10', NULL, 'Groceries', 'Low Stock'),
('66', 'Lenovo Thinkcentre M910', '1', '10', NULL, 'Groceries', 'Low Stock'),
('67', 'Hp  ProOne 400 G2', '1', '10', NULL, 'Groceries', 'Low Stock'),
('68', 'nan nan', '0', '10', NULL, 'Groceries', 'Low Stock'),
('69', 'Dell  U2520D', '0', '10', NULL, 'Groceries', 'Low Stock'),
('70', 'Dell  U2518D', '1', '10', NULL, 'Groceries', 'Low Stock'),
('71', 'Hp M22f', '1', '10', NULL, 'Groceries', 'Low Stock'),
('72', 'Lenovo Thinkvision T24i-2L', '1', '10', NULL, 'Groceries', 'Low Stock'),
('73', 'Dell  P2419H', '2', '10', NULL, 'Groceries', 'Low Stock'),
('74', 'Dell  P2417H', '7', '10', NULL, 'Groceries', 'Low Stock'),
('75', 'Dell  P190ST', '1', '10', NULL, 'Groceries', 'Low Stock'),
('76', 'Dell  P1917S', '3', '10', NULL, 'Groceries', 'Low Stock'),
('77', 'Hp ZR220', '1', '10', NULL, 'Groceries', 'Low Stock'),
('78', 'Hp P201', '2', '10', NULL, 'Groceries', 'Low Stock'),
('79', 'Hp E233', '1', '10', NULL, 'Groceries', 'Low Stock'),
('80', 'Hp E223', '5', '10', NULL, 'Groceries', 'Low Stock'),
('81', 'Dell  E1910C', '1', '10', NULL, 'Groceries', 'Low Stock'),
('82', 'Dell  E1917S', '1', '10', NULL, 'Groceries', 'Low Stock'),
('83', 'Samsung S24A310NHN', '2', '10', NULL, 'Groceries', 'Low Stock'),
('84', 'Hp E24G4', '4', '10', NULL, 'Groceries', 'Low Stock'),
('85', 'Hp Z23', '1', '10', NULL, 'Groceries', 'Low Stock');

-- Table structure for `suppliers`
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `suppliers`
INSERT INTO `suppliers` VALUES
('1', 'MedSupply Corp', 'John Smith', '+1234567890', 'john@medsupply.com', '123 Pharma St, City', '2026-02-06 17:19:44'),
('2', 'Fresh Foods Ltd', 'Mary Johnson', '+1234567891', 'mary@freshfoods.com', '456 Market Ave, City', '2026-02-06 17:19:44'),
('3', 'Tech Distributors', 'Bob Wilson', '+1234567892', 'bob@techdist.com', '789 Tech Blvd, City', '2026-02-06 17:19:44');

-- Table structure for `system_settings`
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `setting_group` varchar(50) NOT NULL DEFAULT 'general',
  `description` text DEFAULT NULL,
  `is_editable` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`),
  UNIQUE KEY `unique_setting` (`setting_key`),
  KEY `idx_group` (`setting_group`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `system_settings`
INSERT INTO `system_settings` VALUES
('1', 'store_name', 'LTS Inventory System', 'string', 'general', 'Store or business name displayed throughout system', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('2', 'default_currency', '4', 'number', 'general', 'Default currency ID for new records', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('3', 'items_per_page', '20', 'number', 'general', 'Number of items displayed per page in lists', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('4', 'company_address', 'P.O. Box 3013', 'string', 'general', 'Company address for receipts and documents', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('5', 'company_phone', '0683186070', 'string', 'general', 'Company phone number for receipts and documents', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('6', 'company_email', 'aropestephen@gmail.com', 'string', 'general', 'Company email for receipts and documents', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('7', 'tax_rate', '0', 'number', 'general', 'Default tax rate percentage', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('8', 'enable_tax', '0', 'boolean', 'general', 'Enable tax calculations on sales', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('9', 'receipt_header', 'Thank you for your business!', 'string', 'receipt', 'Header text printed on receipts', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('10', 'receipt_footer', 'Visit us again!', 'string', 'receipt', 'Footer text printed on receipts', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('11', 'low_stock_threshold', '10', 'number', 'inventory', 'Alert when stock falls below this quantity', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('12', 'expiry_warning_days', '30', 'number', 'inventory', 'Warn when products expire within this many days', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('13', 'enable_email_notifications', '0', 'boolean', 'notifications', 'Enable email notifications for alerts', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('14', 'backup_retention_days', '30', 'number', 'backup', 'Number of days to retain backup files', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('15', 'enable_auto_backup', '0', 'boolean', 'backup', 'Enable automatic daily backups', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('16', 'timezone', 'UTC', 'string', 'general', 'System timezone for date/time display', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('17', 'date_format', 'Y-m-d', 'string', 'general', 'Default date format for display', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('18', 'time_format', 'H:i:s', 'string', 'general', 'Default time format for display', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('19', 'decimal_places', '2', 'number', 'general', 'Number of decimal places for currency', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('20', 'thousands_separator', ',', 'string', 'general', 'Thousands separator for numbers', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22'),
('21', 'decimal_separator', '.', 'string', 'general', 'Decimal separator for numbers', '1', '2026-02-17 16:12:05', '2026-05-30 17:04:22');

-- Table structure for `users`
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Data for `users`
INSERT INTO `users` VALUES
('1', 'admin', '$2y$10$YourGeneratedHashHere', 'System Administrator', 'admin@inventory.com', NULL, '1', '1', '2026-02-06 17:19:44', '2026-02-06 17:59:06'),
('2', 'Stephen', '$2y$10$piu2aoW/HawHMvLVKG0Fze4grruD2JoqBxy90RfiKzNbVtHKMJrWe', 'Stephen Arope', 'aropestephen@gmail.com', '0683186070', '1', '1', '2026-02-06 17:31:00', '2026-05-30 17:04:52'),
('3', 'Bennedict', '$2y$10$sU3FylyZhiL19nZZjyvgN.HMNdF3XwJ/c/4MzOIcQbf.DNUJ2I9tS', 'Bennedict Ndokeji', 'bennedictndokeji@gmail.com', '0717340944', '3', '1', '2026-02-06 18:06:41', '2026-05-30 16:43:55'),
('4', 'Nabeel', '$2y$10$VxKIu2TkkayC.uoR13zMCes11KIOiFWFnDpSgN4g3ZbdLtM7bs7yC', 'Nabeel Nabeel', 'nabeeljamaal306@gmail.com', '0734817343', '2', '1', '2026-02-06 18:10:20', '2026-05-30 17:03:05'),
('5', 'Julieth', '$2y$10$zZKCwrh5jNy017rAue/Z8e3kCDDPyZkNFsE.gz6MRXc8MvlUh6g/i', 'Julieth Arope', 'aropejuliethhope@gmail.com', '0683186070', '1', '1', '2026-02-17 15:52:49', '2026-02-21 16:25:15');

-- End of backup
