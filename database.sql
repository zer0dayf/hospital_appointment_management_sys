-- Hospital Management System Database
-- Generated: 2025-12-17

-- 1. Create and Select Database
CREATE DATABASE IF NOT EXISTS `hospital_db`;
USE `hospital_db`;

-- 2. Drop Tables if they exist (Reverse Dependency Order)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `appointment`;
DROP TABLE IF EXISTS `secretary`;
DROP TABLE IF EXISTS `doctor`;
DROP TABLE IF EXISTS `employee`;
DROP TABLE IF EXISTS `patient`;
DROP TABLE IF EXISTS `person`;
SET FOREIGN_KEY_CHECKS = 1;

-- 3. Create Tables

-- Table: Person (Superclass)
CREATE TABLE `person` (
  `person_id` int(11) NOT NULL AUTO_INCREMENT,
  `fname_surname` varchar(100) NOT NULL,
  `birth_date` date NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`person_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Patient (Subclass of Person)
CREATE TABLE `patient` (
  `patient_id` int(11) NOT NULL,
  `med_history` text DEFAULT NULL,
  `allergies` text DEFAULT NULL,
  `emrg_contact` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`patient_id`),
  CONSTRAINT `fk_patient_person` FOREIGN KEY (`patient_id`) REFERENCES `person` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Employee (Subclass of Person)
CREATE TABLE `employee` (
  `employee_id` int(11) NOT NULL,
  `hiring_date` date NOT NULL,
  `salary` decimal(10,2) NOT NULL,
  `shift_type` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`employee_id`),
  CONSTRAINT `fk_employee_person` FOREIGN KEY (`employee_id`) REFERENCES `person` (`person_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Doctor (Subclass of Employee)
CREATE TABLE `doctor` (
  `doctor_id` int(11) NOT NULL,
  `profession` varchar(50) NOT NULL,
  `room_no` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`doctor_id`),
  CONSTRAINT `fk_doctor_employee` FOREIGN KEY (`doctor_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Secretary (Subclass of Employee)
CREATE TABLE `secretary` (
  `secretary_id` int(11) NOT NULL,
  PRIMARY KEY (`secretary_id`),
  CONSTRAINT `fk_secretary_employee` FOREIGN KEY (`secretary_id`) REFERENCES `employee` (`employee_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table: Appointment
CREATE TABLE `appointment` (
  `appoint_id` int(11) NOT NULL AUTO_INCREMENT,
  `appoint_date` date NOT NULL,
  `appoint_time` time NOT NULL,
  `status` varchar(20) DEFAULT 'Scheduled',
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `secretary_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`appoint_id`),
  KEY `idx_patient` (`patient_id`),
  KEY `idx_doctor` (`doctor_id`),
  CONSTRAINT `fk_appt_patient` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_appt_doctor` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_appt_secretary` FOREIGN KEY (`secretary_id`) REFERENCES `secretary` (`secretary_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Seed Data

-- Persons
INSERT INTO `person` (`person_id`, `fname_surname`, `birth_date`, `address`, `phone_number`, `email`) VALUES
(1, 'John Doe', '1985-05-15', '123 Main St', '555-0101', 'john@example.com'),
(2, 'Jane Smith', '1990-08-22', '456 Oak Ave', '555-0102', 'jane@example.com'),
(3, 'Dr. Alice Wong', '1978-03-10', '789 Pine Ln', '555-0103', 'alice@hospital.com'),
(4, 'Dr. Bob Brown', '1982-11-30', '321 Elm St', '555-0104', 'bob@hospital.com'),
(5, 'Secretary Pam', '1988-06-12', 'Reception Desk', '555-0000', 'pam@hospital.com');

-- Patients
INSERT INTO `patient` (`patient_id`, `med_history`, `allergies`, `emrg_contact`) VALUES
(1, 'Hypertension', 'Penicillin', '555-9999'),
(2, 'Asthma', 'None', '555-8888');

-- Employees
INSERT INTO `employee` (`employee_id`, `hiring_date`, `salary`, `shift_type`) VALUES
(3, '2010-01-01', 150000.00, 'Day'),
(4, '2015-06-15', 140000.00, 'Night'),
(5, '2018-09-01', 50000.00, 'Day');

-- Doctors
INSERT INTO `doctor` (`doctor_id`, `profession`, `room_no`) VALUES
(3, 'Cardiologist', '101'),
(4, 'Pediatrician', '202');

-- Secretaries
INSERT INTO `secretary` (`secretary_id`) VALUES
(5);

-- Appointments
INSERT INTO `appointment` (`appoint_id`, `appoint_date`, `appoint_time`, `status`, `patient_id`, `doctor_id`, `secretary_id`) VALUES
(1, '2025-12-20', '09:00:00', 'Scheduled', 1, 3, 5),
(2, '2025-12-21', '14:30:00', 'Completed', 2, 4, 5);
