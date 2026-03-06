-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 06, 2026 at 07:42 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `safenest_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'admin', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cart`
--

INSERT INTO `cart` (`id`, `user_id`, `created_at`) VALUES
(1, 1, '2026-03-05 06:47:39');

-- --------------------------------------------------------

--
-- Table structure for table `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int(11) NOT NULL,
  `cart_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`) VALUES
(1, 'Smart Security Cameras'),
(2, 'Video Doorbells'),
(3, 'Smart Door Locks'),
(4, 'Motion Sensors'),
(5, 'Window Sensors'),
(6, 'Smart Alarm Systems'),
(7, 'Smoke Detectors'),
(8, 'Carbon Monoxide Detectors'),
(9, 'Smart Lighting Security'),
(10, 'Outdoor Surveillance Systems'),
(11, 'Indoor Monitoring Devices'),
(12, 'Biometric Access Control'),
(13, 'AI Surveillance Systems'),
(14, 'Smart Garage Door Controllers'),
(15, 'Emergency Alert Systems'),
(16, 'Home Automation Security'),
(17, 'Smart Intercom Systems'),
(18, 'Wireless Security Kits'),
(19, 'Baby Monitoring Cameras'),
(20, 'Elderly Care Monitoring Devices');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_status` enum('pending','processing','shipped','delivered','cancelled','returned') DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `street` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) DEFAULT NULL,
  `country` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `order_status`, `payment_status`, `street`, `city`, `state`, `postal_code`, `country`, `created_at`) VALUES
(1, 1, 46000.00, 'processing', 'paid', 'p/o box basti cheena tehsil jampur district rajanpur', 'Rajanpur', 'Punjab', 'jampur', 'Pakistan', '2026-03-05 07:02:14'),
(2, 1, 22000.00, 'pending', 'pending', 'p/o box basti cheena tehsil jampur district rajanpur', 'Rajanpur', 'Punjab', 'jampur', 'Pakistan', '2026-03-06 06:10:26');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(1, 1, 27, 4, 4000.00),
(2, 1, 26, 10, 3000.00),
(3, 2, 9, 1, 22000.00);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `payment_method` enum('JazzCash','EasyPaisa','HBL Pay','UBL Omni','Credit Card') NOT NULL,
  `transaction_id` varchar(150) NOT NULL,
  `transaction_image` varchar(255) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `order_id`, `payment_method`, `transaction_id`, `transaction_image`, `payment_date`) VALUES
