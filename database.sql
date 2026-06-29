-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jun 24, 2026 at 08:33 AM
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
-- Database: `gs_coffee`
--

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `total_price` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `pickup_type` varchar(50) NOT NULL DEFAULT 'Store Pickup',
  `receipt_file` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Preparing','Completed','Cancelled') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_date`, `total_price`, `payment_method`, `pickup_type`, `receipt_file`, `status`) VALUES
(1, 3, '2026-06-15 03:29:37', 160.00, 'COD', 'Store Pickup', NULL, 'Cancelled'),
(2, 3, '2026-06-15 03:29:47', 160.00, 'COD', 'Store Pickup', NULL, 'Cancelled'),
(3, 3, '2026-06-15 03:31:45', 160.00, 'COD', 'Store Pickup', NULL, 'Cancelled'),
(4, 3, '2026-06-19 10:35:05', 250.00, 'COD', 'Store Pickup', NULL, 'Cancelled'),
(5, 3, '2026-06-19 10:35:23', 250.00, 'COD', 'Store Pickup', NULL, 'Cancelled'),
(6, 3, '2026-06-19 10:36:00', 250.00, 'COD', 'Store Pickup', NULL, 'Cancelled'),
(7, 3, '2026-06-19 10:37:37', 250.00, 'COD', 'Store Pickup', NULL, 'Pending'),
(8, 3, '2026-06-19 10:37:40', 250.00, 'COD', 'Store Pickup', NULL, 'Pending'),
(9, 3, '2026-06-19 10:37:45', 250.00, 'COD', 'Store Pickup', NULL, 'Pending'),
(10, 3, '2026-06-19 10:37:51', 250.00, 'COD', 'Store Pickup', NULL, 'Cancelled'),
(11, 3, '2026-06-19 10:40:08', 250.00, 'COD', 'Store Pickup', NULL, 'Cancelled'),
(12, 3, '2026-06-19 10:40:35', 110.00, 'COD', 'Store Pickup', NULL, 'Cancelled'),
(13, 3, '2026-06-21 07:09:28', 120.00, 'COD', 'Store Pickup', NULL, 'Cancelled'),
(14, 3, '2026-06-21 07:41:18', 120.00, 'COD', 'Store Pickup', NULL, 'Pending'),
(15, 3, '2026-06-21 07:42:23', 240.00, 'COD', 'Store Pickup', NULL, 'Pending'),
(16, 3, '2026-06-21 07:42:43', 110.00, 'COD', 'Store Pickup', NULL, 'Pending'),
(17, 3, '2026-06-21 09:25:19', 240.00, 'COD', 'Store Pickup', NULL, 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `ice` varchar(50) DEFAULT NULL,
  `sugar` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_name`, `size`, `ice`, `sugar`, `quantity`, `price`) VALUES
(1, 17, 'Biscoff Latte', 'Medium', 'Normal', '100%', 1, 120.00),
(2, 17, 'Java Chip', 'Medium', 'Normal', '100%', 1, 120.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `category` varchar(100) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `price_medium` decimal(10,2) NOT NULL,
  `price_large` decimal(10,2) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `bestseller` tinyint(1) DEFAULT 0,
  `is_sold_out` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `single_size` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `category`, `product_name`, `price_medium`, `price_large`, `image_path`, `bestseller`, `is_sold_out`, `created_at`, `single_size`) VALUES
(1, 'Coffee Drinks', 'Americano', 70.00, 90.00, 'images/americano.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(2, 'Coffee Drinks', 'Latte', 90.00, 120.00, 'images/latte.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(3, 'Coffee Drinks', 'Spanish Latte', 90.00, 120.00, 'images/spanish-latte.jpg', 1, 0, '2026-06-19 06:45:07', 0),
(4, 'Coffee Drinks', 'Spanish Oat', 110.00, 140.00, 'images/spanish-oat.jpg', 1, 0, '2026-06-19 06:45:07', 0),
(5, 'Coffee Drinks', 'Vanila latte', 100.00, 130.00, 'images/vanila-latte.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(6, 'Coffee Drinks', 'Vanila Oat latte', 100.00, 130.00, 'images/vanila-oat-latte.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(7, 'Coffee Drinks', 'Caramel Macchiato', 100.00, 130.00, 'images/caramel-macchiato.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(8, 'Coffee Drinks', 'Sea Salt', 100.00, 130.00, 'images/sea-salt.jpg', 1, 0, '2026-06-19 06:45:07', 0),
(9, 'Coffee Drinks', 'Milo Latte', 100.00, 130.00, 'images/milo-latte.jpg', 1, 0, '2026-06-19 06:45:07', 0),
(10, 'Coffee Drinks', 'White Mocha', 110.00, 140.00, 'images/white-mocha.jpg', 1, 0, '2026-06-19 06:45:07', 0),
(11, 'Coffee Drinks', 'White Mocha Hazelnut', 130.00, 160.00, 'images/white-mocha-hazelnut.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(12, 'Coffee Drinks', 'Hazelnut Latte', 110.00, 140.00, 'images/hazelnut-latte.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(13, 'Coffee Drinks', 'Ube latte', 110.00, 140.00, 'images/ube-latte.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(14, 'Coffee Drinks', 'Brown Sugar Espresso', 110.00, 140.00, 'images/brown-sugar-espresso.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(15, 'Coffee Drinks', 'Cacao Latte', 120.00, 150.00, 'images/cacao-latte.jpg', 1, 0, '2026-06-19 06:45:07', 0),
(16, 'Coffee Drinks', 'Biscoff Latte', 120.00, 140.00, 'images/biscoff-latte.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(17, 'Non-Coffee Blends', 'Ube Cloud', 90.00, 110.00, 'images/ube-cloud.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(18, 'Non-Coffee Blends', 'Matcha', 90.00, 120.00, 'images/matcha.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(19, 'Non-Coffee Blends', 'Matcha Oatmilk', 110.00, 140.00, 'images/matcha-oatmilk.jpg', 1, 0, '2026-06-19 06:45:07', 0),
(20, 'Non-Coffee Blends', 'White Chocolate Matcha', 110.00, 140.00, 'images/white-chocolate-matcha.jpg', 1, 0, '2026-06-19 06:45:07', 0),
(21, 'Non-Coffee Blends', 'Strawberry Matcha', 110.00, 140.00, 'images/strawberry-matcha.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(22, 'Non-Coffee Blends', 'Sea Salt Matcha', 110.00, 140.00, 'images/sea-salt-matcha.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(23, 'Non-Coffee Blends', 'Strawberry Crème Cocoa', 100.00, 130.00, 'images/strawberry-creme-cocoa.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(24, 'Non-Coffee Blends', 'Salted Crème Cocoa', 100.00, 130.00, 'images/salted-creme-cocoa.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(25, 'Non-Coffee Blends', 'Strawberry Milk', 80.00, 100.00, 'images/strawberry-milk.jpg', 1, 0, '2026-06-19 06:45:07', 0),
(26, 'Non-Coffee Blends', 'Sea Salt Strawberry C.', 90.00, 110.00, 'images/sea-salt-strawberry.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(27, 'Non-Coffee Blends', 'Blueberry Milk', 80.00, 100.00, 'images/blueberry-milk.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(28, 'Non-Coffee Blends', 'Milo Dino', 80.00, 100.00, 'images/milo-dino.jpg', 1, 0, '2026-06-19 06:45:07', 0),
(29, 'Soda', 'Blueberry', 55.00, 65.00, 'images/blueberry-soda.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(30, 'Soda', 'Lychee', 55.00, 65.00, 'images/lychee-soda.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(31, 'Soda', 'Green Apple', 55.00, 65.00, 'images/green-apple-soda.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(32, 'Soda', 'Bubble Gum', 55.00, 65.00, 'images/bubble-gum-soda.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(33, 'Soda', 'Strawberry', 55.00, 65.00, 'images/strawberry-soda.jpg', 0, 0, '2026-06-19 06:45:07', 0),
(34, 'Ice Blended Frappes', 'Ube', 120.00, 120.00, 'images/ube-frappe.jpg', 0, 0, '2026-06-19 06:45:07', 1),
(35, 'Ice Blended Frappes', 'Vanilla', 120.00, 120.00, 'images/vanilla-frappe.jpg', 0, 0, '2026-06-19 06:45:07', 1),
(36, 'Ice Blended Frappes', 'Strawberry Cheesecake', 120.00, 120.00, 'images/strawberry-cheesecake.jpg', 0, 0, '2026-06-19 06:45:07', 1),
(37, 'Ice Blended Frappes', 'Blueberry Cheesecake', 120.00, 120.00, 'images/blueberry-cheesecake.jpg', 0, 0, '2026-06-19 06:45:07', 1),
(38, 'Ice Blended Frappes', 'Oreo Cheesecake', 120.00, 120.00, 'images/oreo-cheesecake.jpg', 0, 0, '2026-06-19 06:45:07', 1),
(39, 'Ice Blended Frappes', 'Nutella Cheesecake', 140.00, 140.00, 'images/nutella-cheesecake.jpg', 0, 0, '2026-06-19 06:45:07', 1),
(40, 'Ice Blended Frappes', 'Salted Caramel', 120.00, 120.00, 'images/salted-caramel.jpg', 0, 0, '2026-06-19 06:45:07', 1),
(41, 'Ice Blended Frappes', 'Milo Dino', 120.00, 120.00, 'images/milo-dino-frappe.jpg', 0, 0, '2026-06-19 06:45:07', 1),
(42, 'Ice Blended Frappes', 'Java Chip', 120.00, 120.00, 'images/java-chip.jpg', 0, 0, '2026-06-19 06:45:07', 1),
(43, 'Ice Blended Frappes', 'Biscoff Crème', 140.00, 140.00, 'images/biscoff-creme.jpg', 0, 0, '2026-06-19 06:45:07', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `review_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `admin_reply` text DEFAULT NULL,
  `review_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `username`, `rating`, `review_text`, `created_at`, `admin_reply`, `review_photo`) VALUES
(1, NULL, 'rouie', 5, 'nice coffee!!', '2026-06-15 02:41:37', 'ty', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `created_at`, `address`, `phone_number`) VALUES
(3, 'Chloe', 'chloemaejan.14@gmail.com', '$2y$10$3til15X3nj7TNCYALry0ju08o4De6Sc3/rYnyFJZgR5QUBjevM5ha', 'user', '2026-06-13 08:35:41', NULL, NULL),
(4, 'rouie', 'rouie@gscoffee.com', '$2y$10$80C03dPXve9a98e963YppeXJsD4tJM62r0jpWIwL5C4vsLDd9KgHW', 'user', '2026-06-15 02:42:42', NULL, NULL),
(5, 'admin', 'admin@gscoffee.com', '$2y$10$o0MZITWVzTZnx8h6/47v9Oa97xjgf3SyTv4uBPqYFQmIsj.ncnpQe', 'admin', '2026-06-15 02:46:03', NULL, NULL),
(6, 'Andrei', 'andrei@gmail.com', '$2y$10$g0BihkxrVPxLWQLPWzuk.eJlkBZcUn1fiM7g6vW6H5YMA/q2SBqhu', 'user', '2026-06-15 04:39:53', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_addresses`
--

CREATE TABLE `user_addresses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_name` varchar(100) DEFAULT NULL,
  `full_address` text NOT NULL,
  `is_default` tinyint(1) DEFAULT 0,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_addresses`
--

INSERT INTO `user_addresses` (`id`, `user_id`, `address_name`, `full_address`, `is_default`, `latitude`, `longitude`) VALUES
(20, 3, 'School', 'De La Salle University Manila Manila Philippines', 0, 14.56494400, 120.99309920);

-- --------------------------------------------------------

--
-- Table structure for table `user_cart`
--

CREATE TABLE `user_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `size` varchar(20) NOT NULL,
  `ice` varchar(20) NOT NULL,
  `sugar` varchar(20) NOT NULL DEFAULT '100%',
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_cart`
--

INSERT INTO `user_cart` (`id`, `user_id`, `product_name`, `size`, `ice`, `sugar`, `quantity`, `price`, `added_at`) VALUES
(15, 3, 'Cacao Latte', 'Medium', 'Normal', '100%', 2, 120.00, '2026-06-23 07:17:08'),
(16, 3, 'Caramel Macchiato', 'Medium', 'Normal', '100%', 1, 100.00, '2026-06-23 07:17:15'),
(17, 3, 'Brown Sugar Espresso', 'Medium', 'Normal', '100%', 1, 110.00, '2026-06-24 06:21:57');

--
-- Indexes for dumped tables
--

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
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_addresses`
--
ALTER TABLE `user_addresses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `user_cart`
--
ALTER TABLE `user_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `user_addresses`
--
ALTER TABLE `user_addresses`
  ADD CONSTRAINT `user_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_cart`
--
ALTER TABLE `user_cart`
  ADD CONSTRAINT `user_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
