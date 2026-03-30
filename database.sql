-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
-- Host: 127.0.0.1
-- Generation Time: Mar 01, 2026
-- Server version: 10.4.24-MariaDB
-- PHP Version: 8.1.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `barbershop_db`
--
CREATE DATABASE IF NOT EXISTS `barbershop_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `barbershop_db`;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--
-- Drop all tables in reverse FK order (child tables first)
DROP TABLE IF EXISTS `appointments`;
DROP TABLE IF EXISTS `barber_services`;
DROP TABLE IF EXISTS `barbers`;
DROP TABLE IF EXISTS `services`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','customer','barber') NOT NULL DEFAULT 'customer',
  `linked_barber_id` int(11) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `linked_barber_id`, `contact_number`) VALUES
(1, 'Admin Setup', 'admin@camotes.com', '$2y$12$PUdoN8X176izlYZaVbIAvuBKJD9Np0DZdx3JEGhy75.tLFKbSfMy2', 'admin', NULL, '09123456789'),
(2, 'Marcus Blade', 'marcus@camotes.com', '$2y$12$PUdoN8X176izlYZaVbIAvuBKJD9Np0DZdx3JEGhy75.tLFKbSfMy2', 'barber', 1, '09111111111'),
(3, 'Elias Beard', 'elias@camotes.com', '$2y$12$PUdoN8X176izlYZaVbIAvuBKJD9Np0DZdx3JEGhy75.tLFKbSfMy2', 'barber', 2, '09222222222'),
(4, 'Julian Trad', 'julian@camotes.com', '$2y$12$PUdoN8X176izlYZaVbIAvuBKJD9Np0DZdx3JEGhy75.tLFKbSfMy2', 'barber', 3, '09333333333');
-- Password for all accounts above is: admin

-- --------------------------------------------------------

--
-- Table structure for table `services`
--
-- (Tables already dropped above)

CREATE TABLE `services` (
  `service_id` int(11) NOT NULL AUTO_INCREMENT,
  `service_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration` varchar(50) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `services`
--
INSERT INTO `services` (`service_id`, `service_name`, `description`, `price`, `duration`, `image`) VALUES
(1, 'Haircut', 'Classic cut, fade, undercut, taper, crew cut, etc.', '35.00', '45 min', 'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?q=80&w=400'),
(2, 'Beard Trim & Shaping', 'Clean lining, beard styling, and maintenance.', '25.00', '30 min', 'https://images.unsplash.com/photo-1622286342621-4bd786c2447c?q=80&w=400'),
(3, 'Hair & Beard Styling', 'Blow-dry and product styling (pomade, wax, gel).', '20.00', '20 min', 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?q=80&w=400'),
(4, 'Hair Coloring', 'Gray coverage, highlights, or full color.', '60.00', '60 min', ''),
(5, 'Hot Towel Shave', 'Traditional straight razor shave with hot towel treatment.', '30.00', '40 min', ''),
(6, 'Hair Wash / Shampoo', 'Deep cleansing before or after haircut.', '10.00', '15 min', ''),
(7, 'Scalp Treatment', 'Anti-dandruff or relaxing scalp massage.', '40.00', '30 min', ''),
(8, 'Kids Haircut', 'Special cuts for children.', '25.00', '30 min', ''),
(9, 'Hair Tattoo / Design', 'Razor line designs and patterns.', '15.00', '20 min', ''),
(10, 'Facial for Men', 'Basic facial cleansing and skin care.', '35.00', '45 min', '');

-- --------------------------------------------------------

--
-- Table structure for table `barbers`
--
-- (barbers table dropped above)
CREATE TABLE `barbers` (
  `barber_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `specialty` varchar(100) DEFAULT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `availability` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`barber_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `barbers`
--
INSERT INTO `barbers` (`barber_id`, `name`, `specialty`, `experience`, `image`, `availability`) VALUES
(1, 'Marcus ''The Blade''', 'Fades & Line-ups', '10 Years', 'https://images.unsplash.com/photo-1605497788044-5a32c7078486?q=80&w=400', 1),
(2, 'Elias', 'Beard Sculpting', '5 Years', 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?q=80&w=400', 1),
(3, 'Julian', 'Classic Traditional', '8 Years', 'https://images.unsplash.com/photo-1534308143481-c55f00be8bd7?q=80&w=400', 1);

-- --------------------------------------------------------

--
-- Table structure for table `barber_services`
--
CREATE TABLE `barber_services` (
  `barber_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  PRIMARY KEY (`barber_id`, `service_id`),
  FOREIGN KEY (`barber_id`) REFERENCES `barbers`(`barber_id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`service_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- By default, give all 3 barbers all 10 services for testing purposes
INSERT INTO `barber_services` (`barber_id`, `service_id`) VALUES
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),
(2,1),(2,2),(2,3),(2,5),(2,6),
(3,1),(3,4),(3,6),(3,8),(3,10);

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--
CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `service_id` int(11) NOT NULL,
  `barber_id` int(11) NOT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('Pending','Approved','Completed','Cancelled') NOT NULL DEFAULT 'Pending',
  `guest_name` varchar(100) DEFAULT NULL,
  `guest_email` varchar(100) DEFAULT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`appointment_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
  FOREIGN KEY (`service_id`) REFERENCES `services`(`service_id`) ON DELETE CASCADE,
  FOREIGN KEY (`barber_id`) REFERENCES `barbers`(`barber_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