(1, 1, 'EasyPaisa', '43543ds43', 'uploads/transactions/1772694789_CCtv.jpg', '2026-03-05 07:13:09');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category_id` int(11) NOT NULL,
  `stock_quantity` int(11) DEFAULT 0,
  `image` varchar(255) DEFAULT 'uploads/default.png',
  `brand` varchar(100) DEFAULT NULL,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `specifications` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`specifications`)),
  `suitable_home_types` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`suitable_home_types`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `category_id`, `stock_quantity`, `image`, `brand`, `features`, `specifications`, `suitable_home_types`, `created_at`) VALUES
(1, 'Smart Cam A1', 'High resolution security camera', 15000.00, 1, 10, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"resolution\":\"1080p\"}', '{\"night_vision\":true}', '[\"apartment\",\"villa\"]', '2026-03-05 06:44:20'),
(2, 'Smart Cam A2', 'Compact smart camera', 12000.00, 1, 15, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"resolution\":\"720p\"}', '{\"night_vision\":true}', '[\"apartment\",\"villa\"]', '2026-03-05 06:44:20'),
(3, 'Smart Cam A3', 'Wide angle security camera', 18000.00, 1, 8, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"resolution\":\"1080p\"}', '{\"night_vision\":true}', '[\"villa\",\"large_home\"]', '2026-03-05 06:44:20'),
(4, 'Video Doorbell B1', 'Smart doorbell with camera', 14000.00, 2, 12, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"video\":\"HD\"}', '{\"motion_alert\":true}', '[\"apartment\",\"villa\"]', '2026-03-05 06:44:20'),
(5, 'Video Doorbell B2', 'Wireless video doorbell', 13000.00, 2, 10, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"video\":\"HD\"}', '{\"motion_alert\":true}', '[\"apartment\"]', '2026-03-05 06:44:20'),
(6, 'Video Doorbell B3', 'Doorbell with 2-way audio', 15000.00, 2, 5, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"video\":\"FullHD\"}', '{\"two_way_audio\":true}', '[\"villa\"]', '2026-03-05 06:44:20'),
(7, 'Smart Lock C1', 'Keyless entry smart lock', 20000.00, 3, 7, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"fingerprint\":true}', '{\"remote_unlock\":true}', '[\"apartment\",\"villa\"]', '2026-03-05 06:44:20'),
(8, 'Smart Lock C2', 'Bluetooth door lock', 18000.00, 3, 9, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"fingerprint\":false}', '{\"remote_unlock\":true}', '[\"apartment\"]', '2026-03-05 06:44:20'),
(9, 'Smart Lock C3', 'Wi-Fi smart lock', 22000.00, 3, 5, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"fingerprint\":true}', '{\"auto_lock\":true}', '[\"villa\"]', '2026-03-05 06:44:20'),
(10, 'Motion Sensor D1', 'Infrared motion sensor', 5000.00, 4, 20, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"range\":\"10m\"}', '{\"sensitivity\":\"high\"}', '[\"apartment\",\"villa\"]', '2026-03-05 06:44:20'),
(11, 'Motion Sensor D2', 'Wireless motion detector', 4500.00, 4, 25, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"range\":\"8m\"}', '{\"sensitivity\":\"medium\"}', '[\"apartment\"]', '2026-03-05 06:44:20'),
(12, 'Motion Sensor D3', 'Outdoor motion sensor', 6000.00, 4, 12, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"range\":\"12m\"}', '{\"sensitivity\":\"high\"}', '[\"villa\",\"large_home\"]', '2026-03-05 06:44:20'),
(13, 'Window Sensor E1', 'Magnetic window sensor', 3000.00, 5, 30, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"alert\":\"sound\"}', '{\"tamper_alert\":true}', '[\"apartment\",\"villa\"]', '2026-03-05 06:44:20'),
(14, 'Window Sensor E2', 'Wireless window detector', 3500.00, 5, 25, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"alert\":\"push\"}', '{\"tamper_alert\":true}', '[\"apartment\"]', '2026-03-05 06:44:20'),
(15, 'Window Sensor E3', 'Smart window sensor', 4000.00, 5, 18, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"alert\":\"email\"}', '{\"tamper_alert\":true}', '[\"villa\"]', '2026-03-05 06:44:20'),
(16, 'Alarm System F1', 'Home alarm with siren', 12000.00, 6, 10, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"siren\":true}', '{\"remote_control\":true}', '[\"apartment\",\"villa\"]', '2026-03-05 06:44:20'),
(17, 'Alarm System F2', 'Wireless alarm system', 14000.00, 6, 8, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"siren\":true}', '{\"remote_control\":true}', '[\"villa\"]', '2026-03-05 06:44:20'),
(18, 'Alarm System F3', 'Smart alarm kit', 15000.00, 6, 6, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"siren\":true}', '{\"mobile_alert\":true}', '[\"apartment\",\"villa\"]', '2026-03-05 06:44:20'),
(19, 'Smoke Detector G1', 'Wireless smoke detector', 4500.00, 7, 20, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"battery_life\":\"2yr\"}', '{\"alarm\":true}', '[\"apartment\",\"villa\"]', '2026-03-05 06:44:20'),
(20, 'Smoke Detector G2', 'Smart smoke sensor', 5000.00, 7, 15, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"battery_life\":\"3yr\"}', '{\"alarm\":true}', '[\"villa\"]', '2026-03-05 06:44:20'),
(21, 'Smoke Detector G3', 'Fire alarm detector', 4800.00, 7, 12, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"battery_life\":\"2yr\"}', '{\"alarm\":true}', '[\"apartment\"]', '2026-03-05 06:44:20'),
(22, 'CO Detector H1', 'Carbon monoxide sensor', 5000.00, 8, 18, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"sensor\":\"electrochemical\"}', '{\"alert\":true}', '[\"apartment\",\"villa\"]', '2026-03-05 06:44:20'),
(23, 'CO Detector H2', 'Smart CO detector', 5500.00, 8, 15, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"sensor\":\"electrochemical\"}', '{\"mobile_alert\":true}', '[\"villa\"]', '2026-03-05 06:44:20'),
(24, 'CO Detector H3', 'Wireless CO detector', 5200.00, 8, 10, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"sensor\":\"electrochemical\"}', '{\"alert\":true}', '[\"apartment\"]', '2026-03-05 06:44:20'),
(25, 'Smart Light I1', 'Motion-activated smart light', 3500.00, 9, 20, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"brightness\":\"800lm\"}', '{\"color\":\"warm_white\"}', '[\"apartment\",\"villa\"]', '2026-03-05 06:44:20'),
(26, 'Smart Light I2', 'Wi-Fi smart bulb', 3000.00, 9, 15, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"brightness\":\"600lm\"}', '{\"color\":\"cool_white\"}', '[\"apartment\"]', '2026-03-05 06:44:20'),
(27, 'Smart Light I3', 'Outdoor smart lighting', 4000.00, 9, 11, 'uploads/1772692939_CCtv.jpg', 'SafeNest', '{\"brightness\":\"1000lm\"}', '{\"color\":\"daylight\"}', '[\"villa\"]', '2026-03-05 06:44:20');

-- --------------------------------------------------------

--
-- Table structure for table `securityexpert`
--

CREATE TABLE `securityexpert` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `usertype` enum('expert','staff') DEFAULT 'expert',
  `created_at` datetime DEFAULT current_timestamp(),
  `status` enum('active','inactive','suspended') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `securityexpert`
--

INSERT INTO `securityexpert` (`id`, `name`, `email`, `password`, `usertype`, `created_at`, `status`) VALUES
(1, 'Haider', 'haider@gmail.com', '$2y$10$clgQ9p6Z9xLNtd6bP0aU2uK25hnmxLRJxpA70ECE9EnlY5FRDG3n2', 'expert', '2026-03-06 11:31:02', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `support_messages`
--

CREATE TABLE `support_messages` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_from_user` tinyint(1) NOT NULL DEFAULT 1,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `read_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `support_messages`
--

INSERT INTO `support_messages` (`id`, `user_id`, `is_from_user`, `message`, `created_at`, `read_at`) VALUES
(1, 1, 1, 'fdsfds', '2026-03-05 07:34:21', NULL),
(2, 1, 0, 'fdsa', '2026-03-05 07:35:32', '2026-03-06 06:22:51'),
(3, 1, 1, 'hy', '2026-03-05 07:52:28', NULL),
(4, 1, 0, 'Sorry, I could not understand that.', '2026-03-05 07:52:30', '2026-03-06 06:22:51'),
(5, 1, 1, 'hy', '2026-03-05 07:52:30', NULL),
(6, 1, 0, 'Sorry, I could not understand that.', '2026-03-05 07:52:33', '2026-03-06 06:22:51'),
(7, 1, 0, 'Yes', '2026-03-06 05:44:25', '2026-03-06 06:22:51'),
(8, 1, 0, 'YUes', '2026-03-06 06:40:22', '2026-03-06 06:41:47'),
(9, 1, 0, 'YUes', '2026-03-06 06:40:44', '2026-03-06 06:41:47'),
(10, 1, 0, 'This is from Expert', '2026-03-06 06:40:56', '2026-03-06 06:41:47');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `display_picture` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `cnic` char(13) NOT NULL,
  `study_program` enum('BSCS','BSIT','BSSE','BSDS','BSMGT','BSAI','MCS','MIT','ADPCS','MSCS') NOT NULL,
  `about_me` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','verified','rejected') DEFAULT 'pending',
  `failed_login_attempts` int(11) DEFAULT 0,
  `street` varchar(255) NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) DEFAULT NULL,
  `postal_code` varchar(20) NOT NULL,
  `country` varchar(100) DEFAULT 'Pakistan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `display_picture`, `password`, `cnic`, `study_program`, `about_me`, `created_at`, `status`, `failed_login_attempts`, `street`, `city`, `state`, `postal_code`, `country`) VALUES
(1, 'NASIRABBAS', 'nasiryt.827@gmail.com', 'uploads/1772690876_wp.jpg', '$2y$10$c6NJ6KFjlDDXgdEg8Kf5s.vaDDZhQ5KwMJyAT2nj0Pqhw/ol7E9j2', '3240254008217', 'ADPCS', 'XYZ', '2026-03-05 06:07:56', 'verified', 0, 'p/o box basti cheena tehsil jampur district rajanpur', 'Rajanpur', 'Punjab', 'jampur', 'Pakistan');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cart_id` (`cart_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `securityexpert`
--
ALTER TABLE `securityexpert`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cnic` (`cnic`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `securityexpert`
--
ALTER TABLE `securityexpert`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `support_messages`
--
ALTER TABLE `support_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `cart` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `support_messages`
--
ALTER TABLE `support_messages`
  ADD CONSTRAINT `support_messages_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
