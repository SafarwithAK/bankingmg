-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2026 at 12:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bank_app`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `account_number` varchar(20) NOT NULL,
  `balance` decimal(15,2) NOT NULL DEFAULT 0.00,
  `currency` varchar(3) NOT NULL DEFAULT 'INR',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `user_id`, `account_number`, `balance`, `currency`, `created_at`, `status`) VALUES
(1, 1, '660636522632', 10000.00, 'INR', '2025-11-28 03:32:57', 'Active'),
(2, 2, '037699704080', 1020.00, 'INR', '2025-11-28 04:52:05', 'Active'),
(3, 3, '800778594980', 900.00, 'INR', '2026-03-25 18:13:49', 'Inactive'),
(4, 6, '958294966341', 2000.00, 'INR', '2026-03-25 19:20:49', 'Active'),
(5, 7, '962019281691', 1000.00, 'INR', '2026-03-25 19:51:47', 'Active'),
(6, 8, '780947135098', 2000000.00, 'INR', '2026-03-25 19:53:21', 'Active'),
(7, 9, '940680675460', 3400.00, 'INR', '2026-03-27 07:19:33', 'Active'),
(8, 10, '467468556578', 1000.00, 'INR', '2026-03-27 16:21:08', 'Inactive'),
(9, 12, '016065941054', 1000.00, 'INR', '2026-04-10 20:27:43', 'Inactive');

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `created_at`) VALUES
(2, 'Ajit@1234', 'ajitkumar091125@gmail.com', '$2y$10$Ss3EkYXr5VF1gFE05fJUd.Koc08yf7YHpKvNqIYGoTeM54my6Qc.C', '2026-03-25 18:03:12');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `branch_name` varchar(100) NOT NULL,
  `ifsc_code` varchar(20) NOT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_name`, `ifsc_code`, `address`, `city`, `state`, `created_at`) VALUES
(1, 'Saharsa', 'MYBK009334', 'Purab bazar saharsa', 'Saharsa', 'Bihar', '2026-03-27 05:57:22'),
(2, 'Madhubani', 'MYBK009608', 'Sandip University madhubani', 'Madhubani', 'Bihar', '2026-03-27 06:17:42'),
(4, 'Damgarhi', 'MYBK009335', 'Damgarhi', 'Saharsa', 'Bihar', '2026-03-27 07:02:35');

-- --------------------------------------------------------

--
-- Table structure for table `csp_accounts`
--

