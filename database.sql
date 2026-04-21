CREATE DATABASE IF NOT EXISTS `badminton_booking_system`;
USE `badminton_booking_system`;

CREATE TABLE IF NOT EXISTS `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone_number` varchar(15) DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `courts` (
  `court_id` int(11) NOT NULL AUTO_INCREMENT,
  `court_number` int(11) NOT NULL,
  `price_per_hour` decimal(10,2) NOT NULL,
  PRIMARY KEY (`court_id`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `bookings` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `court_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(20) DEFAULT 'Reserved',
  PRIMARY KEY (`booking_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`),
  FOREIGN KEY (`court_id`) REFERENCES `courts`(`court_id`)
) ENGINE=InnoDB;

INSERT INTO `users` (`username`, `password`, `full_name`, `phone_number`, `role`) VALUES
('admin', 'admin123', 'System Administrator', '0123456789', 'admin'),
('user', 'user123', 'Regular User', '0111222333', 'user');

INSERT INTO `courts` (`court_number`, `price_per_hour`) VALUES
(1, 15.00), (2, 15.00), (3, 18.00), (4, 18.00);
