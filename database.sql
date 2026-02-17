-- Hospital Management System Database (PostgreSQL)
-- Fixed Version: 2026-02-17

-- 1. Prerequisites (Handled by user via createdb)

-- 2. Drop Tables if they exist (Reverse Dependency Order)
DROP TABLE IF EXISTS appointment CASCADE;
DROP TABLE IF EXISTS secretary CASCADE;
DROP TABLE IF EXISTS doctor CASCADE;
DROP TABLE IF EXISTS employee CASCADE;
DROP TABLE IF EXISTS patient CASCADE;
DROP TABLE IF EXISTS person CASCADE;

-- 3. Create Tables

-- Table: Person (Superclass)
CREATE TABLE person (
  person_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  fname_surname VARCHAR(100) NOT NULL,
  birth_date DATE NOT NULL,
  address VARCHAR(255) DEFAULT NULL,
  phone_number VARCHAR(20) DEFAULT NULL,
  email VARCHAR(100) DEFAULT NULL
);

-- Table: Patient (Subclass of Person)
CREATE TABLE patient (
  patient_id INT PRIMARY KEY REFERENCES person(person_id) ON DELETE CASCADE,
  med_history TEXT DEFAULT NULL,
  allergies TEXT DEFAULT NULL,
  emrg_contact VARCHAR(20) DEFAULT NULL
);

-- Table: Employee (Subclass of Person)
CREATE TABLE employee (
  employee_id INT PRIMARY KEY REFERENCES person(person_id) ON DELETE CASCADE,
  hiring_date DATE NOT NULL,
  salary DECIMAL(10,2) NOT NULL,
  shift_type VARCHAR(20) DEFAULT NULL
);

-- Table: Doctor (Subclass of Employee)
CREATE TABLE doctor (
  doctor_id INT PRIMARY KEY REFERENCES employee(employee_id) ON DELETE CASCADE,
  profession VARCHAR(50) NOT NULL,
  room_no VARCHAR(10) DEFAULT NULL
);

-- Table: Secretary (Subclass of Employee)
CREATE TABLE secretary (
  secretary_id INT PRIMARY KEY REFERENCES employee(employee_id) ON DELETE CASCADE
);

-- Table: Appointment
CREATE TABLE appointment (
  appoint_id INT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  appoint_date DATE NOT NULL,
  appoint_time TIME NOT NULL,
  status VARCHAR(20) DEFAULT 'Scheduled',
  patient_id INT NOT NULL REFERENCES patient(patient_id) ON DELETE CASCADE,
  doctor_id INT NOT NULL REFERENCES doctor(doctor_id) ON DELETE CASCADE,
  secretary_id INT DEFAULT NULL REFERENCES secretary(secretary_id) ON DELETE SET NULL
);

-- Indexes for performance
CREATE INDEX idx_patient ON appointment (patient_id);
CREATE INDEX idx_doctor ON appointment (doctor_id);
CREATE INDEX idx_date ON appointment (appoint_date);
CREATE INDEX idx_status ON appointment (status);
CREATE INDEX idx_fname ON person (fname_surname);

-- 4. Seed Data

-- Persons (Keep IDs consistent)
INSERT INTO person (person_id, fname_surname, birth_date, address, phone_number, email) OVERRIDING SYSTEM VALUE VALUES
(1, 'John Doe', '1985-05-15', '123 Main St', '555-0101', 'john@example.com'),
(2, 'Jane Smith', '1990-08-22', '456 Oak Ave', '555-0102', 'jane@example.com'),
(3, 'Dr. Alice Wong', '1978-03-10', '789 Pine Ln', '555-0103', 'alice@hospital.com'),
(4, 'Dr. Bob Brown', '1982-11-30', '321 Elm St', '555-0104', 'bob@hospital.com'),
(5, 'Secretary Pam', '1988-06-12', 'Reception Desk', '555-0000', 'pam@hospital.com');

-- Patients (ID 1, 2)
INSERT INTO patient (patient_id, med_history, allergies, emrg_contact) VALUES
(1, 'Hypertension', 'Penicillin', '555-9999'),
(2, 'Asthma', 'None', '555-8888');

-- Employees (ID 3, 4, 5)
INSERT INTO employee (employee_id, hiring_date, salary, shift_type) VALUES
(3, '2010-01-01', 150000.00, 'Day'),
(4, '2015-06-15', 140000.00, 'Night'),
(5, '2018-09-01', 50000.00, 'Day');

-- Doctors (ID 3, 4)
INSERT INTO doctor (doctor_id, profession, room_no) VALUES
(3, 'Cardiologist', '101'),
(4, 'Pediatrician', '202');

-- Secretaries (ID 5)
INSERT INTO secretary (secretary_id) VALUES
(5);

-- Appointments
-- Not specifying IDs so IDENTITY handles it and starts from 1
INSERT INTO appointment (appoint_date, appoint_time, status, patient_id, doctor_id, secretary_id) VALUES
('2025-12-20', '09:00:00', 'Scheduled', 1, 3, 5),
('2025-12-21', '14:30:00', 'Completed', 2, 4, 5);