CREATE TABLE `csp_accounts` (
  `id` int(11) NOT NULL,
  `csp_id` int(11) DEFAULT NULL,
  `balance` decimal(15,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `csp_users`
--

CREATE TABLE `csp_users` (
  `id` int(11) NOT NULL,
  `branch_id` int(11) NOT NULL,
  `balance` decimal(15,2) DEFAULT 0.00,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `csp_users`
--

INSERT INTO `csp_users` (`id`, `branch_id`, `balance`, `name`, `username`, `email`, `mobile`, `address`, `password`, `status`, `created_at`) VALUES
(1, 1, 2000.00, 'Gitesh Jha', 'CSP0002', 'gitesh123@gmail.com', '7645871886', 'Rahua', '$2y$10$WpI4TbMSmRQUjyQSNMrtkeU91dfpToqCLeGn6lcLd/JPz6atarz1G', 'Active', '2026-03-27 06:14:31'),
(7, 2, 108800.00, 'AJIT KUMAR', 'CSP0001', 'ajitkumar09112005@gmail.com', '09334649467', 'Madhubani', '$2y$10$IwPTNOE4GNy6QFJrp7vx4uAxj.DrF6HxLwQ.ibmPpZKV5/G1ZCzDO', 'Active', '2026-03-27 07:06:13');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `from_account_id` int(11) DEFAULT NULL,
  `to_account_id` int(11) DEFAULT NULL,
  `type` enum('Credit','Debit') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `from_account_id`, `to_account_id`, `type`, `amount`, `description`, `created_at`) VALUES
(0, 1, NULL, 1, 'Credit', 100.00, 'Added Money', '2026-04-11 00:02:16'),
(0, 1, NULL, 1, 'Credit', 99000.00, 'Added Money', '2026-04-11 00:02:43'),
(0, 0, 1, 7, '', 100.00, '', '2026-04-11 00:19:22'),
(0, 0, 1, 7, '', 1000.00, '', '2026-04-11 00:20:17'),
(0, 1, NULL, 1, 'Credit', 100.00, 'Deposit by CSP', '2026-04-11 00:32:44'),
(0, 1, NULL, 1, 'Credit', 100.00, 'Deposit by CSP', '2026-04-11 00:34:55'),
(0, 1, 1, NULL, 'Debit', 1000.00, 'Withdraw by CSP', '2026-04-11 00:35:12'),
(0, 1, 1, NULL, 'Debit', 98000.00, 'Withdraw by CSP', '2026-04-11 00:36:33'),
(0, 1, NULL, 1, 'Credit', 10000.00, 'Added Money', '2026-04-11 00:53:56'),
(0, 1, NULL, 1, 'Credit', 1000.00, 'Deposit by CSP', '2026-04-11 00:54:13'),
(0, 1, NULL, 1, 'Credit', 1000.00, 'Self Deposit / Added Money', '2026-04-11 01:02:41'),
(0, 1, NULL, 1, 'Credit', 100.00, 'Added via UPI', '2026-04-11 01:12:42'),
(0, 1, NULL, 1, 'Credit', 100.00, 'Self Deposit / Added Money', '2026-04-11 01:14:49'),
(0, 1, NULL, 1, 'Credit', 200.00, 'Added via UPI', '2026-04-11 01:15:33'),
(0, 1, NULL, 1, 'Credit', 500.00, 'Added via UPI (UPI)', '2026-04-11 01:22:06'),
(0, 1, 1, NULL, 'Debit', 1000.00, 'Withdraw by CSP', '2026-04-11 02:04:43'),
(0, 1, 1, NULL, 'Debit', 2000.00, 'Withdraw by CSP', '2026-04-11 02:09:29');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user','admin') NOT NULL DEFAULT 'user',
  `mobile` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `nominee_name` varchar(100) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `kyc_status` enum('Pending','Verified','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `full_name`, `password_hash`, `created_at`, `role`, `mobile`, `address`, `dob`, `gender`, `nominee_name`, `branch_id`, `kyc_status`) VALUES
(1, 'Ajit@123', 'ajitkumar09112005@gmail.com', 'AJIT KUMAR', '$2y$10$37NrTm7zh/8FiLu37MQmGeFU9uLnOUq0JEKdz0ud7pITkgsK7OCzK', '2025-11-28 03:32:57', 'user', '9334649467', 'Bajrang Dham Rahua', '2005-11-09', 'Male', 'Raju Kumar', 2, 'Verified'),
(2, 'raju@', 'raju169@gmail.com', 'RAJU KUMAR', '$2y$10$23srdA7gy9rrOUBcoOQMoeEJoZVlX8NF7Fw/K9EGpn4GhXP5JgFO6', '2025-11-28 04:52:05', 'user', NULL, NULL, NULL, NULL, NULL, 2, 'Pending'),
(3, 'Ajit@1234', 'ajitkumar09@gmail.com', 'AJIT KUMAR', '$2y$10$nwxdaJVJnLveNJmNwfYP3.u66FRAVKLIJVwiDSFFYN2JO4XWLv47i', '2026-03-25 18:13:49', 'user', NULL, NULL, NULL, NULL, NULL, 2, 'Verified'),
(6, 'ravi@123', 'ravi123@gmail.com', 'Ravi Kumar', '$2y$10$NdhTQL9wVNzEYrAC5TLER.okUOqbhgT54dIg63FQQuFGFk4jGLVIa', '2026-03-25 19:20:49', 'user', '123456789', '', '0000-00-00', 'Male', '', 2, 'Pending'),
(7, 'ajeet@123', 'ajeet123@gmail.com', 'AJEET KUMAR', '$2y$10$.he0cNQCyaH/fJSG.1y4MOrQxaO6Rijnjp33R/iYYSzb6l09wvAfK', '2026-03-25 19:51:47', 'user', NULL, NULL, NULL, NULL, NULL, 2, 'Pending'),
(8, 'sahil@123', 'sahil123@gmail.com', 'SAHIL KUMAR', '$2y$10$on4DWkO6Zd/2xQAMq4aXn.f55cM/GMhK/NIBwF2KBxbzDuUbNOaHm', '2026-03-25 19:53:21', 'user', '9142926324', NULL, NULL, NULL, NULL, 2, 'Pending'),
(9, 'dipansu@123', 'dipansu123@gmail.com', 'Dipansu Kumar', '$2y$10$4.EhNbH9FN0yyA/9ad1yXeUm2xGza5rxK296jz/ZoxIKt1csG.slS', '2026-03-27 07:19:33', 'user', NULL, NULL, NULL, NULL, NULL, 2, 'Verified'),
(10, 'gaurav@123', 'gaurav123@gmail.com', 'Gaurav Kumar', '$2y$10$pP3u5OkE/Oz9Itf9D2Q75eQmmCSeBwsFD8NMqbLR3HM26P2Ss6PnG', '2026-03-27 16:21:08', 'user', '9608057813', 'Saran', '2005-06-08', 'Male', 'Ravi', 2, 'Verified'),
(12, 'jay123', 'jayjha@gmail.com', 'jay jha', '$2y$10$N5oLDLeiqkKl.CYm8huT1eTsfz3Si7QimW79OywQ43THyg4FoD.x2', '2026-04-10 20:27:43', 'user', '123456789', '', '0000-00-00', 'Male', '', 1, 'Verified');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_number` (`account_number`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ifsc_code` (`ifsc_code`);

--
-- Indexes for table `csp_accounts`
--
ALTER TABLE `csp_accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `csp_users`
--
ALTER TABLE `csp_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `branch_id` (`branch_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `branch_id` (`branch_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `csp_accounts`
--
ALTER TABLE `csp_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `csp_users`
--
ALTER TABLE `csp_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `accounts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `csp_users`
--
ALTER TABLE `csp_users`
  ADD CONSTRAINT `csp_users_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
