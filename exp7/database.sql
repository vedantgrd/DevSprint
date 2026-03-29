-- Create the database
CREATE DATABASE IF NOT EXISTS `exp7_db`;

-- Use the database
USE `exp7_db`;

-- Create the table
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
);

-- Insert some data
INSERT INTO `products` (`name`) VALUES
('Laptop'),
('Smartphone'),
('Tablet'),
('Headphones'),
('Keyboard'),
('Mouse'),
('Monitor');
