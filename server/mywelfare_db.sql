-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 05, 2026 at 07:38 PM
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
-- Database: `mywelfare_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_aid`
--

CREATE TABLE `tbl_aid` (
  `aid_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `aid_remark` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_aid`
--

INSERT INTO `tbl_aid` (`aid_id`, `user_id`, `amount`, `aid_remark`, `created_at`) VALUES
(1, 3, 100.00, 'Buatlah belanja..', '2026-01-05 17:18:12'),
(2, 5, 150.00, 'bantuan STR', '2026-01-05 18:15:52');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_complaint`
--

CREATE TABLE `tbl_complaint` (
  `complaint_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `complaint_type` varchar(100) NOT NULL,
  `complaint_details` text NOT NULL,
  `status` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_complaint`
--

INSERT INTO `tbl_complaint` (`complaint_id`, `user_id`, `complaint_type`, `complaint_details`, `status`, `created_at`) VALUES
(3, 3, 'delay-processing', 'So latee...', 'resolved', '2026-01-05 02:24:32'),
(4, 5, 'poor-services', 'Heleppp...', 'Pending', '2026-01-05 14:38:49');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `ic_number` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `household_size` int(11) DEFAULT NULL,
  `household_income` decimal(10,2) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `sub_district` varchar(100) DEFAULT NULL,
  `privilege` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_id`, `username`, `email`, `password`, `full_name`, `ic_number`, `phone`, `household_size`, `household_income`, `district`, `sub_district`, `privilege`, `created_at`) VALUES
(3, 'user', 'user@gmail.com', '$2y$10$MKuGVuKwrhL11huxFLSMSOByHJo5RzO5EUk0y3rbjhguDwJ15Zeo6', 'Ali Bin Abu', '010203040506', '0123456789', 1, 3000.00, 'baling', 'bakai', 0, '2026-01-05 00:25:38'),
(4, 'user2', 'user2@gmail.com', '$2y$10$XMW6Tth2JIbNGPAzrIUaeekMsip6Ezvm8vJiemcUQ.hLATLnIoIvW', 'Abubu', '090807060504', '0123456789', 2, 6000.00, 'baling', 'bakai', 0, '2026-01-05 02:22:54'),
(5, 'user3', 'user3@gmail.com', '$2y$10$Eo3WDXJl2iI8GJBFK81MeOAAHwM4kQDnui3Yjmgw/S3jvcre1Ofpa', 'Abu', '0123456789', '0123456789', 1, 2000.00, 'baling', 'pulai', 0, '2026-01-05 03:31:39'),
(6, 'admin', 'admin@gmail.com', '$2y$10$4nqC3P4dj8QW8NEOAMkhFO3hiTXHv7WfrFEoRx2GyjD3VmxHDekLa', 'admin', NULL, NULL, NULL, NULL, 'baling', 'bakai', 1, '2026-01-05 11:48:30'),
(7, 'admin2', 'admin2@gmail.com', '$2y$10$QydHwaO/47tSSsqqCjLS9O3g5m82hMNzxvCETDRt2xUl3L.ouftMG', 'admin2', NULL, NULL, NULL, NULL, 'baling', 'pulai', 2, '2026-01-05 14:39:54'),
(8, 'admin3', 'admin3@gmail.com', '$2y$10$ceHoaxaGV21dw.hyEyEW3.915o5o0Zln0HOT38u7887t1IGUA7rK.', 'admin3', NULL, NULL, NULL, NULL, 'baling', 'kupang', 3, '2026-01-05 17:13:16');

--
-- Triggers `tbl_user`
--
DELIMITER $$
CREATE TRIGGER `before_insert_tbl_user` BEFORE INSERT ON `tbl_user` FOR EACH ROW BEGIN
    IF NEW.full_name IS NULL OR NEW.full_name = '' THEN
        SET NEW.full_name = NEW.username;
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_welfare`
--

CREATE TABLE `tbl_welfare` (
  `welfare_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `aid_type` varchar(100) NOT NULL,
  `welfare_category` varchar(100) NOT NULL,
  `remarks` text NOT NULL,
  `status` varchar(50) NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_welfare`
--

INSERT INTO `tbl_welfare` (`welfare_id`, `user_id`, `aid_type`, `welfare_category`, `remarks`, `status`, `created_at`) VALUES
(7, 3, 'food-assistance', 'elderly', 'Im dying', 'pending', '2026-01-05 02:24:13'),
(8, 4, 'educational-support', 'b40', 'I need my degree', 'approved', '2026-01-05 02:33:02'),
(9, 5, 'medical-assistance', 'disabled', 'Patah kaki', 'Pending', '2026-01-05 14:38:16'),
(10, 5, 'cash-assistance', 'disabled', 'Xde duit la..', 'approved', '2026-01-05 14:39:06');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_aid`
--
ALTER TABLE `tbl_aid`
  ADD PRIMARY KEY (`aid_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tbl_complaint`
--
ALTER TABLE `tbl_complaint`
  ADD PRIMARY KEY (`complaint_id`),
  ADD KEY `fk_complaint_user` (`user_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `ic_number` (`ic_number`);

--
-- Indexes for table `tbl_welfare`
--
ALTER TABLE `tbl_welfare`
  ADD PRIMARY KEY (`welfare_id`),
  ADD KEY `fk_user_welfare` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_aid`
--
ALTER TABLE `tbl_aid`
  MODIFY `aid_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_complaint`
--
ALTER TABLE `tbl_complaint`
  MODIFY `complaint_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tbl_welfare`
--
ALTER TABLE `tbl_welfare`
  MODIFY `welfare_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tbl_aid`
--
ALTER TABLE `tbl_aid`
  ADD CONSTRAINT `tbl_aid_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_complaint`
--
ALTER TABLE `tbl_complaint`
  ADD CONSTRAINT `fk_complaint_user` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `tbl_welfare`
--
ALTER TABLE `tbl_welfare`
  ADD CONSTRAINT `fk_user_welfare` FOREIGN KEY (`user_id`) REFERENCES `tbl_user` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
