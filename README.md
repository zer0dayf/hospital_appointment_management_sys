# Hospital Management System

A mini full-stack hospital management system built to demonstrate core web engineering principles such as relational database modeling, REST-style backend APIs, and dynamic frontend behavior using vanilla technologies.

## Tech Stack

Frontend: HTML5, CSS3, Vanilla JavaScript (ES6+)  
Backend: PHP  
Database: MySQL  
Communication: REST-style API (JSON over HTTP), Fetch API

## Project Description

This project simulates a simplified hospital management system with a strong emphasis on clean architecture and data modeling rather than feature completeness. It demonstrates how a frontend application communicates with a backend API and a relational database in a structured and maintainable way, without relying on modern JavaScript frameworks.

The primary objective of the project is to showcase full-stack development fundamentals that are directly applicable to real-world systems.

## System Architecture

The application follows a classic three-layer architecture. The client-side interface runs in the browser and handles user interactions and UI updates. All data operations are performed through a PHP-based backend API, which communicates with a MySQL database using a normalized relational schema. Data exchange between the frontend and backend is handled asynchronously via JSON.

## Database Design

The database is designed using an inheritance-based relational model to reduce data duplication and accurately represent real-world entities.

Entity hierarchy:
Person (base entity)  
→ Patient  
→ Employee  
→ Doctor (extends Employee)

Shared attributes are stored in the Person table, while specialized entities extend the base entity through foreign keys. Referential integrity is enforced using constraints and cascading rules. This approach improves scalability, maintainability, and data consistency compared to flat table designs.

## Core Features

- Dashboard displaying basic system statistics  
- Patient management (add, list, delete)  
- Doctor management  
- Dynamic UI updates without page reloads  
- Modal-based user interactions  
- Asynchronous frontend–backend communication via API calls  

## Running the Project Locally

Requirements: PHP 8+, MySQL, and a local server environment such as XAMPP, WAMP, or MAMP.

Steps:
1. Clone the repository to your local machine.
2. Create a MySQL database and import the provided database.sql file.
3. Configure database credentials in the PHP connection file.
4. Place the project inside the server root directory and start Apache and MySQL.
5. Access the application via http://localhost/project-folder/

## Known Limitations

The project does not include authentication or authorization mechanisms, input validation or sanitization layers, pagination for large datasets, or API versioning. These limitations were intentionally accepted to keep the project focused on architectural fundamentals.

## Possible Improvements

Future enhancements could include adding authentication and role-based access control, implementing centralized input validation and error handling, modularizing frontend JavaScript files, improving API compliance with REST standards, and introducing automated tests.

## Notes

This project was developed as a portfolio-oriented full-stack exercise to demonstrate practical understanding of web application architecture, relational database design, and frontend–backend interaction.
