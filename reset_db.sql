CREATE DATABASE IF NOT EXISTS fitness_salina;
USE fitness_salina;

CREATE TABLE `clients` (
    `client_id` INT(10) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `surname` VARCHAR(255) NOT NULL,
    `patronymic` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(15) NOT NULL CHECK (
        phone REGEXP '^[+]?[0-9]{1,4}?[-.\\s]?([(]?[0-9]{1,3}[)]?[-.\\s]?){1,4}[0-9]+$'
    ),
    `password` VARCHAR(255) NOT NULL,
    `sex` ENUM('женский', 'мужской') NOT NULL,
    `birthday` DATE NOT NULL,
    PRIMARY KEY (`client_id`) USING BTREE
);

CREATE TABLE `coaches` (
    `coach_id` INT(10) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `surname` VARCHAR(255) NOT NULL,
    `patronymic` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(15) NOT NULL CHECK (
        phone REGEXP '^[+]?[0-9]{1,4}?[-.\\s]?([(]?[0-9]{1,3}[)]?[-.\\s]?){1,4}[0-9]+$'
    ),
    `password` VARCHAR(255) NOT NULL,
    `sex` ENUM('женский', 'мужской') NOT NULL,
    `birthday` DATE NOT NULL,
    `description` TEXT NULL DEFAULT NULL,
    `bid_per_hour` INT NOT NULL,
    PRIMARY KEY (`coach_id`) USING BTREE
);

CREATE TABLE `templates` (
    `template_id` INT(10) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `duration` INT(10) NOT NULL CHECK (duration > 0),
    `description` TEXT NULL DEFAULT NULL,
    PRIMARY KEY (`template_id`) USING BTREE
);

CREATE TABLE `workouts` (
    `workout_id` INT(10) NOT NULL AUTO_INCREMENT,
    `coach_id` INT(10) NOT NULL,
    `template_id` INT(10) NOT NULL,
    `start_time` DATETIME NOT NULL,
    `visit_limit` INT(10) NOT NULL,
    PRIMARY KEY (`workout_id`),
    INDEX (`coach_id`),
    INDEX (`template_id`),
    CONSTRAINT `workouts_ibfk_1` FOREIGN KEY (`coach_id`) REFERENCES `coaches` (`coach_id`) ON DELETE CASCADE,
    CONSTRAINT `workouts_ibfk_2` FOREIGN KEY (`template_id`) REFERENCES `templates` (`template_id`) ON DELETE CASCADE
);

CREATE TABLE `visits` (
    `visit_id` INT(10) NOT NULL AUTO_INCREMENT,
    `client_id` INT(10) NOT NULL,
    `workout_id` INT(10) NOT NULL,
    `is_attended` ENUM('не определено', 'посещена', 'не посещена') NOT NULL,
    PRIMARY KEY (`visit_id`),
    INDEX (`client_id`),
    INDEX (`workout_id`),
    CONSTRAINT `visits_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
    CONSTRAINT `visits_ibfk_2` FOREIGN KEY (`workout_id`) REFERENCES `workouts` (`workout_id`) ON DELETE CASCADE
);

CREATE TABLE `subscription_templates` (
    `subtemp_id` INT(10) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `amount` INT(10) NOT NULL CHECK (amount > 0),
    `cost` INT(10) NOT NULL CHECK (cost > 0),
    PRIMARY KEY (`subtemp_id`)
);

CREATE TABLE `subscriptions` (
    `subscription_id` INT(10) NOT NULL AUTO_INCREMENT,
    `subtemp_id` INT(10) NOT NULL,
    `purchase_date` DATE NOT NULL,
    `expiration_date` DATE NOT NULL,
    `rest` INT(10) NOT NULL,
    `client_id` INT(10) NOT NULL,
    PRIMARY KEY (`subscription_id`),
    INDEX (`client_id`),
    INDEX (`subtemp_id`),
    CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`client_id`) ON DELETE CASCADE,
    CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`subtemp_id`) REFERENCES `subscription_templates` (`subtemp_id`) ON DELETE CASCADE
);



